<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class Auth extends Form {
    public function init() {

        $element = new Element\Text('login_id');
        $element->setLabel('ユーザID');
        $element->setRequired(true);
        $element->setAttributes([
            'class' => ['user'],
            'placeholder'  => 'ユーザIDを入力してください',
        ]);
		$this->add($element);

		
		$element = new Element\Password('password');
        $element->setLabel('パスワード');
        $element->setRequired(true);
		$element->setAttributes([
            'class' => ['pass'],
            'placeholder'  => 'パスワードを入力してください',
        ]);
        $this->add($element);
    }

}