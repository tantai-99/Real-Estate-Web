<?php
namespace Library\Custom\Hp\Page;

use App\Repositories\HpMainParts\HpMainPartsRepository;

class ShopDetail extends AbstractPage\Detail {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_SHOP_DETAIL,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_SHOP_DETAIL,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_MAP,
			)
		),
	);
	
	protected $_requiredMainParts = array(
			HpMainPartsRepository::PARTS_SHOP_DETAIL,
	);
}