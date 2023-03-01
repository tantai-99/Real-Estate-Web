<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class ForService extends HasElement {

	protected $_title = 'サービス内容';
	protected $_template = 'for-service';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'service'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'service') {
			$element = new Element\ForService();
		}
		return $element;
	}
}