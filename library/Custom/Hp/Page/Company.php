<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Company extends HpPage {

	protected $_default_filename = '';
	
	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_COMPANY_OUTLINE,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_COMPANY_OUTLINE,
			)
		),
		array(
			array(
				HpMainPartsRepository::PARTS_IMAGE,
			),
			array(
				HpMainPartsRepository::PARTS_IMAGE,
			),
		),
		array(
			array(
				HpMainPartsRepository::PARTS_MAP,
			)
		),
	);

	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Hp\Page::_decoratePresetParts()
	 */
	protected function _decoratePresetParts($parts, $type) {
		if ($type == HpMainPartsRepository::PARTS_MAP) {
			$parts->getElement('heading')->setValue('アクセスマップ');
		}
	}
}