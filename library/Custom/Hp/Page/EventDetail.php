<?php
namespace Library\Custom\Hp\Page;

use App\Repositories\HpMainParts\HpMainPartsRepository;

class EventDetail extends AbstractPage\Detail {

	protected $_mainParts = array(
		HpMainPartsRepository::PARTS_EVENT_DETAIL,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_EVENT_DETAIL,
			)
		),
	);

	protected $_requiredMainParts = array(
			HpMainPartsRepository::PARTS_EVENT_DETAIL,
	);
}