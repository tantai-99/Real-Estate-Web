<?php
namespace Library\Custom\Hp\Page\Parts\Element;

class Html extends Textarea {

	public function init() {
		parent::init();

		$this->getElement('value')->setAttribute('rows', 15);
	}
}