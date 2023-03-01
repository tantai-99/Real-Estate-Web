<?php

/**
 * 入居予定時期
 *
 */
namespace Library\Custom\Model\Lists;

class ContactPropertyMoveinPlan extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => 'すぐに ',
        2 => '希望物件があり次第',
        3 => '3ヶ月以内に',
        4 => '6ヶ月以内に',
        5 => '検討中',
    );

    protected $_chinese = array(
        1 => '马上',
        2 => '只要有条件合适的物件',
        3 => '3个月内',
        4 => '6个月内',
        5 => '考虑中',
    );
}