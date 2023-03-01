<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class SelectFree extends Select {

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

		$this->setMaxLength($this->_titleMaxLength);
	}

	public function setTitle($title) {
		parent::setTitle($title);
		$this->getElement('title')->setValue($title);
		$this->getElement('title')->setAttribute('placeholder', $title);
		return $this;
	}

	public function setMaxLength($max, $name = 'title') {
		$validator = $this->getElement($name)->getValidator('StringLength');
		if (!$validator) {
			$validator = new StringLength();
			$validator->setMin(null);
			$this->getElement($name)->addValidator($validator);
		}
		$validator->setMax($max);
		$this->getElement($name)->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);

		return $this;
	}
}