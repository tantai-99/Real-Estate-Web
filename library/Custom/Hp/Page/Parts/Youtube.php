<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Youtube extends PartsAbstract {

	protected $_title = 'YouTube';
	protected $_template = 'youtube';

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'code'			=> 'attr_3',
	);

	public function init() {
		parent::init();

		$element = new Element\Text('code');
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => 2000]));
		$this->add($element);
	}
}