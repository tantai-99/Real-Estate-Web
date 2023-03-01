<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Domain;
use Library\Custom\Kaiin\Kaiin\KaiinParams;
use Library\Custom\Kaiin\Kaiin\Kaiin;

use Library\Custom\Model\Lists\CompanyAgreementType;


class BasicInfo extends Form
{

	public function init()
	{

		$disabled	= array();
		if (array_key_exists('copy', $_REQUEST) && ($_REQUEST['copy'] == 'true')) {
			$disabled	= array("disabled"		=> "disabled");
		}

		//companyid
		$element = new Element\Hidden('id');
		$this->add($element);

		// 契約タイプ
		$element = new Element\Radio('contract_type', $disabled);
		$element->setLabel('契約');
		$element->setRequired(	true			) ;
		$obj = new CompanyAgreementType();
		$element->setValueOptions($obj->getAll());
		$this->add($element);

		// コピー元デモ会員No
		if (array_key_exists('copy', $_REQUEST) && ($_REQUEST['copy'] == 'true')) {
			$element = new Element\Text('copy_from_member_no', $disabled);
			$element->setLabel('コピー元会員No');
			$this->add($element);
		}

		// 会員No
		$element = new Element\Text('member_no');
		$element->setLabel('会員No');
		$this->add($element);

		// 会員名
		$element = new Element\Text('member_name');
		$element->setLabel('会員名');
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setAttributes(array(" style" => "width:50%;"));
		$this->add($element);

		//所在地
		$element = new Element\Hidden('location');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 255)));
		$this->add($element);

		//インターネットコード
		$element = new Element\Text('member_linkno');
		$element->setLabel('インターネットコード');
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setAttributes(array(" style" => "width:50%;"));
		$this->add($element);

		// 会社名
		$element = new Element\Text('company_name');
		$element->setLabel('会社名');
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setRequired(true);
		$this->add($element);

		// 利用ドメイン
		$element = new Element\Text('domain');
		$element->setLabel('利用ドメイン');
		$element->addValidator(new Domain());
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setRequired(true);
		$this->add($element);
	}

	public function isValid($params, $checkError = true)
	{
		if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) {
			$this->getElement('company_name')->setRequired(false);
			$this->getElement('domain')->setRequired(false);
		}
		$error_flg	= true;
		$error_flg	= parent::isValid($params, $checkError);

		// 会員Noチェック（デモの場合はチェックをかけない）
		if ($params['basic']['contract_type'] != config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')) {
			if ($params['basic']['member_no'] == "") {
				$this->getElement('member_no')->setMessages(array("会員Noが設定されていません。"));
				$error_flg = false;
			} else {
				// 会員APIに接続して会員情報を取得
				$apiParam	= new KaiinParams();
				$apiParam->setKaiinNo($params['basic']['member_no']);
				$apiObj		= new Kaiin();
				$kaiinData	= $apiObj->get($apiParam, '会員基本取得');
				if (is_null($kaiinData) || empty($kaiinData)) {
					$this->getElement('member_no')->setMessages(array("会員Noに誤りがあります。"));
					$error_flg = false;
				}
				$kaiinData = (object)$kaiinData;

				if (!property_exists($kaiinData, 'kaiinLinkNo') || empty($kaiinData->kaiinLinkNo)) {
					$this->getElement('member_linkno')->setMessages(array("インターネットコードが設定されていません。"));
					$error_flg = false;
				}
			}
		}
		return $error_flg;
	}
}
