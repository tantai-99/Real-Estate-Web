<?php
namespace Library\Custom\Model\Estate\Search\Special\Chikunensu2;
use Library\Custom\Model\Estate\AbstractList;

class CompositeChintaiJigyo1 extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
        '0'=>'下限なし',
        '1'=>'1年以上',
        '3'=>'3年以上',
        '5'=>'5年以上',
        '10'=>'10年以上',
        '15'=>'15年以上',
        '20'=>'20年以上',
        '25'=>'25年以上',
        '30'=>'30年以上',
        '35'=>'35年以上',
        '40'=>'40年以上',
		];
}