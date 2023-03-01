<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\ForAb;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Proprietary extends ForAb {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_DESCRIPTION,
			HpMainPartsRepository::PARTS_FOR_SERVICE,
			HpMainPartsRepository::PARTS_FOR_SUPPORT,
			HpMainPartsRepository::PARTS_FOR_CASE,
			HpMainPartsRepository::PARTS_FOR_DOWNLOAD_APPLICATION,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_DESCRIPTION,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_SERVICE,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_SUPPORT,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_CASE,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_FOR_DOWNLOAD_APPLICATION,
			)
		),
	);

}