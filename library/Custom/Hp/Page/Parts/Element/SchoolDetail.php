<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class SchoolDetail extends ElementAbstract {

	protected $_columnMap = array(
		'name'				=> 'attr_1',
		'school_zoning'		=> 'attr_2',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('name', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 1000;
		$element = new Element\Textarea('school_zoning', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count cancelEnter',
			'rows' => 13,
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}
}