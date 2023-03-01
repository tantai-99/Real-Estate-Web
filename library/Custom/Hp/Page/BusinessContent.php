<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class BusinessContent extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_BUSINESS_CONTENT,
	);
	
	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_BUSINESS_CONTENT,
			)
		),
	);
}
