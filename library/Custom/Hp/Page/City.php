<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\ForAb;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class City extends ForAb {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_CITY,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_CITY,
			)
		),
	);

}