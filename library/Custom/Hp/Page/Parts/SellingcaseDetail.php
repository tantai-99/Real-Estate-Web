<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class SellingcaseDetail extends HasElement {

	protected $_title = '売却事例';
	protected $_template = 'sellingcase-detail';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'sellingcase'
	);
	
	protected $_requiredTypes = array(
			'sellingcase'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'sellingcase') {
			$element = new Element\SellingcaseDetail();
		}
		return $element;
	}
}