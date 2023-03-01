<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Front;

use Library\Custom\Estate\Setting\SearchFilter\Config\Special;

class EnabledItem
	extends Special\EnabledItem {
	
	static protected $_instance;
	
	public function __construct() {

        // 特集の定義をベースにする
        parent::__construct();
	}
}