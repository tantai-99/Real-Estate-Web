<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Lists\HpPagePartsHeadingType;

class ColumnDetail extends ElementAbstract {

	protected $_has_heading = true;

	protected $_columnMap = array(
		'heading_type'	=> 'attr_1',
		'heading'		=> 'attr_2',
		'description'	=> 'attr_3',
		'image'			=> 'attr_4',
		'image_title'	=> 'attr_5',
	);
   	protected $_required_force = array(
  			'heading',
  	);

	public function init() {
		parent::init();

		$element = new Element\Select('heading_type', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$potion = HpPagePartsHeadingType::getInstance()->getAll();
		unset($potion[1]);
		$element->setValueOptions($potion);
		// $element->setAttribute('style', 'width:80%');
		$this->add($element);

		$max = 100;
		$element = new Element\Text('heading', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$element->setRequired(true);
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

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image')) {
			$this->getElement('image_title')->setValidRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		$images = array();
		if ($id = $this->getElement('image')->getValue()) {
			$images[] = $id;
		}
		return $images;
	}
}