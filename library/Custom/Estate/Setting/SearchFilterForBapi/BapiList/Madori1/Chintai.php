<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Madori1;

class Chintai extends Madori1Abstract {
	
	static protected $_instance;
	
	protected $_list = [
		'10'=>'1:01',
		'20'=>'1:02,1:06',
		'30'=>'1:03,1:04,1:07,1:08',
		'40'=>'1:05,1:09',
		'50'=>'2:02,2:06',
		'60'=>'2:03,2:04,2:07,2:08',
		'70'=>'2:05,2:09',
		'80'=>'3:02,3:06',
		'90'=>'3:03,3:04,3:07,3:08',
		'100'=>'3:05,3:09',
		'110'=>'4:02,4:06',
		'120'=>'4:03,4:04,4:07,4:08',
		'130'=>'gte:4:05,gte:4:09',
	];
}