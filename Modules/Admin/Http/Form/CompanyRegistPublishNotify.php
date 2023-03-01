<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class CompanyRegistPublishNotify extends Form
{

	public function init()
	{
		

		// 公開処理通知設定
		$element = new Element\Radio('publish_notify');
		$element->setLabel('公開処理通知設定');
		$element->setRequired(true);
		$element->setValueOptions(array(
			'0' => '公開失敗時のみ通知する',
			'1' => '公開開始・成功・失敗時に通知する(※予約公開は失敗時のみとなります)',
		));
		$this->add($element);
	}
}
