<?php
namespace Library\Custom\Model\Lists;

class StructureType extends ListAbstract {

	static protected $_instance;
	
    protected $_list = array(
        1 => '土地',
        2 => '一戸建て',
    	3 => 'マンション',
    	4 => '1棟マンション・アパート',
    	5 => '店舗・事務所',
    	6 => 'その他',
    );

}