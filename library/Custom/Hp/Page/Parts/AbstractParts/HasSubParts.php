<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;

class HasSubParts extends HasElement {

	public function setPreset() {
		parent::setPreset();

		foreach ($this->getSubForm('elements')->getSubForms() as $subParts) {
			$subParts->setPreset();
		}
		return $this;
	}

	public function forTemplate() {
		parent::forTemplate();

		foreach ($this->getSubForm('elements')->getSubForms() as $subParts) {
			$subParts->forTemplate();
		}
		return $this;
	}
}