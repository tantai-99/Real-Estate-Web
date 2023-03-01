<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class PurchasingRealEstate extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_PURCHASING_REAL_ESTATE,
			HpMainPartsRepository::PARTS_SET_LINK_AUTO,
	);
	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_PURCHASING_REAL_ESTATE,
				// HpMainPartsRepository::PARTS_SET_LINK_AUTO,
			)
		),
	);

}