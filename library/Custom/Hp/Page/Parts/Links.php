<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class Links extends HasElement {

	protected $_title = 'リンク集';
	protected $_template = 'links';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'link'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'link') {
			$element = new Element\Link();
		}
		return $element;
	}
}