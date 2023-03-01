<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Second;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\ItemLabel as AbstractItemLabel;

class ItemLabel extends AbstractItemLabel	 {
	
	static protected $_instance;
	
	public function __construct() {
		$labels = [];
		$labels['kakaku'][1]='下限：賃料（価格）';
		$labels['kakaku'][2]='上限：賃料（価格）';
		$labels['tatemono_ms'][1]='建物面積';
		$labels['tatemono_ms'][1]='建物面積';
		$labels['tochi_ms'][1]='土地面積';
		$labels['tochi_ms'][2]='土地面積';
		$labels['saiteki_yoto_cd'][1]='最適用途';
		$labels['madori'][1]='間取り';
		$labels['chikunensu'][1]='築年数';
		$labels['image'][1]='画像あり物件のみ';
		$labels['koukokuhi'][1]='広告費ありのみ';
		$labels['tesuryo'][1]='手数料ありのみ（分かれを除く）';
        $labels['tesuryo'][2]='手数料ありのみ（分かれを含める）';

		$this->_list = $labels;
	}
}