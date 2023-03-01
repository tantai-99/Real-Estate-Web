<?php
namespace Library\Custom\Model\Estate\Search\Special\Chikunensu1;
use Library\Custom\Model\Estate\Search\Special\Kakaku\AbstractKakaku;

class CompositeChintaiJigyo2 extends AbstractKakaku {
	
	static protected $_instance;
	
	protected $_list = [
        '0'=>'指定なし',
        '10'=>'1年以内',
        '30'=>'3年以内',
        '40'=>'5年以内',
        '50'=>'10年以内',
        '60'=>'15年以内',
        '70'=>'20年以内',
        '80'=>'25年以内',
        '90'=>'30年以内',
        '100'=>'35年以内',
        '110'=>'40年以内',
        '120'=>'新築を除く',
		];
}