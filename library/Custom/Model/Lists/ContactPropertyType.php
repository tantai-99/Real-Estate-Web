<?php
/**
 * お問い合わせ　物件の種別
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyType extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => 'マンション',
        2 => '一戸建て',
        3 => '土地',
        4 => '1棟マンション・アパート',
    );
}