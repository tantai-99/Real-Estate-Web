<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class ForExample extends ElementAbstract {

	protected $_columnMap = array(
			'title'			=> 'attr_1',
			'description'	=> 'attr_3',
			'image'			=> 'attr_4',
			'image_title'	=> 'attr_5',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('title', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
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