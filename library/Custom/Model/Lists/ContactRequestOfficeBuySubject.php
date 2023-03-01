<?php
/**
 * 事業用売買物件のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactRequestOfficeBuySubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'〇〇マンションの物件を紹介して欲しい',
        2 =>'利回りが〇〇％以上の投資用物件を紹介して欲しい',
        3 =>'〇〇用のおすすめ物件を紹介して欲しい',
    );
}