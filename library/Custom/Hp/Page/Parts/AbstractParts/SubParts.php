<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Form\Element;
use Library\Custom\Form;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;

class SubParts extends HasElement {

	protected $_is_preset = false;

	public function init() {
		$element = new Element\Hidden('type', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$element = new Element\Hidden('sort', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('class', 'sort-value');
		$element->setRequired(true);
		$this->add($element);

		$this->addSubForm(new Form(), 'elements');
	}

	public function createPartsElement($type, $elementNo, $assign = true) {
		$element = parent::createPartsElement($type, $elementNo, $assign);
		if ($element) {
			$element->setIsSubElement(true);
		}
		return $element;
	}

	public function setType($type) {
		$this->getElement('type')->setValue($type);
		return $this;
	}

	public function getType() {
		return $this->getElement('type')->getValue();
	}

	public function setIsPreset($isPreset) {
		$this->_is_preset = $isPreset;
		return $this;
	}

	public function isPreset() {
		return $this->_is_preset;
	}


	public function getSaveTable() {
		return \App::make(HpMainElementRepositoryInterface::class);
	}

	protected function _beforeSave($data) {
		if (isset($data['area_id'])) {
			$data['parts_id'] = $data['area_id'];
			unset($data['area_id']);
		}
		return $data;
	}
}