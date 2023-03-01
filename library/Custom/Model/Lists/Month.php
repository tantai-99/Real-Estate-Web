<?php
namespace Library\Custom\Model\Lists;

class Month extends ListAbstract {
	
	static protected $_instance;
	
	public function __construct() {
		
		$list = array();
		
		for ($i = 1; $i <= 12; $i++) {
			
			$list[$i] = $i . '月';
		}
		
		$this->_list = $list;
	}
	
}