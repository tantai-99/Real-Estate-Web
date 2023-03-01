<?php
/**
 * 居住用売買物件のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactRequestLivingBuySubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'〇〇マンションの物件を紹介して欲しい',
        2 =>'〇〇小学校区内のおすすめ物件を紹介して欲しい',
        3 =>'リノベーションが可能なおすすめ物件を紹介して欲しい',
    );
}