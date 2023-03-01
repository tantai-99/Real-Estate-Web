<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class RentalInitialCost extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_RENTAL_INITIAL_COST,
			HpMainPartsRepository::PARTS_SET_LINK_AUTO,
	);
	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_RENTAL_INITIAL_COST,
				// HpMainPartsRepository::PARTS_SET_LINK_AUTO,
			)
		),
	);

}