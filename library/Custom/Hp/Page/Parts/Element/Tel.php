<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use App\Rules\Tel as RulesTel;

class Tel extends Text {

	protected $_valueMaxLength = 100;
	
	public function init() {
		parent::init();

		$this->getElement('value')->addValidator(new RulesTel());
	}
}