<?php

/**
 * お問い合わせ　現住居区分
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPersonCurrentHomeClass extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '持家',
        2 => '賃貸',
        3 => '社宅',
        4 => 'その他',
    );

    protected $_chinese = array(
        1 => '自置居所',
        2 => '租赁',
        3 => '公司住房',
        4 => '其他',
    );
}