<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\InArray;
use Library\Custom\Model\Lists\ManagerAccountAuthority;


class AccountRegist extends Form
{

	public function init()
	{
		//companyid
		$element = new Element\Hidden('id');
		$this->add($element);

		//担当者名
		$element = new Element\Text('name');
		$element->setLabel('担当者名');
		$element->setRequired(true);
		//		$element->addValidator('StringLength', false, array('max' => 30, 'messages' => '30文字以内で入力してください'));
		$element->addValidator(new StringLength(array('max' => 30)));
		$this->add($element);

		//ログインID
		$element = new Element\Text('login_id');
		$element->setLabel('ログインID');
		$element->setRequired(true);
		$element->addValidator('alpha_num');
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

		//パスワード
		$element = new Element\Password('password');
		$element->setLabel('パスワード');
		$element->setRequired(true);
		$element->addValidator('alpha_num');
		//		$element->addValidator('StringLength', false, array('min' => 8, 'max' => 30, 'messages' => '8文字以上30文字以内で入力してください'));
		$element->addValidator(new StringLength(array('min' => 8, 'max' => 30)));
		$this->add($element);

		//権限
		$element = new Element\Checkbox('privilege_flg');
		$element->setLabel('権限');
		$element->setRequired(true);
		$element->addValidator(new InArray(array_keys(ManagerAccountAuthority::getInstance()->getAll())));
		$element->setSeparator('　　');
		$element->setValueOptions(ManagerAccountAuthority::getInstance()->getAll());
		$this->add($element);
	}

	public function getAgencyAuthorityElements()
	{
		$element = $this->getElement('privilege_flg');
		$element->setMultiOptions(ManagerAccountAuthority::getInstance()->getListPrivilegeByAgency());
	}
}
