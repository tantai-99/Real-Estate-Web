<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Second;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\ItemType as AbstractItemType;
class ItemType extends AbstractItemType {
	
	static protected $_instance;
	
	public function __construct() {
		$types = [];

		$types['kakaku'][1] = 'list';
		$types['kakaku'][2] = 'list';
		$types['madori'][1] = 'multi';
		$types['tatemono_ms'][1] = 'list';
		$types['tatemono_ms'][2] = 'list';
		$types['tochi_ms'][1] = 'list';
		$types['tochi_ms'][2] = 'list';
		$types['saiteki_yoto_cd'][1] = 'radio';
		$types['chikunensu'][1] = 'radio';

		$this->_list = $types;
	}
}