<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class Recruit extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'recruit';

	protected $_title = '採用情報';

	protected $_presetTypes = array(
			'recruit'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'recruit') {
			$element = new Element\Recruit();
		}

		return $element;
	}
}