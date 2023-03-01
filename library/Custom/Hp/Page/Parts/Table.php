<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class Table extends HasElement {

	protected $_title = '表';
	protected $_template = 'table';

	protected $_presetTypes = array(
			'free'
	);

	protected function _createPartsElement($type) {
		$element = null;

		if ($type == 'free') {
			$element = new Element\TextFree();
		}
		return $element;
	}

}