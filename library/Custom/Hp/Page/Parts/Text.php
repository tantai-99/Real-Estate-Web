<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\Wysiwyg;

class Text extends PartsAbstract {

	protected $_title = 'テキスト';
	protected $_template = 'text';

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'value'			=> 'attr_3',
	);

	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('value');
		//, array('disableLoadDefaultDecorators'=>true)
		$element->setValidRequired(true);
		// $element->setValue('part-text');
		$this->add($element);
	}
}