<?php
namespace App\Repositories\HpFile2Used;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpFile2UsedRepository extends BaseRepository implements HpFile2UsedRepositoryInterface
{   
    public function getModel()
    {
        return \App\Models\HpFile2Used::class;
    }
    public function saveHpPageFile2s( $hpId, $hpPageId, $file2Ids, $transaction = true ) {
        if ( $transaction ) {
            \DB::beginTransaction() ;
        }

        $this->delete( array(['hp_page_id', $hpPageId]), true ) ;
        foreach ( $file2Ids as $file2Id ) {
            $this->create(array(
                'hp_id'         => $hpId        ,
                'hp_page_id'    => $hpPageId    ,
                'hp_file2_id'   => $file2Id ,
            ));
        }

        if ( $transaction ) {
            \DB::commit() ;
        }
    }

    /**
     * HP内で使用されているファイル２IDを取得
     *
     * @param $hpId
     * @return array
     */
    public function usedFile2IdsInHp( $hpId ){

        $select = $this->model->select() ;
        $select->groupBy(' hp_file2_id' ) ;
        $select->where( 'hp_id', $hpId ) ;
        $rows = $select->get() ;

        $res = array() ;
        if($rows) {
            foreach ( $rows as $row ) {
                $res[] = $row->hp_file2_id ;
            }
        }
        return $res ;

    }

    /**
     * ページ使用ファイル２のID一覧を取得する
     * @param int   $hpId
     * @param int   $hpPageId
     * @return array
     */
    public function usedFile2IdsInPage( $hpId, $hpPageId ) {
        $select = $this->model->select();
        $select->groupBy('hp_file2_id');
        $select->where('hp_id', $hpId);
        $select->where('hp_page_id', $hpPageId);
        $rows = $select->get() ;
        $res = array() ;
        if($rows) {
            foreach ( $rows as $row ) {
                $res[] = $row->hp_file2_id ;
            }
        }
        return $res ;
    }
}