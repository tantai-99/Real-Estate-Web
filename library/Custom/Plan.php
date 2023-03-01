<?php
namespace Library\Custom;

use Library\Custom\Model\Lists\CmsPlan;

/**
 * 
 * プラン情報のベースクラス
 *
 */
abstract class Plan {
	
	public 		$initialPages	=	array()	;								// 初期のページ構成
	public		$categoryMap	=	array()	;								// カテゴリごとのタイプ
	protected	$canUsePages	=	false	;								// $categoryMapをフラットにしたもの
	
	/**
	 * 指定されたプラン情報のインスタンスを返す
	 *
	 * @param	$toTagetPlanName	string		プラン名
	 */
	static public function factory( $toTagetPlanName )
	{
		$toTagetPlanClassName	= "Library\Custom\Plan\\" . ucfirst( $toTagetPlanName )	;
		$instance				= new $toTagetPlanClassName()	;
		
		return $instance	;
	}
	
	/**
	 * 使用可能なページの差分を返す
	 *
	 * @param	$to		Library\Custom\Plan&		プラン
	 */
	public function getDiffPages(& $to )
	{
		$del		= $this	->_whatDaletedPages( $to	) ;
		$add		= $to	->_whatDaletedPages( $this	) ;

        //Custom diff Plan Lite
        if (count($add) >0 && CmsPlan::getValuePlan(get_class($this)) == config('constants.cms_plan.CMS_PLAN_LITE')) {
            foreach (CmsPlan::getBlockSiteMapPlanLite() as $typeCode) {
                if (($key = array_search($typeCode, $add)) !== false) {
                   unset($add[$key]);
                }
            }
        }

		$result		= array (
			'del'	=> $this->sortDelete($del)	,
			'add'	=> $add	,
		) ;
		return $result	;
	}

	/**
	 * タイプの属するカテゴリを取得する
	 *
	 * @param int $type
	 *
	 * @return int|NULL
	 */
	public function getCategoryByType($type) {
		$type = (int)$type;
		foreach ($this->categoryMap as $category => $types) {
			if (in_array($type, $types, true)) {
				return $category;
			}
		}
		return NULL;
	}
	
	protected function _whatDaletedPages(&$to )
	{
		$this	->_setCausePages()		;
		$to		->_setCausePages()		;
		return array_diff($this->canUsePages, $to->canUsePages	) ;
	}
	
	protected function _setCausePages()
	{
		if ( $this->canUsePages == false )
		{
			$this->canUsePages = iterator_to_array( new \RecursiveIteratorIterator( new \RecursiveArrayIterator( array_merge($this->categoryMap, $this->pageMapArticle) ) ), false ) ;
		}
	}
    
    protected function sortDelete($del) {
        $results = array();
        usort($del, function($a, $b)
            {
                if ($a == $b)
                {
                    return 0;
                }
                else if ($a < $b)
                {
                    return 1;
                }
                else {
                    return -1;
                }
            });
        return array_merge($results, $del);
    }
}
