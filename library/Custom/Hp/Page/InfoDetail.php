<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\Detail;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class InfoDetail extends Detail {

	protected $_default_filename = '';
	
	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_INFO_DETAIL,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_INFO_DETAIL,
			)
		),
	);
}