<?php
namespace Library\Custom\Model\Lists;

class StaffDetail extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1  => 'ニックネーム',
        2  => '年齢',
        3  => '部署名',
        4  => 'ブログ',
        5  => '得意エリア',
        6  => '特技',
        7  => '経験年数',
        8  => '種別',
        9  => '店舗名',
        10 => '氏名',
        11 => 'ふりがな',
        12 => '画像のタイトル',
        13 => '出身',
        14 => '趣味',
        15 => '資格',
        16 => 'PRコメント',
    );


    protected $_chinese = array(
        1  => '昵称',
        2  => '年龄',
        3  => '部门',
        4  => '博客',
        5  => '擅长邻域',
        6  => '专长',
        7  => '工作经验年数',
        // 8  => '',
        // 9  => '',
        // 10 => '',
        // 11 => '',
        // 12 => '',
        13 => '出神地',
        14 => '爱好',
        15 => '资格证书',
        // 16 => '',
    );
}


