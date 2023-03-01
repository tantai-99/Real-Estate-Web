<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
class Select extends ElementAbstract {

	protected $_template = 'hp-page.parts.table.select';

	protected $_columnMap = array(
			'value' => 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new Element\Select('value', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);
	}

	public function setValueOptions($options) {
		$this->getElement('value')->setValueOptions($options);
		return $this;
	}

	public function getValueOptions() {
		return $this->getElement('value')->getValueOptions();
	}
}