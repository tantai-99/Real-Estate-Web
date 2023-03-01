<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class Qa extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'qa';

	protected $_title = 'Q&A';

	protected $_presetTypes = array(
			'qa'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'qa') {
			$element = new Element\Qa();
		}

		return $element;
	}
}