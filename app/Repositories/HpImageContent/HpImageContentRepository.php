<?php
namespace App\Repositories\HpImageContent;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpImageContentRepository extends BaseRepository implements HpImageContentRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpImageContent::class;
    }
    
    protected $_name = 'hp_image_content';
    protected $_bulk_copy_threshold = 100;
    /**
     * イメージの容量を計算する
     *
     * @param int $hp_id
     */
    public function getCapacity($hp_id) {
        $capacity	= 0		;

	    $select = $this->model->from("hp_image_content as hic" );
        $select->selectRaw("length(hic.content) as blob_capacity");
        $select->join("hp_image as hi", function($join) {
            $join->on("hic.id", "hi.hp_image_content_id")
                 ->where("hi.hp_id", "hic.hp_id")
                 ->whereNull("hi.sys_name")
                 ->where("hi.delete_flg", 0);
        });
        $select->where( "hic.hp_id", $hp_id	) ;
        $select->where( "hic.delete_flg", 0) ;
        $rows = $select->withoutGlobalScopes()->get();
        foreach( $rows as $row ) {
            $capacity = (int)$capacity + (int)$row->blob_capacity ;
        }
        return $capacity ;
    }

    /**
     * テーブルデータのコピー処理(App\Repositories\BaseRepository:copyAll override)
     *
     */
    public function copyAll($cols, $data = array(), $where = null, $order = null, $count = null, $offset = null) {
        if(!is_null($order) || !is_null($count) || !is_null($offset)) {
            return parent::copyAll($cols, $data, $where, $order, $count, $offset);
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
            return parent::copyAll($cols, $data, $where, $order, $count, $offset);
        }

        // 1件ずつコピー
        $cnt = 0;
        foreach($rows as $row) {
            $where_aid =  array(['aid', $row->aid]);
            $insert_id = parent::copyAll($cols, $data, $where_aid, $order, $count, $offset);
            $cnt++;
            if($cnt % $this->_bulk_copy_threshold == 0) {
                $this->copyPolling();
            }
        }
        return true;
    }
}
