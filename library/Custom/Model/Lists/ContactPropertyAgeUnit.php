<?php
/**
 * お問い合わせ　築年数
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyAgeUnit extends ListAbstract {

    static protected $_instance;
    
    protected $_list = array(
        'showa'   => '昭和',
        'heisei'  => '平成',
        'reiwa'   => '令和',
        'seireki' => '西暦',
    );
}