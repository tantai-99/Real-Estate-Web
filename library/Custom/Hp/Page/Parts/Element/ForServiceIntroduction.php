<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Hp\Page\Parts\Element\ForService;

class ForServiceIntroduction extends SubParts {

	protected $_has_heading = false;

	protected $_columnMap = array(
		'name'			=> 'attr_1',
		'description'	=> 'attr_2',
		'image'			=> 'attr_3',
		'image_title'	=> 'attr_4',
	);

	protected $_required_force = array(
			'name',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('name', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
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
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}

	protected $_presetTypes = array(
			'service'
	);

	protected $_freeTypes = array(
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'service') {
			$element = new ForService();
		}
		return $element;
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image')) {
			$this->getElement('image_title')->setRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		$images = array();

		if ($image = $this->getElement('image')->getValue()) {
			$images[] = $image;
		}

		$subForms = $this->getSubForm('elements')->getSubForms();
		foreach ($subForms as $name => $form) {
			if ($_images = $form->getUsedImages()) {
				$images = array_merge($images, $_images);
			}
		}
		return $images;
	}
}