<?php
/**
 * お問い合わせ　物件の現況
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyAreaUnit extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '坪',
        2 => 'm<sup>2</sup>',
    );
}