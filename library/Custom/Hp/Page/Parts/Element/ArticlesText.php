<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
class ArticlesText extends ElementAbstract {

	protected $_columnMap = array(
		'description'	=> 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows',6);
		$this->add($element);
	}


	public function isValid($data, $checkError = true) {
		return parent::isValid($data);
	}
}