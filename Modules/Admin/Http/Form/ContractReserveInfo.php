<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\CmsPlan;
use Library\Custom\Kaiin\Tanto\TantoParams;
use Library\Custom\Kaiin\Tanto\GetTanto;
use App\Rules\StringLength;
use App\Rules\DateFormat;
use App\Rules\Regex;
use App\Rules\Plan;



class ContractReserveInfo extends Form
{

	protected	$_row;

	/**
	 * コンストラクタ
	 * 
	 * @param	App\Http\Model\Company&		$row
	 */
	public function __construct(&$row = null)
	{
		$this->_row		= &$row;
		

		$copyPlan	= config('constants.CmsPlan.CMS_PLAN_NONE');
		if (array_key_exists('copy', $_REQUEST) && ($_REQUEST['copy'] == 'true')) {
			$copyPlan	= $this->_row->cms_plan;
		}

		// プラン
		$element	= new Element\Select('reserve_cms_plan');
		$element->setLabel('プラン');
		$obj		= new CmsPlan();
		$element->setValueOptions($obj->getAll());
		$element->setValue($copyPlan);
		$this->add($element);

		// 利用開始申請日
		$element	= new Element\Text('reserve_applied_start_date');
		$element->setLabel('利用開始申請日');
		$element->addValidator(new DateFormat(array('messages' => '利用開始申請日が正しくありません。', 'format' => 'Y/m/d')));
		$element->setDescription("※yyyy/mm/dd");
		$element->setAttributes(array("class" => "datepicker"));
		$this->add($element);

		// 利用開始日
		$element	= new Element\Text('reserve_start_date');
		$element->setLabel('利用開始日');
		$element->addValidator(new DateFormat(array('messages' => '利用開始日が正しくありません。', 'format' => 'Y/m/d')));
		$element->setDescription("※yyyy/mm/dd");
		$element->setAttributes(array("class" => "datepicker"));
		$this->add($element);

		// 契約担当者ID
		$element = new Element\Text('reserve_contract_staff_id');
		$element->setLabel('契約担当者');
		$element->addValidator(new Regex(array('messages' => '半角英数字のみです。', 'pattern' => '/^[0-9]+$/')));
		$this->add($element);

		// 契約担当者名
		$element = new Element\Text('reserve_contract_staff_name');
		$element->setLabel('契約担当者名');
		$element->setAttributes(array("style" => "width:90%;"));
		$element->addValidator(new StringLength(array('max' => 30)));
		$this->add($element);

		// 契約担当者部署
		$element = new Element\Text('reserve_contract_staff_department');
		$element->setLabel('契約担当者部署');
		$element->setAttributes(array("style" => "width:90%;"));
		$this->add($element);
	}

	public function isValid($params, $checkError = true)
	{
		$this->getElement('reserve_cms_plan')->addValidator(new Plan($params));

		$error_flg	= parent::isValid($params);

		if ($params['reserve']['reserve_cms_plan'] != config('constants.cms_plan.CMS_PLAN_NONE')) {	// ATHOME_HP_DEV-2608 【契約登録、プラン変更、オプション追加】契約情報予約のプランがプルダウンで表示されている
			if ($params['reserve']['reserve_applied_start_date'] == "") {
				$this->getElement('reserve_applied_start_date')->setMessages(array("利用開始申請日が入力されていません。"));
				$error_flg = false;
			}
			if ($params['reserve']['reserve_start_date'] == "") {
				$this->getElement('reserve_start_date')->setMessages(array("利用開始日が入力されていません。"));
				$error_flg = false;
			}
			if ($params['reserve']['reserve_contract_staff_id'] == "") {
				$this->getElement('reserve_contract_staff_id')->setMessages(array("契約担当者番号が入力されていません。"));
				$error_flg = false;
			}
		}
		// 契約担当者IDチェック
		if ($params['reserve']['reserve_contract_staff_id'] != "") {
			// 会員APIに接続して担当者情報を取得
			$tantoApiParam	= new TantoParams();
			$tantoApiParam->setTantoCd($params['reserve']['reserve_contract_staff_id']);
			$tantoapiObj	= new GetTanto();
			$tantouInfo		= $tantoapiObj->get($tantoApiParam, '担当者取得');
			if (is_null($tantouInfo) || empty($tantouInfo)) {
				$this->getElement('reserve_contract_staff_id')->setMessages(array("契約担当者番号に誤りがあります。"));
				$error_flg = false;
			}
		}

		return $error_flg;
	}
}
