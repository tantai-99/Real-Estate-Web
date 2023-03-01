<?php
namespace Library\Custom\Model\Estate\Search\Special\Rimawari2;
use Library\Custom\Model\Estate\Search\Special\Kakaku\AbstractKakaku;

class CompositeBaibaiJigyo1 extends AbstractKakaku {
	
	static protected $_instance;
	
	protected $_list = [
        '0'=>'上限なし',
        '5'=>'5%',
        '10'=>'10%',
        '15'=>'15%',
    ];
}