<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Special;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class ItemType extends Abstract\ItemType {
	
	static protected $_instance;
	
	public function __construct() {
		$types = [];
		$types['kakaku'][1] = 'list';
		$types['kakaku'][2] = 'list';
		
		$types['rimawari'][1] = 'list';
		$types['rimawari'][2] = 'list';
		
		$types['keiyaku_joken'][1] = 'list';
		
		$types['menseki'][1] = 'list';
		$types['menseki'][2] = 'list';
		$types['menseki'][3] = 'list';
		$types['menseki'][4] = 'list';

		$types['madori'][1] = 'multi';

        $types['reform_renovation'][1] = 'radio';

        $types['reformable_parts'][1] = 'multi';
        $types['reformable_parts'][2] = 'multi';
        $types['reformable_parts'][3] = 'multi';

        $types['joho_kokai'][1] = 'radio';
		
		$types['chikunensu'][1] = 'multi';
		$types['chikunensu'][2] = 'list';
		
		$types['eki_toho_fun'][1] = 'radio';

        $types['torihiki_taiyo'][1] = 'multi';

		$this->_list = $types;
	}
}