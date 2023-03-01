<?php
namespace Library\Custom\Model\Lists;

class StructureTypeForEvent extends ListAbstract {

	static protected $_instance;
	
    protected $_list = array(
        1 => '土地',
        2 => '一戸建て',
    	3 => 'マンション',
    );

}