<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;

class ForDownloadApplication extends HasElement {

	protected $_title = '申請書のダウンロード';
	protected $_template = 'for-download-application';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'file'
	);

	protected $_max_element_count = 5;

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'file') {
			$element = new Element\ForDownloadApplication();
		}
		return $element;
	}
}