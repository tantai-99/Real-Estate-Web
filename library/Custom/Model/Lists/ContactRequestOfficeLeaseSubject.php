<?php
/**
 * 事業用賃貸物件のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactRequestOfficeLeaseSubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'〇〇ビルの貸オフィスの物件を紹介して欲しい',
        2 =>'〇〇駅の〇〇出口側にあるおすすめ物件を紹介して欲しい',
        3 =>'〇〇用の居抜き物件を紹介して欲しい',
    );
}