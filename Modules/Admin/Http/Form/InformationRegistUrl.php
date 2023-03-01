<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Url;

class InformationRegistUrl extends Form
{
	public function init()
	{
		

		//指定URL
		$element = new Element\Text('url');
		$element->setLabel('指定URL');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 2000)));
		$element->addValidator(new Url());
		$this->add($element);
	}

	public function isValid($data, $checkErrors = true)
	{
		$isValid = true;
		$isValid = parent::isValid($data);
		if ($data['basic']['display_type_code'] == 1) {
			if (empty($data['designation']['url'])) {
				$this->getElement('url')->setMessages(["URLを入力してください。"]);
				$isValid = false;
			}
		}
		return $isValid;
	}
}
