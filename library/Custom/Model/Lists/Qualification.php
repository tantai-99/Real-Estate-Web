<?php
namespace Library\Custom\Model\Lists;

class Qualification extends ListAbstract {

    static protected $_instance;

    const PAGE = 1;
    const URL  = 2;

    protected $_list = array(
        1  => '宅地建物取引士',
        2  => '公認　不動産コンサルティングマスター',
        3  => '一級建築士',
        4  => '二級建築士',
        5  => '不動産鑑定士',
        6  => '1級ファイナンシャルプランニング技能士',
        7  => '2級ファイナンシャルプランニング技能士',
        8  => '3級ファイナンシャルプランニング技能士',
        9  => '司法書士',
        10 => '土地家屋調査士',
        11 => '損害保険募集人資格',
        12 => '住宅ローンアドバイザー',
        13 => 'マンション管理士',
        14 => 'インテリアコーディネーター',
        15 => '管理業務主任者',
        16 => '行政書士',
        17 => '税理士',
        18 => '公認会計士',
    );

    protected $_chinese = array(
        1  => '有资格的房地产经纪人',
        2  => '注册房地产咨询师',
        3  => '1级建筑师',
        4  => '2级建筑师',
        5  => '房地产评估师',
        6  => '1级财务理财规划技能士',
        7  => '2级财务理财规划技能士',
        8  => '3级财务理财规划技能士',
        9  => '司法书士',
        10 => '房地产测量师',
        11 => '保险募集人资格',
        12 => '按揭顾问',
        13 => '公寓管理士',
        14 => '室内设计师助理',
        15 => '管理业务主任',
        16 => '行政书士',
        17 => '税理士',
        18 => '注册会计师',
    );


}