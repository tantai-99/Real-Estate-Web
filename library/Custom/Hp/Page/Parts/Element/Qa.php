<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Lists\QaCategory;

class Qa extends SubParts {

	protected $_has_heading = false;

	protected $_columnMap = array(
		'category'		=> 'attr_1',
	);

	public function init() {
		parent::init();

		$element = new Element\Select('category', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + QaCategory::getInstance()->getAll());
		$this->add($element);

	}

	protected $_presetTypes = array(
			'qa'
	);

	protected $_freeTypes = array(
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'qa') {
			$element = new QaDetail();
		}
		return $element;
	}
}