<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\Form;
use Library\Custom\Hp\Page\SectionParts\Form\Contact;

class FormContact extends Form {

	protected function _createFormParts($options) {
		return new Contact($options);
	}
}