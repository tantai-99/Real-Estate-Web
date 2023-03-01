<?php

namespace App\Repositories\HpImageUsed;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpImageUsedRepository extends BaseRepository implements HpImageUsedRepositoryInterface
{   
    public function getModel()
    {
        return \App\Models\HpImageUsed::class;
    }
    /**
     * ページ使用画像を登録する
     * @param int $hpId
     * @param int $hpPageId
     * @param array $imageIds
     */
    public function saveHpPageImages($hpId, $hpPageId, $imageIds, $transaction = true) {
    	if ($transaction) {
    		\DB::beginTransaction();
    	}

    	$this->delete(array(['hp_page_id', $hpPageId]), true);
    	foreach ($imageIds as $imageId) {
    		$this->create(array(
    			'hp_id' => $hpId,
    			'hp_page_id' => $hpPageId,
    			'hp_image_id' => $imageId,
    		));
    	}

    	if ($transaction) {
    		\DB::commit();
    	}
    }

    /**
     * HP内で使用されている画像IDを取得
     *
     * @param $hpId
     * @return array
     */
    public function usedImageIdsInHp($hpId){

        $select = $this->model->select();
        $select->groupBy('hp_image_id');
        $select->where('hp_id', $hpId);
        $rows = $select->get();

        $res = array();
        if ($rows) {
            foreach ($rows as $row) {
                $res[] = $row->hp_image_id;
            }
        }
        return $res;

    }

    /**
     * ページ使用画像のID一覧を取得する
     * @param int $hpId
     * @param int $hpPageId
     * @return array
     */
    public function usedImageIdsInPage($hpId, $hpPageId) {
        $select = $this->model->select();
        $select->groupBy('hp_image_id');
        $select->where('hp_id', $hpId);
        $select->where('hp_page_id', $hpPageId);
        $rows = $select->get();
        $res = array();
        if ($rows) {
            foreach ($rows as $row) {
                $res[] = $row->hp_image_id;
            }
        }
        return $res;
    }
}