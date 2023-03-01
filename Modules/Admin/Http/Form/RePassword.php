<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class RePassword extends Form {
	
	public function init()
	{

		

		//companyid
		$element = new Element\Hidden('id');
		$this->add($element);

		//現在のパスワード
		$element = new Element\Password('current_password');
		$element->setLabel('現在のパスワード');
		$element->setRequired(true);
		$element->addValidator('alpha_num');
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

		//新しいパスワード
		$element = new Element\Password('password');
		$element->setLabel('新パスワード');
		$element->setRequired(true);
		$element->addValidator('alpha_num');
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));

		$this->add($element);

		//パスワード(差異)
		$element = new Element\Password('re_password');
		$element->setLabel('確認用パスワード');
		$element->setRequired(true);
		$element->addValidator('alpha_num');
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

	}
	
}