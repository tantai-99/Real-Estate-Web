<?php
namespace Library\Custom\Model\Lists;

class ShopDetail extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '住所',
        2 => 'アクセス',
        3 => 'TEL',
        4 => 'FAX',
        5 => '営業時間',
        6 => '定休日',
    );

    protected $_chinese = array(
        1 => '所在地',
        2 => '路线',
        3 => 'TEL',
        4 => 'FAX',
        5 => '营业时间',
        6 => '店休日',
    );
}