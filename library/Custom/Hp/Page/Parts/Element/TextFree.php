<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;

class TextFree extends Text {

	protected $_titleMaxLength = 100;

	protected $_columnMap = array(
			'value' => 'attr_1',
			'title' => 'attr_2',
	);

	public function init() {
		parent::init();

		$element = new Element\Text('title', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$this->add($element);

		$this->setMaxLength($this->_titleMaxLength, 'title');
	}

	public function setTitle($title) {
		parent::setTitle($title);
		$this->getElement('title')->setValue($title);
		$this->getElement('title')->setAttribute('placeholder', $title);
		return $this;
	}
}