<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
class ImageCategory extends Form {
	
	public function __construct() {
		parent::__construct();
		
		$element = new Element\Text('id');
		$element->setLabel('ID');
		// $element->addValidator(new Int());
		$this->add($element);
		
		$element = new Element\Text('name');
		$element->setLabel('カテゴリ名');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 20)));
		$this->add($element);
		
		$element = new Element\Text('sort');
		$element->setLabel('並び順');
		$element->setRequired(true);
			// ->addValidator(new Int());
		$this->add($element);
	}
}