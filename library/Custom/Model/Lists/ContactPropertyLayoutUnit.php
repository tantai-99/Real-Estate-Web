<?php
/**
 * お問い合わせ　間取り
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyLayoutUnit extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => 'R',
        2 => 'K',
        3 => 'DK',
        4 => 'LDK',
    );
}
