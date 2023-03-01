<?php
namespace App\Repositories\HpFileContent;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpFileContentLength\HpFileContentLengthRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class HpFileContentRepository extends BaseRepository implements HpFileContentRepositoryInterface
{   
    protected $_name = 'hp_file_content';
    protected $_bulk_copy_threshold = 100;
    
    public function getModel()
    {
        return \App\Models\HpFileContent::class;
    }

    public function fetchInfo( $hpId, $id )
    {
    	$select = $this->model->select('id', 'extension','filename');
    	$select->from( $this->_name ) ;
    	$select->where('id', $id	) ;
    	$select->where( 'hp_id', $hpId	) ;
    	return $select->first();
    }

    /**
     * 実際に使用している容量を計算する
     *
     * @param int $hp_id
     */
    public function getCapacity($hp_id) {

        $capacity = 0;

        //各ページに紐付いているファイルから算出
        $select = $this->model->withoutGlobalScopes();
        $select->from($this->_name. " as f");
        $select->selectRaw("fl.content_length as blob_capacity");
        $select->leftJoin("hp_file_content_length as fl", function($join){
            $join->on("f.aid", "fl.hp_file_content_id");
        });
        $select->join("hp", function($join) {
            $join->on("hp.id", "f.hp_id")
                 ->where("hp.delete_flg", 0);
        });
        $select->join("hp_page as p", function($join) {
            $join->on("p.hp_id", "f.hp_id")
                 ->where("p.delete_flg", 0);
        });
        $select->join("hp_main_parts as mp", function($join) {
            $join->on("mp.hp_id", "f.hp_id")
                 ->where("p.id", "mp.page_id")
                 ->where("mp.delete_flg", 0);
        });
        $select->join("hp_main_element as me", function($join) {
            $join->on("me.hp_id", "f.hp_id")
                 ->where("mp.id", "me.parts_id")
                 ->where("me.delete_flg", 0);
        });

        $select->where("f.hp_id", $hp_id);
        $page = \App::make(HpPageRepositoryInterface::class);
        $select->whereIn("p.page_type_code", $page->getAddedFilePages());
        $select->where("mp.parts_type_code", HpMainPartsRepository::PARTS_FOR_DOWNLOAD_APPLICATION);
        $select->where("me.type", "file");
        $select->where("me.attr_2", "f.id");
        $select->where("f.delete_flg", 0);
        $rows = $select->get();
        foreach($rows as $row) {
            $capacity = (int)$capacity+(int)$row->blob_capacity;
        }
        return $capacity;
    }

    /**
     * テーブルデータのコピー処理(App\Repositories\BaseRepository:copyAll override)
     *
     */
    public function copyAll($cols, $data = array(), $where = null, $order = null, $count = null, $offset = null) {
        $action_start = date('Y-m-d H:i:s');

        $hp_id = null;
        if(isset($data['hp_id'])) {
            $hp_id = $data['hp_id'];
        }

        if(!is_null($order) || !is_null($count) || !is_null($offset)) {
            $this->copyPolling();
            parent::copyAll($cols, $data, $where, $order, $count, $offset);
            $this->insertContentLength($hp_id, $action_start);
            $this->copyPolling();
            return true;
        }

        // 引数条件に合致するaid一覧を取得する
        $select = $this->model->select('aid');
        $select->where($where);
        $rows = $select->get();
        $this->copyPolling();

        // 対象が0ならコピー不要
        if(count($rows) == 0) { 
            return true;
        }
        // 対象が規定数以下なら、一括コピー
        if(count($rows) < $this->_bulk_copy_threshold) {
            parent::copyAll($cols, $data, $where, $order, $count, $offset);
            $this->insertContentLength($hp_id, $action_start);
            $this->copyPolling();
            return true;
        }
        // 1件ずつコピー
        $tablelen   = App::make(HpFileContentLengthRepositoryInterface::class);
        $cnt = 0;
        foreach($rows as $row) {
            $where_aid =  array(['aid', $row->aid]);
            $insert_id = parent::copyAll($cols, $data, $where_aid, $order, $count, $offset);
            $select_len = $this->model->select();
            // $select_len->from( $this->_name, array("length(content) as content_length"));
            $select_len->where("aid", $insert_id);
            $rows_len = $this->fetchAll($select_len);

            $lid = $tablelen->create(array(
                'hp_file_content_id' => $insert_id,
                'content_length' => $rows_len[0]->content_length,
            ));
            $cnt++;
            if($cnt % $this->_bulk_copy_threshold == 0) {
                $this->copyPolling();
            }
        }
        return true;
    }
    private function insertContentLength($hp_id, $action_start) {
		if(is_null($hp_id)) return;

        $select_len = $this->model->select();
        // $select_len->from( $this->_name, array("aid", "length(content) as content_length"));
        $select_len->where("hp_id", $hp_id);
        $select_len->where("create_date", '>=' , $action_start);
        $rows_len = $this->fetchAll($select_len);
        $tablelen = App::make(HpFileContentLengthRepositoryInterface::class);
        foreach($rows_len as $row) {
            $lid = $tablelen->insert(array(
                'hp_file_content_id' => $row->aid,
                'content_length' => $row->content_length
            ));
        }
    }
}