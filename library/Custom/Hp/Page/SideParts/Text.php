<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Text extends SidePartsAbstract {

	protected $_title = 'テキスト';
	protected $_template = 'text';

	protected $_columnMap = array(
			'heading'		=> 'attr_1',
			'value'			=> 'attr_2',
	);

	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('value', array('disableLoadDefaultDecorators'=>true));
		// $element->setRequired(true);
		// $element->setValue('side-text');
		$element->setValidRequired(true);

		$this->add($element);
	}
}