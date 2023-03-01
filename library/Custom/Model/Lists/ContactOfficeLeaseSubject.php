<?php
/**
 * 物件問い合わせ（事業用賃貸）のお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class ContactOfficeLeaseSubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 =>'物件の詳細を知りたい（周辺環境や条件など）',
        2 =>'実際に物件を見たい',
        3 =>'最新の空き状況を知りたい',
        4 =>'写真や間取図等をもっと見たい',
        5 =>'条件に合う他の物件も紹介して欲しい',
        6 =>'お店に行って相談したい',
    );
}