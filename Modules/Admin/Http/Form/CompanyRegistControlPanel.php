<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\Url;
use App\Rules\StringLength;
use App\Rules\Regex;

class CompanyRegistControlPanel extends Form
{

	public function init()
	{
		

		//コンパネ情報枠 #########################
		//コンパネ_アドレス
		$element = new Element\Text('cp_url');
		$element->setLabel('コンパネアドレス');
		$element->addValidator(new Url());
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setRequired(true);
		$this->add($element);

		//コンパネ_ユーザーID
		$element = new Element\Text('cp_user_id');
		$element->setLabel('コンパネ_ユーザーID');
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setRequired(true);
		$this->add($element);

		//コンパネ_パスワード
		$element = new Element\Text('cp_password');
		$element->setLabel('コンパネ_パスワード');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$element->setRequired(true);
		$this->add($element);

		//コンパネ_パスワード_非活性
		$element = new Element\Checkbox('cp_password_used_flg');
		$element->setLabel('コンパネパスワード非活性（切替対応）');
		$this->add($element);
	}

	public function isValid($params, $checkError = true)
	{
		if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) {
			$this->getElement('cp_user_id')->setRequired(false);
			$this->getElement('cp_password')->setRequired(false);
		}
		return parent::isValid($params, $checkError);
	}
}
