<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Image extends ElementAbstract {

	protected $_columnMap = array(
			'image'			=> 'attr_1',
			'image_title'	=> 'attr_2',
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$this->add($element);

	}

	public function useImageTitle($max = 30) {
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);
	}

	public function getUsedImages() {
		return array($this->getElement('image')->getValue());
	}
}