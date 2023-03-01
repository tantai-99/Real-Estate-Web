<?php

class Api_Form_Contact_RequestLivingbuy extends Api_Form_Contact_Abstract {

	public function init() {
		parent::init();
	}
	
	public function isValid($data) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementsBelongTo());

		return parent::isValid($data);
	}
}