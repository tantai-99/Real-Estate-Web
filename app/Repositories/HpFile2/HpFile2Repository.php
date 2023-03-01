<?php
namespace App\Repositories\HpFile2;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2Category\HpFile2CategoryRepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;

class HpFile2Repository extends BaseRepository implements HpFile2RepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpFile2::class;
    }

    const TYPE_SAMPLE = 1;

    /**
     * システムで用意するファイルを作成する
     * @param int $hpId
     */
    public function initSysFile2s( $hpId )
    {
    	$this->_initSysFile2s( $hpId, self::TYPE_SAMPLE, null, glob( storage_path('data/samples/file2s').DIRECTORY_SEPARATOR.'*.*' ) ) ;
    }

    /**
     * 
     * @param int $hpId
     * @param int $type
     * @param string $categoryName
     * @param array $file2s
     */
    protected function _initSysFile2s( $hpId, $type, $categoryName, $file2s )
    {
    	if ( !$file2s ) {
    		return ;
    	}
    	
    	$file2Info = array() ;
    	$file2Name = array();
    	foreach ( $file2s as $file2 )
    	{
    		$info							= pathinfo( $file2 )	;
    		$info[ 'org' ]					= $file2				;
    		$file2Info[ $info['filename'] ]	= $info;
    		$file2Name[ $info['filename'] ]	= $info['filename'];
    	}

        $nowFile2 = $this->model->whereIn('sys_name', $file2Name)->where('hp_id', $hpId)->get();
        foreach ($nowFile2 as $key => $value) {
            if(isset($file2Info[$value->sys_name])) {
                unset($file2Info[$value->sys_name]);
            }
        }

        if(count($file2Info) == 0) return;

        $hpIds = (array)$hpId;
    	reset( $hpIds ) ;
    	
    	$categoryTable	= App::make(HpFile2CategoryRepositoryInterface::class);
    	$contentTable	= App::make(HpFile2ContentRepositoryInterface::class);
        foreach ($hpIds as $key => $hpId) {
    		// カテゴリ作成
    		$categoryId = 0 ;
    		if ( $categoryName )
    		{
    			$categoryRow = $categoryTable->create( array(
    				'name'	=> $categoryName												,
    				'sort'	=> $categoryTable->countRows( array( ['hp_id', $hpId ]) )	,
    				'hp_id'	=> $hpId
    			)) ;
    			$categoryRow->save() ;
    			$categoryRow->id	= $categoryRow->aid	;
    			$categoryRow->save() ;
    			$categoryId			= $categoryRow->id	;
    		}
    		
    		// ファイル作成
    		foreach ( $file2Info as $name => $info ) {
                if(!isset($file2Info[$name])) continue;
                $contentRow = $contentTable->create( array(
    				'extension'	=> $info[ 'extension' ]					,
    				'content'	=> file_get_contents( $info[ 'org' ] )	,
    				'hp_id'		=> $hpId
    			) ) ;
    			$contentRow->save() ;
    			$contentRow->id = $contentRow->aid ;
    			$contentRow->save() ;
    			
    			$file2Row = $this->create( array(
    				'type'					=> $type			,
    				'sys_name'				=> $name			,
    				'title'					=> $name			,
    				'hp_file2_content_id'	=> $contentRow->id	,
    				'category_id'			=> $categoryId		,
    				'hp_id'					=> $hpId			,
    			) ) ;
    			$file2Row->save() ;
    			$file2Row->id = $file2Row->aid ;
    			$file2Row->save() ;
    		}
    	}
    	reset( $hpIds ) ;
    }

    public function getSysFile2Map( $hpId, $type )
    {
        $map	= array() ;
        // $sql = '
	       //  SELECT hp_file2.*, hp_file2_content.extension
	       //  FROM hp_file2 LEFT JOIN hp_file2_content ON hp_file2.hp_file2_content_id = hp_file2_content.id
        //     WHERE	hp_file2.hp_id				= ?
        //     AND     hp_file2.type               = ?
	       //  AND		hp_file2.delete_flg			= 0
	       //  AND		hp_file2_content.delete_flg	= 0
        // ';
        $query = $this->model->withoutGlobalScopes()->selectRaw('hp_file2.*, hp_file2_content.extension')
        ->leftJoin('hp_file2_content', function($join) {
            $join->on('hp_file2.hp_file2_content_id', 'hp_file2_content.id');
        })
        ->where([['hp_file2.hp_id', $hpId],['hp_file2.type', $type],['hp_file2.delete_flg', 0],['hp_file2_content.delete_flg', 0]]);

        // $rowset = $this->getAdapter()->fetchAll( $sql, array( $hpId, $type ) ) ;
        $rowset = $query->get();
    	foreach ( $rowset as $row ) {
    		if ( !$row['sys_name'] ) {
    			continue ;
    		}
    		$map[ $row['sys_name'] ] = array($row['id'], $row['extension']) ;
    	}
    	return $map ;
    }

	public function fetchFile2Information( $file2_id )
    {
		$query = $this->model->withoutGlobalScopes()->selectRaw('hp_file2.*, hp_file2_content.extension')
        ->leftJoin('hp_file2_content', function($join) {
            $join->on('hp_file2.hp_file2_content_id', 'hp_file2_content.id');
        })
		->where([['hp_file2.id', $file2_id],['hp_file2.delete_flg', 0],['hp_file2_content.delete_flg', 0]]);
		return $query->first();
    }
}
