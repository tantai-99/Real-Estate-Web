<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Date;
use Library\Custom\Model\Lists\StructureTypeForCustomervoice;

class CustomervoiceDetail extends PartsAbstract {

	protected $_title = 'お客様の声';
	protected $_template = 'customervoice-detail';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_columnMap = array(
			'title'			=> 'attr_1',
			'area'			=> 'attr_2',
			'structure_type'=> 'attr_3',
			'date'			=> 'attr_4',
			'image'			=> 'attr_5',
			'image_title'	=> 'attr_6',
			'customer_name'	=> 'attr_7',
			'customer_age'	=> 'attr_8',
			'customer_comment'	=> 'attr_9',
			'staff_name'	=> 'attr_10',
			'staff_comment'	=> 'attr_11'
	);

	public function init() {
		parent::init();

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
		$element = new Element\Text('area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Select('structure_type', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setValueOptions(array(''=>'選択してください') + StructureTypeForCustomervoice::getInstance()->getAll());
		$this->add($element);

		$max = 30;
		$element = new Element\Text('date', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Date());
		$element->setAttributes([
			'class' => 'datepicker w200',
			'data-maxlength' => $max,
		]);
		$element->class = array('datepicker w200');
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

		$max = 100;
		$element = new Element\Text('customer_name', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('customer_age', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'w80',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('customer_comment', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setAttribute('rows', 6);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('staff_name', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('staff_comment', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
		$this->add($element);
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
		return $images;
	}
}