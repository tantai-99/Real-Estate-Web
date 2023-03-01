<?php
namespace App\Repositories\HpImage;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpImageCategory\HpImageCategoryRepositoryInterface;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;

class HpImageRepository extends BaseRepository implements HpImageRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpImage::class;
    }

    const TYPE_SAMPLE = 1;
    protected $_bulk_copy_threshold = 100;

    /**
     * hp_image_content.extension を一緒に取得
     */
    public function fetchImageInformation($image_id)
    {
        $select = $this->model->withoutGlobalScopes()->from('hp_image');
        $select->selectRaw('hp_image.*, hp_image_content.extension');
        $select->leftJoin('hp_image_content', function($join) {
            $join->on('hp_image.hp_image_content_id', 'hp_image_content.id');
        });
        $select->where('hp_image.id', $image_id);
        $select->where('hp_image.delete_flg', 0);
        $select->where('hp_image_content.delete_flg', 0);

        return $select->first();
    }

    public function initSysImages($hpIds) {
        
        $this->_initSysImages($hpIds, self::TYPE_SAMPLE, null, glob(storage_path('data/samples/images').DIRECTORY_SEPARATOR.'*.*'));
    }
    
    public function getSysImageMap($hpId, $type) {
        $map = array();
        $rowset = $this->fetchAll(array(['type', $type], ['hp_id', $hpId]));
        foreach ($rowset as $row) {
            if (!$row->sys_name) {
                continue;
            }
            $map[$row->sys_name] = $row->id;
        }
        return $map;
    }
    
    /**
     * 
     * @param array|int $hpIds
     * @param int $type
     * @param string $categoryName
     * @param array $images
     */
    protected function _initSysImages($hpIds, $type, $categoryName, $images) {
        if (!$images) {
            return;
        }
        
        $hpIds = (array) $hpIds;
        
        $imageInfo = array();
        foreach ($images as $image) {
            $info = pathinfo($image);
            $info['org'] = $image;
            $imageInfo[ $info['filename'] ] = $info;
        }
        
        reset($hpIds);
        
        $categoryTable = App::make(HpImageCategoryRepositoryInterface::class);
        $contentTable = App::make(HpImageContentRepositoryInterface::class);
        foreach ($hpIds as $key => $hpId) {
            
            if ($this->fetchRow(array(['type', $type], ['hp_id', $hpId]))) {
                // 既に同タイプの画像がある場合はスキップ
                continue;
            }
            
            // カテゴリ作成
            $categoryId = 0;
            if ($categoryName) {
                $categoryRow = $categoryTable->create(array(
                    'name' => $categoryName,
                    'sort' => $categoryTable->countRows(array(['hp_id ', $hpId])),
                    'hp_id' => $hpId
                ));
                $categoryRow->save();
                $categoryRow->id = $categoryRow->aid;
                $categoryRow->save();
                $categoryId = $categoryRow->id;
            }
            
            // 画像作成
            foreach ($imageInfo as $name => $info) {
                $contentRow = $contentTable->create(array(
                    'extension' => $info['extension'],
                    'content' => file_get_contents($info['org']),
                    'hp_id' => $hpId
                ));
                $contentRow->save();
                $contentRow->id = $contentRow->aid;
                $contentRow->save();
                
                $imageRow = $this->create(array(
                    'type' => $type,
                    'sys_name' => $name,
                    'title' => $name,
                    'hp_image_content_id' => $contentRow->id,
                    'category_id' => $categoryId,
                    'hp_id' => $hpId,
                ));
                $imageRow->save();
                $imageRow->id = $imageRow->aid;
                $imageRow->save();
            }
        }
        reset($hpIds);
    }

    /**
     * 追加されるシステム用の画像を生成する
     * @param array|int $hpIds
     * @param int $type
     * @param string $categoryName
     * @param array $images
     */
    public function addSysImages($hpId, $type, $categoryName, $images) {

        if (!$images) {
            return;
        }

        $imageInfo = array();
        $imgName = array();
        foreach ($images as $image) {
            $info = pathinfo($image);
            $info['org'] = $image;
            $imageInfo[ $info['filename'] ] = $info;
            $imgName[$info['filename']] = $info['filename'];
        }

        $nowImages = $this->model->whereIn('sys_name', $imgName)->where('hp_id', $hpId)->get();
        foreach ($nowImages as $key => $value) {
            if(isset($imgName[$value->sys_name])) {
                unset($imgName[$value->sys_name]);
            }
        }

        if(count($imgName) == 0) return;

        $categoryTable = App::make(HpImageCategoryRepositoryInterface::class);
        $contentTable = App::make(HpImageContentRepositoryInterface::class);

        // カテゴリ作成
        $categoryId = 0;
        if ($categoryName) {
            $categoryRow = $categoryTable->create(array(
                'name' => $categoryName,
                'sort' => $categoryTable->countRows(array(['hp_id', $hpId])),
                'hp_id' => $hpId
            ));
            $categoryRow->save();
            $categoryRow->id = $categoryRow->aid;
            $categoryRow->save();
            $categoryId = $categoryRow->id;
        }

        // 画像作成
        foreach ($imageInfo as $name => $info) {
            if(!isset($imgName[$name])) continue;

            $contentRow = $contentTable->create(array(
                'extension' => $info['extension'],
                'content' => file_get_contents($info['org']),
                'hp_id' => $hpId
            ));
            $contentRow->save();
            $contentRow->id = $contentRow->aid;
            $contentRow->save();
            
            $imageRow = $this->create(array(
                'type' => $type,
                'sys_name' => $name,
                'title' => $name,
                'hp_image_content_id' => $contentRow->id,
                'category_id' => $categoryId,
                'hp_id' => $hpId,
            ));
            $imageRow->save();
            $imageRow->id = $imageRow->aid;
            $imageRow->save();
        }
    }
}
