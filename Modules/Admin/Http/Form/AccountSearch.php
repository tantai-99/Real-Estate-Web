<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class AccountSearch extends Form
{

	public function init()
	{
		//基本情報枠 #########################

		//担当者名
		$element = new Element\Text('name');
		$element->setLabel('担当者名');
		$this->add($element);

		//ログインID
		$element = new Element\Text('login_id');
		$element->setLabel('ログインID');
		$this->add($element);

		//権限
		$element = new Element\Checkbox('privilege_edit_flg');
		$element->setValueOptions(array("1" => "修正権限"));
		$element->setSeparator('');
		$this->add($element);
		//権限
		$element = new Element\Checkbox('privilege_manage_flg');
		$element->setValueOptions(array("1" => "管理権限"));
		$element->setSeparator('');
		$this->add($element);

		//権限
		$element = new Element\Checkbox('privilege_create_flg');
		$element->setValueOptions(array("1" => "代行作成権限"));
		$element->setSeparator('');
		$this->add($element);

		//権限
		$element = new Element\Checkbox('privilege_open_flg');
		$element->setValueOptions(array("1" => "代行更新権限"));
		$element->setSeparator('');
		$this->add($element);
	}
}
