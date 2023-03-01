<?php
namespace Library\Custom\Hp\Page\Parts\Element; 
use Library\Custom\Form\Element;
class TextareaLinkAuto extends ElementAbstract {

	protected $_columnMap = array(
		'lead'			=> 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('lead', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
		$this->add($element);
	}


	public function isValid($data, $checkError = true) {
		return parent::isValid($data);
	}
}