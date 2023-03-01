<?php
namespace Library\Custom\Model\Estate\Search\Special\Madori1;

use Library\Custom\Model\Estate\Search\Special\Kakaku\AbstractKakaku;

class CompositeBaibaiKyoju1 extends AbstractKakaku {
	
	static protected $_instance;
	
	protected $_list = [
        '10'=>'1R',
        '20'=>'1K',
        '30'=>'1DK',
        '40'=>'1LDK',
        '50'=>'2K',
        '60'=>'2DK',
        '70'=>'2LDK',
        '80'=>'3K',
        '90'=>'3DK',
        '100'=>'3LDK',
        '110'=>'4K',
        '120'=>'4DK',
        '130'=>'4LDK',
        '140'=>'5K',
        '150'=>'5DK',
        '160'=>'5LDK以上',
		];
}