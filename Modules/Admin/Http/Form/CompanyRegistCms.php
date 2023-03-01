<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Regex;

class CompanyRegistCms extends Form
{

	public function init()
	{
		

		//ＣＭＳ情報枠 #########################
		//ログインID
		$element = new Element\Hidden('account_id');
		// $element->addValidator('Digits', false, array('messages' => '数値のみです。'));
		$this->add($element);

		//ログインID
		$element = new Element\Text('login_id');
		$element->setLabel('ログインID');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 8)));
		$element->addValidator(new Regex (array('pattern' => '/^[a-zA-Z0-9\s]+$/', 'messages' =>'半角英数字のみです。')));
		$this->add($element);

		//パスワード
		$element = new Element\Text('password');
		$element->setLabel('パスワード');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$element->addValidator(new Regex (array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字記号のみです。')));
		$this->add($element);
	}
}
