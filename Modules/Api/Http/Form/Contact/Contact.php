<?php
namespace Modules\Api\Http\Form\Contact;

class Contact extends ContactAbstract {

	public function init() {

		parent::init();
	}
	
	public function isValid($data, $checkError = true) {

		$this->setData($data);

		return parent::isValid($data);
	}
}