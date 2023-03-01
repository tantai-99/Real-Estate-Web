<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\EmailAddress;

class CompanyGoogleAnalyticsTag extends Form
{

	public function init()
	{
		

		//id
		$element = new Element\Hidden('id');
		$this->add($element);

		//companyid
		$element = new Element\Hidden('company_id');
		$element->setRequired(true);
		$this->add($element);
		/*
		//ApiKey
		$element = new Element\Text('google_api_key');
		$element->setLabel('Google API Key');
		$this->add($element);
*/
		//ユーザーＩＤ
		$element = new Element\Text('google_user_id');
		$element->setLabel('ユーザーID');
		$element->addValidator(new EmailAddress());
		//		$element->addValidator('StringLength', false, array('max' => 255, 'messages' => '255文字以内で入力してください'));
		$element->addValidator(new StringLength(array('max' => 255)));
		$element->setRequired(true);
		$this->add($element);

		//パスワード
		$element = new Element\Text('google_password');
		$element->setLabel('パスワード');
		$element->setRequired(true);
		//		$element->addValidator('StringLength', false, array('max' => 100, 'messages' => '100文字以内で入力してください'));
		$element->addValidator(new StringLength(array('max' => 100)));
		$this->add($element);

		//アナリティクスAPI認証用メールアドレス
		$element = new Element\Text('google_analytics_mail');
		$element->setLabel('アナリティクスAPI認証用メールアドレス');
		$element->addValidator(new EmailAddress());
		$element->addValidator(new StringLength(array('max' => 255)));
		$element->setRequired(true);
		$this->add($element);

		//P12ファイル
		//		$element = new Zend_Form_Element_File('google_p12');
		//		$element->setLabel('証明書ファイル（p12）');
		//		$this->add($element);


		//P12ファイル用ファイル名
		$element = new Element\Hidden('file_name');
		$element->setLabel('証明書ファイル（p12）');
		$element->setRequired(true);
		$this->add($element);

		//ViewID
		$element = new Element\Text('google_analytics_view_id');
		$element->setLabel('ビューＩＤ');
		// $element->addValidator('Digits', false, array('messages' => '半角数値で入力してください'));
		//ViewIDは結局桁数がわからないため、桁数制限削除
		//		$element->addValidator('StringLength', false, array('min' => 8, 'max' => 8, 'messages' => '8文字で入力してください'));
		//		$vali = new StringLength(array('min' => 8, 'max' => 8));
		//		$vali->setMessages(array(StringLength::INVALID => "8文字で入力してください",StringLength::TOO_SHORT => "8文字で入力してください",StringLength::TOO_LONG => "8文字で入力してください"));
		//		$element->addValidator($vali);
		$element->setRequired(true);
		$this->add($element);

		//アナリティクスコード
		$element = new Element\Textarea('google_analytics_code');
		$element->setLabel('アナリティクスコード');
		$element->setRequired(true);
		$element->setAttributes(array('rows' => 5));
		$element->addValidator(new StringLength(array('max' => 1000)));
		$this->add($element);
	}

}
