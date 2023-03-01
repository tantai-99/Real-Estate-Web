<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class Lists extends HasElement {

	protected $_title = 'リスト';
	protected $_template = 'list';

	protected $_presetTypes = array(
			'list'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'list') {
			$element = new Element\Lists();
		}
		return $element;
	}
}