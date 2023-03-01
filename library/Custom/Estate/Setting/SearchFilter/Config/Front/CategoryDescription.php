<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Front;

use Library\Custom\Estate\Setting\SearchFilter\Config\Special;

class CategoryDescription
	extends Special\CategoryDescription {
	
	static protected $_instance;
	
	public function __construct() {

        // 特集の定義をベースにする
        parent::__construct();
	}
}