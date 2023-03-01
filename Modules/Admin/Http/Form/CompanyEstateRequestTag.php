<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;


class CompanyEstateRequestTag extends Form
{

	public function init()
	{
		
		//id
		$element = new Element\Hidden('id');
		$this->add($element);

		//companyid
		$element = new Element\Hidden('company_id');
		$element->setRequired(true);
		$this->add($element);
		$this->mainTag('residential_rental_request', 'thanks');
		$this->mainTag('business_rental_request', 'thanks');
		$this->mainTag('residential_sale_request', 'thanks');
		$this->mainTag('business_sale_request', 'thanks');
		$this->mainTag('residential_rental_request', 'input');
		$this->mainTag('business_rental_request', 'input');
		$this->mainTag('residential_sale_request', 'input');
		$this->mainTag('business_sale_request', 'input');
	}

	//その他タグ #########################
	private function mainTag($key1 = null, $key2 = null)
	{
		$name = empty($key1) ? 'above_close_head_tag' : 'above_close_head_tag_' . $key1 . '_' . $key2;
		$element = new Element\Textarea($name);
		$element->setLabel('&lt;/head&gt;直上タグ情報');
		$element->setAttributes(array('rows' => 5));
		$element->addValidator(new StringLength(array('max' => 5000)));
		$this->add($element);

		$name = empty($key1) ? 'under_body_tag' : 'under_body_tag_' . $key1 . '_' . $key2;
		$element = new Element\Textarea($name);
		$element->setLabel('&lt;body&gt;直下タグ情報');
		$element->setAttributes(array('rows' => 5));
		$element->addValidator(new StringLength(array('max' => 5000)));
		$this->add($element);

		$name = empty($key1) ? 'above_close_body_tag' : 'above_close_body_tag_' . $key1 . '_' . $key2;
		$element = new Element\Textarea($name);
		$element->setLabel('&lt;/body&gt;直上タグ情報');
		$element->setAttributes(array('rows' => 5));
		$element->addValidator(new StringLength(array('max' => 5000)));
		$this->add($element);
	}
}
