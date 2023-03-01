<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\Detail;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class ColumnDetail extends Detail {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_COLUMN_DETAIL,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_COLUMN_DETAIL,
			)
		),
	);
}