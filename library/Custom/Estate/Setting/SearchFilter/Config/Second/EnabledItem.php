<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Second;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\EnabledItem as AbstractEnabledItem;
use Library\Custom\Model\Estate\TypeList;

class EnabledItem extends AbstractEnabledItem {
	
	static protected $_instance;
	
	public function __construct() {
		$estateTypes = [
			TypeList::TYPE_CHINTAI,
			TypeList::TYPE_PARKING,
			TypeList::TYPE_KASI_TENPO,
			TypeList::TYPE_KASI_OFFICE,
			TypeList::TYPE_KASI_TOCHI,
			TypeList::TYPE_KASI_OTHER,
			TypeList::TYPE_MANSION,
			TypeList::TYPE_KODATE,
			TypeList::TYPE_URI_TOCHI,
			TypeList::TYPE_URI_TENPO,
			TypeList::TYPE_URI_OFFICE,
			TypeList::TYPE_URI_OTHER,

		];
		$this->_types = $estateTypes;
		$this->_typeIndex = array_flip($estateTypes);
		
		$enables = [];
		$enables['kakaku'][1]            =[1,1,1,1,1,1,1,1,1,1,1,1];
		$enables['kakaku'][2]            =[1,1,1,1,1,1,1,1,1,1,1,1];
		$enables['madori'][1]            =[1,0,0,0,0,0,1,1,0,0,0,0];
		$enables['tatemono_ms'][1]       =[1,0,1,1,0,1,1,1,0,1,1,1];
		$enables['tatemono_ms'][2]       =[1,0,1,1,0,1,1,1,0,1,1,1];
		$enables['tochi_ms'][1]          =[0,0,0,0,1,1,0,1,1,1,1,1];
		$enables['tochi_ms'][2]          =[0,0,0,0,1,1,0,1,1,1,1,1];
		$enables['saiteki_yoto_cd'][1]   =[0,0,0,0,0,0,0,0,1,0,0,0];
		$enables['chikunensu'][1]        =[1,0,1,1,0,1,1,1,0,1,1,1];
		$enables['image'][1]             =[1,1,1,1,1,1,1,1,1,1,1,1];
		$enables['koukokuhi'][1]         =[1,1,1,1,1,1,1,1,1,1,1,1];
		$enables['tesuryo'][1]           =[1,1,1,1,1,1,1,1,1,1,1,1];
		$enables['tesuryo'][2]           =[0,0,0,0,0,0,1,1,1,1,1,1];
		$this->_list = $enables;
	}
}