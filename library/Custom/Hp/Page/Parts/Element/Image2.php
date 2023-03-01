<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Image2 extends ElementAbstract {

	protected $_columnMap = array(
			'image1'		=> 'attr_1',
			'image1_title'	=> 'attr_2',
			'image2'		=> 'attr_3',
			'image2_title'	=> 'attr_4',
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image1', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$this->add($element);

		$element = new Element\Hidden('image2', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

	}

	public function useImageTitle($max = 30) {
		$element = new Element\Text('image1_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->class = array('watch-input-count');
		$element->maxlength = $max;
		$this->add($element);

		$element = new Element\Text('image2_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->class = array('watch-input-count');
		$element->maxlength = $max;
		$this->add($element);
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image1') && $this->getElement('image1_title')) {
			$this->getElement('image1_title')->setRequired(true);
		}
		if (!isEmptyKey($_data, 'image2') && $this->getElement('image2_title')) {
			$this->getElement('image2_title')->setRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		$images = array();
		if ($id = $this->getElement('image1')->getValue()) {
			$images[] = $id;
		}
		if ($id = $this->getElement('image2')->getValue()) {
			$images[] = $id;
		}
		$images;
	}
}