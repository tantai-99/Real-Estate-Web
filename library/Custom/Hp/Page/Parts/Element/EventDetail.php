<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\RequiredSomeOne;
use App\Rules\DateSpan;
use App\Rules\Date;
use App\Rules\Url;
use Library\Custom\Model\Lists\StructureTypeForEvent;

class EventDetail extends ElementAbstract {

	protected $_columnMap = array(
			'heading'			=> 'attr_1',
			'comment'			=> 'attr_2',
			'image1'			=> 'attr_3',
			'image1_title'		=> 'attr_4',
			'image2'			=> 'attr_5',
			'image2_title'		=> 'attr_6',
			'structure_type'	=> 'attr_7',
			'start'				=> 'attr_8',
			'end'				=> 'attr_9',
			'adress'			=> 'attr_10',
			'price'				=> 'attr_11',
			'url'				=> 'attr_12',
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
		$element->setValueOptions(array(''=>'選択してください') + StructureTypeForEvent::getInstance()->getAll());
		$this->add($element);

		$max = 30;
		$element = new Element\Text('start', array('disableLoadDefaultDecorators'=>true));
		// $element->setAllowEmpty(false);
		$element->addValidator(new RequiredSomeOne(array('start', 'end')));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Date());
		$element->addValidator(new DateSpan(array('elementNames'=>array('start', 'end'), 'form' => $this)));
		$element->setAttributes([
			'class' => 'datepicker w200',
			'maxlength' => $max,
		]);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('end', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Date());
		$element->setAttributes([
			'class' => 'datepicker w200',
			'maxlength' => $max,
		]);
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

		$max = 2000;
		$element = new Element\Text('url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Url());
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
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