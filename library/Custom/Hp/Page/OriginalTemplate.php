<?php

namespace Library\Custom\Hp\Page;

use Library\Custom\Hp\Page;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class OriginalTemplate extends Page
{

	protected $_mainParts = array(
		HpMainPartsRepository::PARTS_ORIGINAL_TEMPLATE,
		HpMainPartsRepository::PARTS_SET_LINK_AUTO,
	);
	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_ORIGINAL_TEMPLATE,
			),
			array(
				HpMainPartsRepository::PARTS_SET_LINK_AUTO,
			)
		),
	);
}
