<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Repositories\HpContactParts\HpContactPartsRepository;

class AbstractElement extends Form {

	protected $_is_required = false;

	protected $_default_required_type;

	public function setIsRequired($isRequired) {
		$this->_is_required = $isRequired;
		return $this;
	}

	public function isRequired() {
		return $this->_is_required;
	}

	public function setDefaultRequiredType($requiredType) {
		$this->_default_required_type = $requiredType;
	}

	protected $_title;

	public function setTitle($title) {
		$this->_title = $title;
		return $this;
	}

	public function getTitle() {
		return $this->_title;
	}

	public function init() {
		parent::init();
		$element = new Element\Hidden('sort');
		$element->setAttribute('class', 'sort-value');
		$element->setRequired(true);
		$element->setValue(0);
		$this->add($element);

		$element = new Element\Radio('required_type');
		$element->setRequired(true);

		if ($this->_is_required) {
			$options = array(
				HpContactPartsRepository::REQUIREDTYPE_REQUIRED => '必須',
			);
		}
		else {
			$options = array(
				HpContactPartsRepository::REQUIREDTYPE_REQUIRED => '必須',
				HpContactPartsRepository::REQUIREDTYPE_OPTION   => '任意',
				HpContactPartsRepository::REQUIREDTYPE_HIDDEN   => '非表示',
			);
		}
		$element->setValueOptions($options);
		$element->setValue($this->_default_required_type);
		$element->setSeparator("</li>\n<li>");
		$this->add($element);
	}
}