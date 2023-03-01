<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use App\Rules\Url as RulesUrl;

class Url extends Text {

	protected $_valueMaxLength = 2000;
	
	public function init() {
		parent::init();

		$this->getElement('value')->addValidator(new RulesUrl());
	}
}