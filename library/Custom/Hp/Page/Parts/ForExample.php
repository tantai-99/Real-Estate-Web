<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class ForExample extends HasElement {

	protected $_title = '実例紹介';
	protected $_template = 'for-example';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'example'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'example') {
			$element = new Element\ForExample();
		}
		return $element;
	}
}