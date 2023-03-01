<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Special;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class CategoryDescription extends Abstract\CategoryDescription {
	
	static protected $_instance;
	
	public function __construct() {
		$list = [];
		$this->_list = $list;
	}
}