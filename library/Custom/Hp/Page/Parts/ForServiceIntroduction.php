<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class ForServiceIntroduction extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'for-service-introduction';

	protected $_title = 'サービス紹介';

	protected $_presetTypes = array(
			'service'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'service') {
			$element = new Element\ForServiceIntroduction();
		}

		return $element;
	}
}