<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class History extends HasElement {

	protected $_title = '会社沿革';
	protected $_template = 'history';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'history'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'history') {
			$element = new Element\History();
		}
		return $element;
	}
}