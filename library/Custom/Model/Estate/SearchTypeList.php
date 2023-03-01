<?php
namespace Library\Custom\Model\Estate;

class SearchTypeList extends AbstractList {

	static protected $_instance;

	const TYPE_AREA  = 1;
	const TYPE_ENSEN = 2;
	const TYPE_SPATIAL = 3;

	protected $_list = [
		self::TYPE_AREA=>'地域から探す',
		self::TYPE_ENSEN=>'沿線・駅から探す',
		self::TYPE_SPATIAL=>'地図から探す',
	];

	public function getKeyConst() {
		return [
			'TYPE_AREA' => self::TYPE_AREA,
			'TYPE_ENSEN' => self::TYPE_ENSEN,
			'TYPE_SPATIAL' => self::TYPE_SPATIAL,
		];
	}

	public function getAllForSpecialDirect() {
		return [
            '1-0'   => '地域から探す（市区郡から指定する）',
            '1-1'   => '地域から探す（町名から指定する）',
			'2'     => '沿線・駅から探す（沿線・駅で指定する）',
			'3'     => '地図から探す（地図で抽出する）'
		];
	}
}