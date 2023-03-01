<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Chikunensu1;

class Kodate extends Chikunensu1Abstract {
	
	static protected $_instance;
	
	protected $_list = [
		'10'=>'shinchiku_chuko_cd=1',
		'20'=>'chikunensu=lte:3',
		'30'=>'chikunensu=lte:5',
		'40'=>'chikunensu=lte:10',
		'50'=>'chikunensu=lte:20',
	];
}