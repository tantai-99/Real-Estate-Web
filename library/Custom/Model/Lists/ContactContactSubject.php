<?php
/**
 * 会社問い合わせのお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactContactSubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => 'お店に直接訪問したい',
        2 => '希望条件に合う物件を紹介してほしい',
        3 => '入居・購入に関して相談したい',
    );
}
