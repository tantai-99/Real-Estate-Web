<?php

namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\Regex;
use App\Rules\StringLength;

class RePassword extends Form
{

	public function __construct()
	{
		parent::__construct();

		//companyid
		$element = new Element\Hidden('id');
		$this->add($element);

		//現在のパスワード
		$element = new Element\Text('password');
		$element->setLabel('現在のパスワード');
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9#$&]+$/', 'messages' => '半角英数字(記号は#$&)のみです。')));
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

		//新しいパスワード
		$element = new Element\Text('new_password');
		$element->setLabel('新パスワード');
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9#$&]+$/', 'messages' => '半角英数字(記号は#$&)のみです。')));
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

		//新しいパスワード(確認)
		$element = new Element\Text('re_new_password');
		$element->setLabel('確認用パスワード');
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9#$&]+$/', 'messages' => '半角英数字(記号は#$&)のみです。')));
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);
	}
}
