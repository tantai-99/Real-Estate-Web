<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use App\Rules\EmailAddress;

class Email extends Text {

	protected $_valueMaxLength = 255;
	
	public function init() {
		parent::init();

		$this->getElement('value')->addValidator(new EmailAddress());
	}
}