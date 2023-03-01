<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Greeting extends PartsAbstract {

	protected $_title = '代表挨拶';
	protected $_template = 'greeting';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_columnMap = array(
			'text'		=> 'attr_1',
			'title'		=> 'attr_2',
			'signature'	=> 'attr_3',
			'image'		=> 'attr_4',
			'image_title'	=> 'attr_5',
	);

	protected $_required_force = array(
			'text',
			'title',
			'signature',
	);


	public function init() {
		parent::init();

		$element = new Element\Wysiwyg('text', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('title', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('signature', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}

	public function isValid($data, $checkError = true) {
		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (isset($_data['image']) && $_data['image']) {
			$this->getElement('image_title')->setRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		if ($image = $this->getElement('image')->getValue()) {
			return array($image);
		}
		return array();
	}
}