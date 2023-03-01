<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class Auth extends Form {
	
	public function __construct() {
		$element = new Element\Text('login_id');
		$element->setLabel('ユーザID');
		$element->setAttributes([
			'class' => 'user',
			'placeholder' => 'ユーザIDを入力してください'
		]);
		$element->setRequired(true);
		$this->add($element);
		
		$element = new Element\Password('password');
		$element->setLabel('パスワード');
		$element->setAttributes([
			'class' => 'pass',
			'placeholder' => 'パスワードを入力してください'
		]);
		$element->setRequired(true);
		$this->add($element);
	}
	
}