<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\AbstractPage\Form;
use Library\Custom\Hp\Page\SectionParts\Form\RequestOfficebuy;
use Library\Custom\Hp\Page\SectionParts\Tdk;
use Library\Custom\Model\Lists\HpPagePlaceholderData;

class FormRequestOfficebuy extends Form {
	
	public function initContents() {
    	$tdk = new Tdk(array('hp' => $this->getHp(), 'page'=>$this->getRow()));

    	//プレースフォルダーを設定する
    	$placeholder = new HpPagePlaceholderData();
    	$data = $placeholder->get($this->_row->page_type_code);
    	foreach($tdk->getElements() as $name => $element) {
    		if(isset($data[$name])) $element->setAttribute('placeholder', $data[$name]);
    	}

    	if (!$tdk->getElement('filename')->getValue()) {
    		$tdk->getElement('filename')->setValue($this->_default_filename);
    	}
    	
    	$this->form->addSubForm($tdk, 'tdk');

		$options = array('hp' => $this->getHp(), 'page'=>$this->getRow());
		$this->form->addSubForm($this->_createFormParts($options), 'form');
	}

	protected function _createFormParts($options) {
		return new RequestOfficebuy($options);
	}
}