<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Text extends ElementAbstract {

	protected $_valueClass = 'Library\Custom\Form\Element\Text';

	protected $_valueMaxLength = 100;

	protected $_columnMap = array(
			'value' => 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new $this->_valueClass('value', array('disableLoadDefaultDecorators'=>true));
		if (get_class($element) == 'Library\Custom\Form\Element\Textarea') {
			$element->setAttribute('rows', 6);
		}
		$element->setValidRequired(true);
		// $element->setValue('part-list');
		$this->add($element);

		$this->setMaxLength($this->_valueMaxLength);
	}

	public function setMaxLength($max, $name = 'value') {
		// $validator = $this->getElement($name)->getValidators();
		// if (!$validator) {
		// 	$validator = new StringLength();
		// 	$validator->setMin(null);
		// 	$this->getElement($name)->addValidator($validator);
		// }
		// $validator->setMax($max);
		$this->getElement($name)->addValidator(new StringLength(array('max' => $max)));
		$this->getElement($name)->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		// ->class = array('watch-input-count');
		// $this->{$name}->maxlength = $max;

		return $this;
	}
}