<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Lists\Month;

class History extends ElementAbstract {

	protected $_columnMap = array(
			'year'			=> 'attr_1',
			'month'			=> 'attr_2',
			'text'			=> 'attr_3',
			'image'			=> 'attr_4',
			'image_title'	=> 'attr_5',
	);

	protected $_required_force = array(
			'year',
			'text',
	);

	public function init() {
		parent::init();

		$max = 4;
		$element = new Element\Text('year', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttribute('maxlength', $max);
		$this->add($element);


		$element = new Element\Select('month', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setValueOptions(Month::getInstance()->getAll());
		$this->add($element);

		$max = 100;
		$element = new Element\Text('text', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'maxlength' => $max,
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