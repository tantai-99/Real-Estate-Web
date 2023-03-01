<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class City extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'city';

	protected $_title = '街情報';

	protected $_presetTypes = array(
			'city'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'city') {
			$element = new Element\City();
		}

		return $element;
	}
}