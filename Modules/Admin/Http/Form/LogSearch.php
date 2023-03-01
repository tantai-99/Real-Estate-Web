<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\LogType;
use DateTime;
use DateTimeZone;
use App\Rules\DateFormat;

class LogSearch extends Form 
{
	
	public function init() 
	{
		// 操作日時の日付設定
		$dt = new DateTime();
		$dt->setTimeZone(new DateTimeZone('Asia/Tokyo'));
		$currentDate = $dt->format('Y-m-d');

		//基本情報枠 #########################

		//契約タイプ
		$element = new Element\Select('log_type');
		$obj = new LogType();
		$list = $obj->getAll();
		$element->setValueOptions($list);
		$element->setAttributes(array("style"=>"width:60%"));
		$this->add($element);

		//担当者ＩＤ
		$element = new Element\Text('athome_staff_id');
		$element->setLabel('担当者ＩＤ');
		$element->setAttributes(array("style"=>"width:80%"));
		$this->add($element);

		//会員No
		$element = new Element\Text('member_no');
		$element->setLabel('会員No');
		$element->setAttributes(array("style"=>"width:80%"));
		$this->add($element);

		//会社名
		$element = new Element\Text('company_name');
		$element->setLabel('会社名');
		$element->setAttributes(array("style"=> "width:80%"));
		$this->add($element);

		//操作日時
		$element = new Element\Text('datetime_s');
		$element->setLabel('操作日時');
		$element->setDescription("※yyyy-mm-dd hh:ii");
		$element->setAttributes([
            "style"=>"width:45%",
            "class"=> "datepicker",
        ]);
		$element->addValidator(new DateFormat(array('messages' => '操作日時（開始）が正しくありません。', 'format' => 'Y-m-d H:i')));
		$element->setValue($currentDate . ' 00:00');
		$this->add($element);

		//操作日時
		$element = new Element\Text('datetime_e');
		$element->setLabel('操作日時');
		$element->setAttributes([
            "style"=>"width:45%",
            "class"=> "datepicker",
        ]);
		$element->addValidator(new DateFormat(array('messages' => '操作日時（終了）が正しくありません。', 'format' => 'Y-m-d H:i')));
		$element->setValue($currentDate . ' 23:59');
		$this->add($element);

	}
}