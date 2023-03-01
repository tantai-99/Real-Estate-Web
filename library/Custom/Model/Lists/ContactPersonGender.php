<?php

/**
 * お問い合わせ　性別
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPersonGender extends ListAbstract {

    static protected $_instance;

    protected $_list    = array(
        1 => '男性',
        2 => '女性',
    );

    protected $_chinese = array(
        1 => '先生',
        2 => '女士',
    );
}