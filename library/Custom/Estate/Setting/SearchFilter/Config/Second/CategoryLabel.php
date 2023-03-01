<?php

namespace Library\Custom\Estate\Setting\SearchFilter\Config\Second;

use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\CategoryLabel as AbstractCategoryLabel;
use Library\Custom\Model\Estate\TypeList;

class CategoryLabel extends AbstractCategoryLabel  {
	
	static protected $_instance;
	
	public function __construct() {
		
		$labels['kakaku']=[
			'default'=>'賃料',
			TypeList::TYPE_MANSION=>'価格',
			TypeList::TYPE_KODATE=>'価格',
			TypeList::TYPE_URI_TOCHI=>'価格',
			TypeList::TYPE_URI_TENPO=>'価格',
			TypeList::TYPE_URI_OFFICE=>'価格',
			TypeList::TYPE_URI_OTHER=>'価格',
		];
		$labels['madori']='間取り';
		$labels['tatemono_ms']=[
			'default'=>'使用部分面積',
			TypeList::TYPE_CHINTAI=>'専用面積',
			TypeList::TYPE_MANSION=>'専用面積',
			TypeList::TYPE_KODATE=>'建物面積',
		];
		$labels['tochi_ms']='土地面積';
		$labels['chikunensu']='築年数';
		$labels['image']='画像';
		$labels['koukokuhi']='広告費';
		$labels['tesuryo']='手数料';
		$labels['saiteki_yoto_cd']='最適用途';

		$this->_list = $labels;
	}

}