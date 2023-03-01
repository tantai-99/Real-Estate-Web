<?php
namespace Library\Custom\Model\Estate\Search\Special\ReformableParts1;
use Library\Custom\Model\Estate\AbstractList;

class AbstractParts1 extends AbstractList {

    protected $_list = [
		// reform_renovation_mizumawari_cd:
        '1'=>'水回りの位置変更',
        '2'=>'キッチン',
        '3'=>'浴室',
        '4'=>'トイレ',
        '5'=>'洗面所',
        '6'=>'給排水管',
        '7'=>'給湯器',
        // reform_renovation_mizumawari_sonota_ari_fl:true
        '999'=>'その他',

    ];
}