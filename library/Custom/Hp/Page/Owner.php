<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\ForAb;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Owner extends ForAb {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_FOR_SERVICE_INTRODUCTION,
			HpMainPartsRepository::PARTS_FOR_EXAMPLE,
			HpMainPartsRepository::PARTS_FOR_OWNER_REVIEW,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_SERVICE_INTRODUCTION,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_EXAMPLE,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_OWNER_REVIEW,
			)
		),
	);

}