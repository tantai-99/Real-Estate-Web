<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Form\Element;
class Checkbox extends ElementAbstract {

	protected $_valueClass = 'Library\Custom\Form\Element\Checkbox';
	protected $_columnMap = array(
			'contact' => 'attr_1',
	);


	public function init() {
		parent::init();

		$element = new $this->_valueClass('contact', array('disableLoadDefaultDecorators'=>true));
        // $element->setRequired(true);
        $element->setValue(1);
		$this->add($element);
	}
}