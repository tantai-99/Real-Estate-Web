<?php
namespace Library\Custom\Model\Estate\Search\Special\ReformableParts2;
use Library\Custom\Model\Estate\AbstractList;

class AbstractParts2 extends AbstractList {

    protected $_list = [
		// reform_renovation_naiso_cd:
        '1'=>'内装全面(床・壁・天井・建具)',
        '2'=>'間取り変更・スケルトン',
        '3'=>'全室クロス張替え',
        '4'=>'床(フローリング等)',
        '5'=>'壁・天井(クロス・塗装等)',
        '6'=>'建具(室内ドア等)',
        '7'=>'サッシ',
        '8'=>'収納',
		// reform_renovation_naiso_sonota_ari_fl:true
        '999'=>'その他',
    ];
}