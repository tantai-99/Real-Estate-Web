<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\Regex;
use App\Rules\StringLength;
use App\Rules\DateFormat;
use Library\Custom\Kaiin\Tanto\TantoParams;
use Library\Custom\Kaiin\Tanto\GetTanto;

class CompanySecondEstate extends Form {
    

    public function init() {

        //基本情報枠 #########################
        //SeondEstateId
        $element = new Element\Hidden('id');
        $this->add($element);


        //利用開始申請日
        $element = new Element\Text('applied_start_date');
        $element->setLabel('利用開始申請日');
        $element->setRequired(true);
        $element->addValidator(new DateFormat(array('messages' => '利用開始申請日が正しくありません。', 'format' => 'Y-m-d')));
        $element->setDescription("※yyyy-mm-dd");
        $element->setAttribute("class", "datepicker");
        $this->add($element);

        //利用開始日
        $element = new Element\Text('start_date');
        $element->setLabel('利用開始日');
        $element->setRequired(true);
        $element->addValidator(new DateFormat(array('messages' => '利用開始日が正しくありません。', 'format' => 'Y-m-d')));
        $element->setDescription("※yyyy-mm-dd");
        $element->setAttribute("class", "datepicker");
        $this->add($element);

        //契約担当者ID
        $element = new Element\Text('contract_staff_id');
        $element->setRequired(true);
        $element->setLabel('契約担当者');
        $element->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'messages' => '半角数字のみです。')));
        $this->add($element);

        //契約担当者名
        $element = new Element\Text('contract_staff_name');
        $element->setRequired(true);
        $element->setLabel('契約担当者名');
        $element->setAttribute("style", "width:20%;");
        $element->addValidator(new StringLength(array('max' => 30)));
        $this->add($element);

        //契約担当者部署
        $element = new Element\Text('contract_staff_department');
        $element->setRequired(true);
        $element->setLabel('契約担当者部署');
        $element->setAttribute("style", "width:90%;");
        $this->add($element);

        //利用停止申請日
        $element = new Element\Text('applied_end_date');
        $element->setLabel('利用停止申請日');
        $element->addValidator(new DateFormat(array('messages' => '利用停止申請日が正しくありません。', 'format' => 'Y-m-d')));
        $element->setDescription("※yyyy-mm-dd");
        $element->setAttribute("class", "datepicker");
        $this->add($element);

        //利用停止日
        $element = new Element\Text('end_date');
        $element->setLabel('利用停止日');
        $element->addValidator(new DateFormat(array('messages' => '利用停止日が正しくありません。', 'format' => 'Y-m-d')));
        $element->setDescription("※yyyy-mm-dd");
        $this->add($element);
        $element->setAttribute("class", "datepicker");

        //解約担当者ID
        $element = new Element\Text('cancel_staff_id');
        $element->setLabel('解約担当者');
        $element->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'messages' => '半角数字のみです。')));
        $this->add($element);

        //解約担当者名
        $element = new Element\Text('cancel_staff_name');
        $element->setLabel('解約担当者名');
        $element->addValidator(new StringLength(array('max' => 30)));
        $element->setAttribute("style", "width:20%;");
        $this->add($element);

        //解約担当者部署
        $element = new Element\Text('cancel_staff_department');
        $element->setLabel('解約担当者部署');
        $element->setAttribute("style", "width:90%;");
        $this->add($element);

    }


    public function isValid($params, $checkError = true) {

    	$error_flg = true;
        $error_flg = parent::isValid($params);

        //契約担当者IDチェック
		$param = 'secondEstate';
		if (getRequestInfo()['action'] == 'original-setting' || getRequestInfo()['action'] == 'originalSetting') {
			$param = 'originalSetting';
		}

    	//契約担当者IDチェック
    	if($this->getElement('contract_staff_id')->getValue() != "") {

            //会員APIに接続して担当者情報を取得
            $tantoApiParam = new TantoParams();
            $tantoApiParam->setTantoCd($params[$param]['contract_staff_id']);
            $tantoapiObj = new GetTanto();
            $tantouInfo = $tantoapiObj->get($tantoApiParam, '担当者取得');
            if (is_null($tantouInfo) || empty($tantouInfo)) {
                $this->getElement('contract_staff_id')->setMessages( array("契約担当者番号に誤りがあります。") );
                $error_flg = false;
            }
    	}

    	//解約担当者IDチェック
    	if($params[$param]['cancel_staff_id'] != "") {

            //会員APIに接続して担当者情報を取得
            $tantoApiParam = new TantoParams();
            $tantoApiParam->setTantoCd($params[$param]['cancel_staff_id']);
            $tantoapiObj = new GetTanto();
            $tantouInfo = $tantoapiObj->get($tantoApiParam, '担当者取得');
            if (is_null($tantouInfo) || empty($tantouInfo)) {
                $this->getElement('cancel_staff_id')->setMessages( array("解約担当者番号に誤りがあります。") );
                $error_flg = false;
            }
        }
    	return $error_flg;
    }
}
