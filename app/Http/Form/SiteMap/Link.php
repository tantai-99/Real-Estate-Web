<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form\Element;
use App\Rules;

class Link extends LinkAbstract
{

	public function __construct()
	{
		parent::__construct();

		$element = new Element\Text('link_url');
		$element->setLabel('URL');
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => 2000)));
		$element->addValidator(new Rules\Url());
		$this->add($element);

		$max = 20;
		$element = new Element\Text('title');
		$element->setLabel('リンク名');
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);
	}

	public function isValid($data, $checkErrors = true)
	{
		$isValid = parent::isValid($data);
		if (empty($data['link_url'])) {
			$this->getElement('link_url')->setMessages(["URLを入力してください。"]);
			$isValid = false;
		}
		if (empty($data['title'])) {
			$this->getElement('title')->setMessages(["リンク名を入力してください。"]);
			$isValid = false;
		}
		return $isValid;
	}
}
