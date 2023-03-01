<?php

namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules;

class Keyword extends Form
{
	//初期設定画面用のplaceholder文言
	private $_placeholders = array(
		0 => "○○市 不動産",
		1 => "株式会社○○不動産",
	);

	public function __construct()
	{
		parent::__construct();

		$max = 20;
		$names = array();
		for ($i = 0; $i < 6; $i++) {
			$label = 'キーワード' . ($i + 1);
			$names[] = $i;
			$element = new Element\Text('keyword_' . $i);
			$element->setLabel($label);
			$element->setName("$i");
			$element->addValidator(new Rules\Keyword());
			$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
			$element->setAttributes(
				array(
					'class' => array('watch-input-count'),
					'data-maxlength' => $max,
				)
			);

			if ($i == 0) $element->setAttribute('class', 'watch-input-count  first');

			//初期設定のみ使用されているみたいですので、ここに記載します。
			if (isset($this->_placeholders[$i])) {
				$element->setAttribute('placeholder', $this->_placeholders[$i]);
			};

			$this->add($element);
		}
		$this->getElement(0)->addValidator(new Rules\RequiredSomeOne($names, $this));
	}

	/**
	 * 3つずつの配列で取得する
	 */
	public function getElementsByGroup()
	{
		$ret = array();
		$i = 0;
		foreach ($this->getElements() as $name => $element) {
			$ret[floor($i++ / 3)][$name] = $element;
		}

		return $ret;
	}

	public function setDefaults(array $data)
	{
		$name = $this->getName();
		if ($data) {
			if (isset($data[$name]) && !is_array($data[$name])) {
				$data[$name] = explode(',', $data[$name]);
			} else {
				$data[$name] = array();
			}
		}
		return parent::setDefaults($data);
	}

	public function getValues($suppressArrayNotation = false)
	{
		return implode(',', parent::getValue($suppressArrayNotation));
	}
}
