<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Hp\Page\Parts\Element;

class ForReview extends HasElement {

	protected $_title;
	protected $_reviewer;
	protected $_template = 'for-review';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'review'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'review') {
			$element = new Element\ForReview();
		}
		return $element;
	}

	public function getReviewer() {
		return $this->_reviewer;
	}
}