<?php
namespace Library\Custom\Model\Lists;

class Recruit extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1  => '仕事内容',
        2  => '応募資格',
        3  => '給与',
        4  => '賃金改定',
        5  => '諸手当',
        6  => '勤務地',
        7  => '勤務時間',
        8  => '休日',
        9  => '休暇',
        10 => '福利厚生',
        11 => '教育研修',
        12 => '採用に関する問い合わせ先',
        13 => '応募方法',
        14 => 'PRコメント',
    );

    protected $_chinese = array(
        1  => '职位描述',
        2  => '报名资格',
        3  => '薪水',
        4  => '工资调整',
        5  => '津贴',
        6  => '工作地点',
        7  => '工作时间',
        8  => '休息日',
        9  => '假期',
        10 => '福利',
        11 => '培训与发展（教育培训）',
        12 => '联系方式',
        13 => '报名方法',
        14 => 'PR',
    );
}