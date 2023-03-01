<?php
namespace Library\Custom\View\Helper;

/**
 * 物件設定に対応する物件問い合わせページが無い物件種別名の配列を返す 
 */
class GetEstateContactNecessity extends  HelperAbstract
{
	static private $_has_draft = null;
	
	/**
	 * 物件お問い合わせ画面が必要な場合はtrue
	 */
    public function getEstateContactNecessity()
    {
    	if (!is_null(self::$_has_draft)) {
    		return self::$_has_draft;
    	}
    	
    	$hp = getInstanceUser('cms')->getCurrentHp();
    	self::$_has_draft = empty($hp) ? array() : $hp->getEstateContactNecessity();
    	return self::$_has_draft;
    }
}