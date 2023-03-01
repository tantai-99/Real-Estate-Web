<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class LoginByMemberNo extends Form {

	public function init() {
		
		$element = new Element\Text('member_no');
		$element->setLabel('会員番号（会員No）');
		$element->setRequired(true);
		$this->add($element);
		
	}
}