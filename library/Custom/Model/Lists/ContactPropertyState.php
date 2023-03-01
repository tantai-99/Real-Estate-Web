<?php
/**
 * お問い合わせ　物件の現況
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyState extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '居住中',
        2 => '賃貸中',
        3 => '空室',
        4 => '上物有（土地）',
        5 => '更地（土地）',
    );
}