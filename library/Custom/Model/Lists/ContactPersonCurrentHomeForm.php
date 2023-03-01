<?php

/**
 * お問い合わせ　現住居形態
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPersonCurrentHomeForm extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '一戸建て',
        2 => 'アパート',
        3 => 'マンション',
    );

    protected $_chinese = array(
        1 => '日式公寓',
        2 => '日式简易出租房',
        3 => '公寓',
    );
}

