<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\DateFormat;
use Library\Custom\Model\Lists\InformationDisplayPageCode;
use Library\Custom\Model\Lists\InformationDisplayTypeCode;

class InformationRegist extends Form
{
	public function init()
	{
		

		//companyid
		$element = new Element\Hidden('id');
		$this->add($element);

		//担当者名
		$element = new Element\Text('title');
		$element->setLabel('タイトル');
		$element->setRequired(true);
		//		$element->addValidator('StringLength', false, array('max' => 100, 'messages' => '100文字以内で入力してください'));
		$element->addValidator(new StringLength(array('max' => 100)));
		$this->add($element);

		//公開開始日
		$element = new Element\Text('start_date');
		$element->setLabel('公開開始日');
		$element->setRequired(true);
		$element->addValidator(new DateFormat(array('messages' => '公開開始日が正しくありません。', 'format' => 'Y-m-d')));
		$element->setAttributes([
			'style' => 'width:90%',
			'class' => 'datepicker'
		]);
		$element->setDescription("※yyyy-mm-dd");
		$element->setValue(date("Y-m-d"));
		$this->add($element);

		//公開終了日
		$element = new Element\Text('end_date');
		$element->setLabel('公開終了日');
		$element->addValidator(new DateFormat(array('messages' => '公開終了日が正しくありません。', 'format' => 'Y-m-d')));
		$element->setDescription("※yyyy-mm-dd");
		$element->setAttributes([
			'style' => 'width:90%',
			'class' => 'datepicker'
		]);
		$this->add($element);

		//公開設定
		$element = new Element\Select('display_page_code');
		$element->setLabel('公開設定');
		$element->setRequired(true);
		$list = new InformationDisplayPageCode();
		$element->setValueOptions($list->getAll());
		$this->add($element);

		//重要表示
		$element = new Element\Checkbox('important_flg');
		$element->setLabel('重要表示');
		$element->setRequired(false);
		$element->setDescription("※CMSのホーム画面にお知らせを表示させたい場合は、公開設定を「ログイン後表示」または「全てに表示」にして重要度をチェックしてください。");
		$element->setValueOptions(array("1" => "あり"));
		$element->setSeparator('');
		$this->add($element);

		/* なくなりました
		//new表示
		$element = new Zend_Form_Element_MultiCheckbox('new_flg');
		$element->setLabel('new');
		$element->setMultiOptions(array("1" => "あり"));
		$this->add($element);
*/
		//表示方法
		$element = new Element\Radio('display_type_code');
		$element->setLabel('表示方法');
		$element->setRequired(true);
		$list = new InformationDisplayTypeCode();
		$element->setValueOptions($list->getAll());
		$this->add($element);
	}


	public function isValid($params, $checkError = true)
	{
		$error_flg = true;
		$error_flg = parent::isValid($params);

		//開始終了チェック
		if ($params['basic']['start_date'] != "" && $params['basic']['end_date'] != "") {
			$start_date = str_replace("-", "", $params['basic']['start_date']);
			$end_date   = str_replace("-", "", $params['basic']['end_date']);
			if ($start_date > $end_date) {
				$this->getElement('end_date')->setMessages(array("公開終了日は、公開開始日より未来日に設定してください。"));
				$error_flg = false;
				if ($checkError) {
					$this->checkErrors();
				}
			}
		}
		return $error_flg;
	}
}
