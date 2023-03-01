<?php
/**
 * お問い合わせ　物件の現況
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyCellReason extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '住み替えの為',
        2 => '相続',
        3 => '転勤',
        4 => '離婚',
        5 => '金銭的理由',
        6 => '他社で売れない',
        7 => '財産分与',
        8 => '所有者が高齢のため',
        9 => '人に頼まれた',
        10 => '知人・親族に売るため',
        11 => '任意売却',
        12 => '資産整理',
        13 => 'その他',
    );
}