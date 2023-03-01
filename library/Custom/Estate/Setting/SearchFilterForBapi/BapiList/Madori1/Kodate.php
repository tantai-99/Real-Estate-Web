<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Madori1;

class Kodate extends Madori1Abstract {
	
	static protected $_instance;
	
	protected $_list = [
		'10'=>'1:01,1:02,1:03,1:04,1:05,1:06,1:07,1:08,1:09',
		'20'=>'2:02,2:06',
		'30'=>'2:03,2:04,2:07,2:08',
		'40'=>'2:05,2:09',
		'50'=>'3:02,3:06',
		'60'=>'3:03,3:04,3:07,3:08',
		'70'=>'3:05,3:09',
		'80'=>'4:02,4:06',
		'90'=>'4:03,4:04,4:07,4:08',
		'100'=>'4:05,4:09',
		'110'=>'5:02,5:06',
		'120'=>'5:03,5:04,5:07,5:08',
		'130'=>'gte:5:05,gte:5:09',
	];
}