<?php
namespace Library\Custom\View\Helper;

class HasBackupData extends  HelperAbstract
{
	static private $_has_backupdata = null;
	
    public function hasBackupData()
    {
    	if (!is_null(self::$_has_backupdata)) {
    		return self::$_has_backupdata;
    	}
    	
    	self::$_has_backupdata = !!getInstanceUser('cms')->getBackupHp();
    	return self::$_has_backupdata;
    }
}