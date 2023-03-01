<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;

class BusinessContent extends SubParts {
	protected $_columnMap = array(
			'heading' => 'attr_1',
			'row_setting' => 'attr_2',
	);

	protected $_presetTypes = array(
			'business_content_item'
	);

	public function init() {
		parent::init();

		$max = 50;
		$element = new Element\Text('heading', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Radio('row_setting', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(1 => "1列",2 => "2列",3 => "3列"));
		$element->setRequired(true);
		$element->setValue(1);
		$element->setSeparator(" ");
		$this->add($element);

	}

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'business_content_item') {
			$element = new BusinessContentItem();
		}
		return $element;
	}
}