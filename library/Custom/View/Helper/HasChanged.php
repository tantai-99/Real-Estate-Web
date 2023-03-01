<?php
namespace Library\Custom\View\Helper;

class HasChanged extends  HelperAbstract
{
	static private $_has_draft = null;
	
    public function hasChanged()
    {
    	if (!is_null(self::$_has_draft)) {
    		return self::$_has_draft;
    	}
    	
    	$hp = getInstanceUser('cms')->getCurrentHp();
    	self::$_has_draft = ($hp && $hp->hasChanged());
    	return self::$_has_draft;
    }
}