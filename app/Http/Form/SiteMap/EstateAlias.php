<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\AliasEstatePageId;

class EstateAlias extends LinkAbstract
{

	public function __construct()
	{
		parent::__construct();

		$element = new Element\Text('link_page_id');
		$element->addValidator(new AliasEstatePageId());
		$this->add($element);
	}
}
