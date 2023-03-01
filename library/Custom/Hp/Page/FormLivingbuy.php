<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\Form;
use Library\Custom\Hp\Page\SectionParts\Form\Livingbuy;

class FormLivingbuy extends Form {

	public function initContents() {
		$options = array('hp' => $this->getHp(), 'page'=>$this->getRow());
		$this->form->addSubForm($this->_createFormParts($options), 'form');
	}

	protected function _createFormParts($options) {
		return new Livingbuy($options);
	}
}