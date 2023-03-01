<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use Library\Custom\Form\Element;

class FreeItem extends Multi {

	protected $_free_choice_count = 10;

	protected $_default_value = '';
	public function setDefaultValue($key) {
		$this->_default_value = $key;
		return $this;
	}
	public function getDefaultValue() {
		return $this->_default_value;
	}

	public function init() {
		parent::init();

		$element = new Element\Radio('choices_type_code');
		$element->setValueOptions(array(
			'checkbox'	=> 'チェックボックス',
			'select'	=> 'プルダウン',
			// 'radio'		=> 'ラジオボタン',
			'text'		=> 'テキストボックス（1行）',
			'textarea'	=> 'テキストボックス（複数行）'
		));

		$element->setValue('checkbox');
		if($this->getDefaultValue() != "") $element->setValue($this->getDefaultValue());
		$element->setSeparator("</li>\n<li>");
		$this->add($element);
	}
}