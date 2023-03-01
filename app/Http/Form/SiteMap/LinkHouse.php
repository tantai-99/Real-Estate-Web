<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form\Element;
use App\Rules;

class LinkHouse extends LinkAbstract
{

	public function __construct()
	{
		parent::__construct();

		$element = new Element\Hidden('link_house');
		$this->add($element);

		$max = 20;
		$element = new Element\Text('title_house');
		$element->setLabel('リンク名');
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);
	}

	public function isValid($data, $checkErrors = true)
	{
		$isValid = parent::isValid($data);
		if ($data['link_house'] == '') {
			$this->getElement('link_house')->setMessages('物件を選択してください。');
			$isValid = false;
		}
		if ($data['title_house'] == '') {
			$this->getElement('title_house')->setMessages('リンク名を入力してください。');
			$isValid = false;
		}
		return $isValid;
	}

	public function getMessages()
	{
		$messages = array();

		foreach ($this->getElements() as $name => $element) {
			if (!$element->hasErrors()) {
				continue;
			}

			$messages[$name] = $this->getGroupErrors(array($name));
		}

		return $messages;
	}

	public function getGroupErrors($elements = array())
	{
		if (!$elements) {
			$elements = $this->getElements();
		}

		$messages = array();
		foreach ($elements as $name => $element) {
			if (!($element instanceof Element)) {
				$name = $element;
				$element = $this->getElement($name);
			}
			foreach ($element->getMessages() as $key => $message) {
				$messages[$key] = $message;
			}
		}
		return $messages;
	}
}
