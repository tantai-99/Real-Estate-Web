<?php
namespace Library\Custom\Hp\Page\Parts\Element;

class Lists extends Textarea {

	protected $_valueMaxLength = 1000;
	
	public function init() {
		parent::init();
		// $this->value->rows = 1;
		$this->getElement('value')->setAttribute('rows', 1);
	}

}