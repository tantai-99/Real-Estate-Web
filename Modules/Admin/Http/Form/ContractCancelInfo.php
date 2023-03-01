<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\DateFormat;
use App\Rules\Regex;
use Library\Custom\Kaiin\Tanto\TantoParams;
use Library\Custom\Kaiin\Tanto\GetTanto;

class ContractCancelInfo extends Form
{

	public function init()
	{

		

		// 利用停止申請日
		$element = new Element\Text('applied_end_date');
		$element->setLabel('利用停止申請日');
		$element->addValidator(new DateFormat(array('messages' => '利用停止申請日が正しくありません。', 'format' => 'Y/m/d')));
		$element->setDescription("※yyyy/mm/dd");
		$element->setAttributes(array("class" => "datepicker"));
		$this->add($element);

		// 利用停止日
		$element = new Element\Text('end_date');
		$element->setLabel('利用停止日');
		$element->addValidator(new DateFormat(array('messages' => '利用停止日が正しくありません。', 'format' => 'Y/m/d')));
		$element->setDescription("※yyyy/mm/dd");
		$element->setAttributes(array("class" => "datepicker"));
		$this->add($element);

		// 解約担当者ID
		$element = new Element\Text('cancel_staff_id');
		$element->setLabel('解約担当者');
		$element->addValidator(new Regex(array('messages' => '半角英数字のみです。', 'pattern' => '/^[0-9]+$/')));
		$this->add($element);

		// 解約担当者名
		$element = new Element\Text('cancel_staff_name');
		$element->setLabel('解約担当者名');
		$element->addValidator(new StringLength(array('max' => 30)));
		$element->setAttributes(array("style" => "width:90%;"));
		$this->add($element);

		// 解約担当者部署
		$element = new Element\Text('cancel_staff_department');
		$element->setLabel('解約担当者部署');
		$element->setAttributes(array("style" => "width:90%;"));
		$this->add($element);
	}


	public function isValid($params, $checkError = true)
	{
		$error_flg = true;
		$error_flg = parent::isValid($params);

		// 解約担当者IDチェック
		if ($params['cancel']['cancel_staff_id'] != "") {
			// 会員APIに接続して担当者情報を取得
			$tantoApiParam	= new TantoParams();
			$tantoApiParam->setTantoCd($params['cancel']['cancel_staff_id']);
			$tantoapiObj	= new GetTanto();
			$tantouInfo		= $tantoapiObj->get($tantoApiParam, '担当者取得');
			if (is_null($tantouInfo) || empty($tantouInfo)) {
				$this->getElement('cancel_staff_id')->setMessages(array("解約担当者番号に誤りがあります。"));
				$error_flg = false;
			}
		}
		return $error_flg;
	}
}
