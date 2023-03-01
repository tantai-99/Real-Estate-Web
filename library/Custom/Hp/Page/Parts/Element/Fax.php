<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use App\Rules\Fax as RulesFax;

class Fax extends Text {

	protected $_valueMaxLength = 100;
	
	public function init() {
		parent::init();

		$this->getElement('value')->addValidator(new RulesFax());
	}
}