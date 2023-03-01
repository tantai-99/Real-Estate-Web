<?php
namespace Library\Custom\Model\Estate\Search\Special\TorihikiTaiyo1;

class UriOffice extends AbstractTorihiki {
	
	static protected $_instance;
	
    protected $_list = [
        '01'=>'売主',
        '03'=>'代理',
        '04'=>'専任',
        '05'=>'一般',
        '06'=>'専属専任',
        '08'=>'媒介',
    ];
}