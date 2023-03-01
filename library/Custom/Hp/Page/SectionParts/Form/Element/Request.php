<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use Library\Custom\Form\Element;

class Request extends FreeItem {

	public function init() {
		parent::init();

		$element = new Element\Checkbox('detail_flg');
  		$element->setLabel('備考を表示させる');
		$this->add($element);
	}
}