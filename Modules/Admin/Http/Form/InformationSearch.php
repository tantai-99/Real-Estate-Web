<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\InformationDisplayPageCode;
use App\Rules\DateFormat;


class InformationSearch extends Form
{
	public function init()
	{
		
		//基本情報枠 #########################

		//担当者名
		$element = new Element\Text('title');
		$element->setLabel('タイトル');
		$this->add($element);

		//公開設定
		$element = new Element\Select('display_page_code');
		$element->setLabel('公開設定');
		$listObj = new InformationDisplayPageCode();
		$list = array();
		$list[""] = "";
		foreach ($listObj->getAll() as $key => $val) {
			$list[$key] = $val;
		}
		$element->setValueOptions($list);
		$this->add($element);

		//開始日
		$element = new Element\Text('start_date');
		$element->setLabel('開始日');
		$element->setDescription("※yyyy-mm-dd");
		$element->setAttributes([
			'style' => 'width:45%',
			'class' => 'datepicker'
		]);
		$element->addValidator(new DateFormat(array('messages' => '公開日（開始）が正しくありません。', 'format' => 'Y-m-d')));
		$this->add($element);

		//終了日
		$element = new Element\Text('end_date');
		$element->setLabel('終了日');
		$element->setDescription("※yyyy-mm-dd");
		$element->setAttributes([
			'style' => 'width:45%',
			'class' => 'datepicker'
		]);
		$element->addValidator(new DateFormat(array('messages' => '公開日（終了）が正しくありません。', 'format' => 'Y-m-d')));
		$this->add($element);
	}
}
