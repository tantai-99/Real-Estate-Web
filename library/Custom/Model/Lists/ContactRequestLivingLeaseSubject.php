<?php
/**
 * 居住用賃貸物件のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactRequestLivingLeaseSubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'〇〇マンションの物件を紹介して欲しい',
        2 =>'〇〇大学の近くでおすすめの物件を紹介して欲しい',
        3 =>'初期費用が抑えられるおすすめ物件を紹介して欲しい',
    );
}