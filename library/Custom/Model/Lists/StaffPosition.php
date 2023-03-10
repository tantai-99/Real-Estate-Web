<?php
namespace Library\Custom\Model\Lists;

class StaffPosition extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1  => '店長',
        2  => '副店長',
        3  => '所長',
        4  => '副所長',
        5  => 'センター長',
        6  => '副センター長',
        7  => '部長',
        8  => '次長',
        9  => '副部長',
        10 => 'マネージャー',
        11 => '課長',
        12 => '主任',
        13 => 'チーフ',
        14 => '営業',
        15 => '営業担当',
        16 => '担当',
        17 => '事務',
    );

    protected $_chinese = array(
        1  => '店长',
        2  => '副店长',
        3  => '所长',
        4  => '副所长',
        5  => '中心长',
        6  => '副中心长',
        7  => '部长',
        8  => '次长',
        9  => '副部长',
        10 => '经理',
        11 => '科长',
        12 => '主任',
        13 => '首席',
        14 => '销售',
        15 => '销售担当',
        16 => '担当',
        17 => '事务人员',
    );
}