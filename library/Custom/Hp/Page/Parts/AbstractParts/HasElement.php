<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;

use Library\Custom\Hp\Page\Parts\PartsAbstract;

class HasElement extends PartsAbstract {

	protected $_has_element = true;
	protected $_requiredTypes = array();

	protected $_max_element_count = 0;

	public function createPartsElement($type, $elementNo, $assign = true) {
		if ($this->_max_element_count && $this->_max_element_count <= count($this->getSubForm('elements')->getSubForms())) {
			return null;
		}
		$element = parent::createPartsElement($type, $elementNo, $assign);
		if ($element && $this->isRequiredType($type)) {
			$element->setRequired(true);
			if ($element->getElement('value')){
				$element->getElement('value')->setRequired(true);
			}
		}
		return $element;
	}

	public function getMaxElementCount() {
		return $this->_max_element_count;
	}
	
	public function isValid($data, $checkError = true) {
		if ($this->_requiredTypes) {
			$elementTypes = array();
			foreach ($this->getSubForm('elements')->getSubForms() as $name => $elem) {
				$elementTypes[] = $elem->type->getValue();
			}

			foreach ($this->_requiredTypes as $type) {
				if (!in_array($type, $elementTypes)) {
					throw new \Exception('required element not found');
				}
			}
		}

		return parent::isValid($data, $checkError = true);
	}
	
	public function isRequiredType($type) {
		return in_array($type, $this->_requiredTypes);
	}
}