<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\Element;

class InfoDetail extends Table {

	protected $_is_unique = true;

	protected $_title = 'お知らせ';

	protected $_has_heading = false;

	protected $_presetTypes = array(
			'html',
			'image'
	);

	protected $_freeTypes = array(
	);

	protected function _createPartsElement($type) {
		$element = null;
		switch ($type) {
			case 'html':
				$element = new Element\Html();
				$element->setTitle('本文');
				break;
			case 'image':
				$element = new Element\Image();
				$element->setTitle('画像');
				$element->useImageTitle();
				break;
			default:
				break;
		}

		return $element;
	}
}