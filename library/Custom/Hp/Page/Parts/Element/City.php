<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\AmenitiesCategory;
use Library\Custom\Hp\Page\Parts\Element\CityAmenities;

class City extends SubParts {

	protected $_has_heading = false;

	protected $_columnMap = array(
		'category'		=> 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new Element\Select('category', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + AmenitiesCategory::getInstance()->getAll());
		$this->add($element);

	}

	protected $_presetTypes = array(
			'amenities'
	);

	protected $_freeTypes = array(
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'amenities') {
			$element = new CityAmenities();
		}
		return $element;
	}
}