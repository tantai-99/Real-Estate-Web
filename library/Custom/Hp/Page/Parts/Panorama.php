<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\PanoramaCode;

class Panorama extends PartsAbstract {

	protected $_title = 'VR内見・パノラマ';
	protected $_template = 'panorama';

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'code'			=> 'attr_3',
	);

	public function init() {
		parent::init();

		$element = new Element\Text('code');
		$element->setValidRequired(true);
		$element->addValidator(new PanoramaCode());
		$this->add($element);
	}
}
