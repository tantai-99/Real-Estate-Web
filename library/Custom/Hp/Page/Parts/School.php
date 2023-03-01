<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class School extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'school';

	protected $_title = '学区情報';

	protected $_presetTypes = array(
			'school'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'school') {
			$element = new Element\School();
		}

		return $element;
	}
}