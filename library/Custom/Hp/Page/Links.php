<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Links extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_LINKS,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_LINKS,
			)
		),
	);

}