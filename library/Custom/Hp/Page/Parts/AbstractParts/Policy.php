<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\PartsAbstract;

class Policy extends PartsAbstract {

	protected $_template = 'policy';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_sample_filename;

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'value'			=> 'attr_3',
	);

	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('value', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setAttribute('rows', 25);
		$this->add($element);
	}

	public function getSample() {
		return file_get_contents(storage_path('data/samples/' . $this->_sample_filename));
	}
}