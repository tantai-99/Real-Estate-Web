<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class InformationRegistDetail extends Form
{

	public function init()
	{
		

		//内容
		$element = new Element\Textarea('contents');
		$element->setLabel('内容');
		$element->setRequired(true);
		$element->setAttributes(array('rows' => 5));
		//		$element->addValidator('StringLength', false, array('max' => 1000, 'messages' => '1000文字以内で入力してください'));
		$element->addValidator(new StringLength(array('max' => 1000)));
		$this->add($element);
	}
}
