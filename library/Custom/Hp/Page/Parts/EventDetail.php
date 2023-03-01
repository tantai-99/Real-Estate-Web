<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element\EventDetail as EDetail;

class EventDetail extends HasElement {

	protected $_title = 'イベント情報';
	protected $_template = 'event-detail';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'event'
	);

	protected $_requiredTypes = array(
			'event'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'event') {
			$element = new EDetail();
		}
		return $element;
	}
}