<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Hp\Page\Parts\Element;

class BusinessContent extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'business_content';

	protected $_title = '事業内容';

	protected $_presetTypes = array(
			'business_content'
	);

	protected $_has_heading = false;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'business_content') {
			$element = new Element\BusinessContent();
		}
		return $element;
	}
}
