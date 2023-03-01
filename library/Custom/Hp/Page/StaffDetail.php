<?php
namespace Library\Custom\Hp\Page;

use App\Repositories\HpMainParts\HpMainPartsRepository;

class StaffDetail extends AbstractPage\Detail {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_STAFF_DETAIL,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_STAFF_DETAIL,
			)
		),
	);
	
	protected $_requiredMainParts = array(
			HpMainPartsRepository::PARTS_STAFF_DETAIL,
	);
}