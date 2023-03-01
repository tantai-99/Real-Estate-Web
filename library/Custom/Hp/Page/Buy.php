<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Buy extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_BUY,
			HpMainPartsRepository::PARTS_SET_LINK_AUTO,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_BUY,
				// HpMainPartsRepository::PARTS_SET_LINK_AUTO,
			)
		),
	);

}