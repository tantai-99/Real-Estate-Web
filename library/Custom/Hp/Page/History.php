<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class History extends HpPage {

	protected $_default_filename = '';
	
	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_HISTORY,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_HISTORY,
			)
		),
	);

}