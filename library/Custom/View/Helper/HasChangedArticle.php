<?php
namespace Library\Custom\View\Helper;

class HasChangedArticle extends  HelperAbstract
{
	static private $_has_draft = null;
	
    public function hasChangedArticle()
    {
    	if (!is_null(self::$_has_draft)) {
    		return self::$_has_draft;
    	}
    	
    	$hp = getInstanceUser('cms')->getCurrentHp();
    	self::$_has_draft = ($hp && $hp->hasChangedArticle());
    	return self::$_has_draft;
    }
}