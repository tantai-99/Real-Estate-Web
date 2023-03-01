<?php
namespace App\Repositories\HpFile2Content;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2ContentLength\HpFile2ContentLengthRepositoryInterface;

class HpFile2ContentRepository extends BaseRepository implements HpFile2ContentRepositoryInterface
{   
    protected $_name = 'hp_file2_content';
    protected $_bulk_copy_threshold = 100;

    public function getModel()
    {
        return \App\Models\HpFile2Content::class;
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
	* ファイルの容量を計算する
	*
	* @param int $hp_id
	*/
	public function getCapacity( $hp_id ) {

	    $capacity	= 0		;

	    $select = $this->model->withoutGlobalScopes();

        $select->from("hp_file2_content as hic" );
        $select->selectRaw("hicl.content_length as blob_capacity");
	    $select->leftJoin("hp_file2_content_length as hicl", function($join){
            $join->on("hic.aid", "hicl.hp_file2_content_id");
        });
        $select->join("hp_file2 as hi", function($join) {
            $join->on("hic.id", "hi.hp_file2_content_id")
                 ->where("hi.hp_id", "hic.hp_id")
                 ->where("hi.delete_flg", 0);
        });
        $select->where( "hic.hp_id", $hp_id	) ;
        $select->where( "hic.delete_flg", 0) ;
        $rows = $select->get();
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
        $tablelen   = App::make(HpFile2ContentLengthRepositoryInterface::class);

        $cnt = 0;
        foreach($rows as $row) {
            $where_aid =  array(['aid', $row->aid]);
            $insert_id = parent::copyAll($cols, $data, $where_aid, $order, $count, $offset);

            $select_len = $this->model->select();
            // $select_len->from( $this->_name, array("length(content) as content_length"));
            $select_len->where("aid", $insert_id);
            $rows_len = $this->fetchAll($select_len);

            $lid = $tablelen->create(array(
                'hp_file2_content_id' => $insert_id,
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

        $where = [['hp_id',$hp_id],['create_date', $action_start]];
        $rows_len = $this->fetchAll($where);
        $tablelen   = App::make(HpFile2ContentLengthRepositoryInterface::class);
        foreach($rows_len as $row) {
            $lid = $tablelen->create(array(
                'hp_file2_content_id' => $row->aid,
                'content_length' => $row->content_length,
            ));
        }
    }
}