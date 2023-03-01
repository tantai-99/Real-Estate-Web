<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Lists\StructureTypeForSellingcase;
use Library\Custom\Model\Lists\LayoutType;

class SellingcaseDetail extends ElementAbstract {

	protected $_columnMap = array(
			'heading'			=> 'attr_1',
			'comment'			=> 'attr_2',
			'image1'			=> 'attr_3',
			'image1_title'		=> 'attr_4',
			'image2'			=> 'attr_5',
			'image2_title'		=> 'attr_6',
			'structure_type'	=> 'attr_7',
			'adress'			=> 'attr_8',
			'price'				=> 'attr_9',
			'rooms'				=> 'attr_10',
			'layout'			=> 'attr_11',
			'area'				=> 'attr_12',
			'age_of_a_building'	=> 'attr_13',
			'time'				=> 'attr_14',
	);

	protected $_required_force = array(
			'heading',
			'comment',
			'structure_type',
	);


	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('heading', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('comment', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setAttribute('rows', 6);
		$this->add($element);

		$element = new Element\Hidden('image1', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image1_title', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Hidden('image2', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image2_title', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Select('structure_type', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setValueOptions(array(''=>'選択してください') + StructureTypeForSellingcase::getInstance()->getAll());
		$this->add($element);

		$max = 100;
		$element = new Element\Text('adress', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('price', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'w200',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$options = array('' => '選択してください');
		for ($i = 1; $i <= 20; $i++) {
			$options[$i] = $i;
		}
		$element = new Element\Select('rooms', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions($options);
		$element->setAttribute('class', 'w200');
		$this->add($element);

		$element = new Element\Select('layout', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + LayoutType::getInstance()->getAll());
		$element->setAttribute('class', 'w200');
		$this->add($element);

		// 面積
		$max = 100;
		$element = new Element\Text('area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'w200',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('age_of_a_building', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'w200',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		// 時期
		$max = 100;
		$element = new Element\Text('time', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}
	
	public function isReplaceNotEmptyMessage() {
		return true;
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image1')) {
			$this->getElement('image1_title')->setRequired(true);
		}
		if (!isEmptyKey($_data, 'image2')) {
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
		return $images;
	}
}