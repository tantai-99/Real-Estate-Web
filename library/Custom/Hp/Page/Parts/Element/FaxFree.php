<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use App\Rules\Fax;

class FaxFree extends TextFree {

	protected $_valueMaxLength = 100;
	
	public function init() {
		parent::init();

		$this->getElement('value')->addValidator(new Fax());
	}
}