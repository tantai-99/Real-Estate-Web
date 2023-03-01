<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Date;

class ForReview extends ElementAbstract {

	protected $_columnMap = array(
			'title'			=> 'attr_1',
			'area'			=> 'attr_2',
			'date'			=> 'attr_3',
			'image1'		=> 'attr_4',
			'image1_title'	=> 'attr_5',
			'image2'		=> 'attr_6',
			'image2_title'	=> 'attr_7',
			'review'		=> 'attr_8',
			'staff_name'	=> 'attr_9',
			'staff_comment'	=> 'attr_10'
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

		$max = 100;
		$element = new Element\Text('area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('date', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Date());
		$element->setAttributes([
			'class' => 'datepicker w300',
			'data-maxlength' => $max,
		]);
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

		$element = new Element\Wysiwyg('review', array('disableLoadDefaultDecorators'=>true));
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
		if ($image = $this->getElement('image1')->getValue()) {
			$images[] = $image;
		}
		if ($image = $this->getElement('image2')->getValue()) {
			$images[] = $image;
		}
		return $images;
	}
}