<?php
/**
 * 物件問い合わせ（居住用売買）のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactOfficeBuySubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'物件の詳細を知りたい（周辺環境や条件など）',
        2 =>'資料を送って欲しい',
        3 =>'実際に物件を見たい',
        4 =>'最新の販売状況を知りたい',
        5 =>'写真や間取図等をもっと見たい',
        6 =>'条件に合う他の物件も紹介して欲しい',
        7 =>'購入について相談したい',
        8 =>'お店に行って相談したい',
    );
}