<?php
namespace Library\Custom\Model\Estate;

class SearchTypeCondition extends AbstractList {

	static protected $_instance;

    const TYPE_CITY   = 1;
    const TYPE_CHOSON = 2;
	const TYPE_ENSEN  = 3;
	const TYPE_FILTER = 4;

	protected $_list = [
		self::TYPE_CITY=>'地域から探す（市区郡から指定する）',
		self::TYPE_CHOSON=>'地域から探す（町名から指定する）',
        self::TYPE_ENSEN=>'沿線・駅から探す（沿線・駅で指定する）',
        self::TYPE_FILTER=>'絞り込み条件のみで指定する',
	];
}