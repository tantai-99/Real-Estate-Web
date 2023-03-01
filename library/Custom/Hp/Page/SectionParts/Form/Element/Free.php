<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use Library\Custom\Form\Element;

class Free extends Multi {

	protected $_free_choice_count = 10;
	protected $_type = null;

	public function setTitle($title) {
		$this->getElement('item_title')->setValue($title);
		return parent::getTitle();
	}

	public function setType($type) {
		$this->_type = $type;
		return $this;
	}

	public function init() {
		parent::init();

		$element = new Element\Text('item_title');
		$element->setRequired(true);
		$this->add($element);

		$element = new Element\Radio('choices_type_code');
		$element->setValueOptions(array(
			'checkbox'	=> 'チェックボックス',
			'select'	=> 'プルダウン',
			'text'		=> 'テキストボックス（1行）',
			'textarea'	=> 'テキストボックス（複数行）'
		));
		$element->setValue('checkbox');
		$element->setSeparator("</li>\n<li>");
		$this->add($element);

	}
}