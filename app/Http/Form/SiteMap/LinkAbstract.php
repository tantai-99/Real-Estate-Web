<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form\Element;

class LinkAbstract extends Page
{

	public function __construct()
	{
		parent::__construct();

		$element = new Element\Checkbox('link_target_blank');
		$this->add($element);
	}
}
