<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form\Element;
use App\Rules\AliasHpPageId;

class Alias extends LinkAbstract
{

	public function __construct()
	{
		parent::__construct();

		$validator = $this->getElement('parent_page_id')->getValidator('ParentHpPageId');
		$aliasValidator = new AliasHpPageId($validator->getTable(), $validator->getHpId());

		$element = new Element\Text('link_page_id');
		$element->setLabel('既存ページ');
		$element->addValidator($aliasValidator);
		$this->add($element);
	}

	public function isValid($data, $checkErrors = true)
	{
		$isValid = parent::isValid($data);

		if (array_key_exists('link_page_id', $data) && $data['link_page_id'] == null) {
			$this->getElement('link_page_id')->setMessages('ページを選択してください。');
			$isValid = false;
		}

		return $isValid;
	}
}
