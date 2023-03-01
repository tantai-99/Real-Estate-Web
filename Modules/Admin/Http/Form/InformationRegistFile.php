<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class InformationRegistFile extends Form
{
	public function init()
	{
		

		//ファイル名
		$element = new Element\Text('name');
		$element->setLabel('ファイル名');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setAttributes(array("style" => "width:70%"));
		$this->add($element);

		/*
		//ファイル
		$element = new Zend_Form_Element_File('file');
		$element->setLabel('ファイル');
		$element->setRequired(true);
		$element->addValidator('Size', false, 5242880);
		$element->setDescription("※3MBまで");
		$this->add($element);
*/
		//fileId
		$element = new Element\Hidden('file_id');
		$element->setAttributes(array("class" => "file_id"));
		$this->add($element);

		$element = new Element\Hidden('tmp_file');
		$element->setAttributes(array("class" => "tmp_file"));
		$this->add($element);

		//ファイル名
		$element = new Element\Hidden('up_file_name');
		$element->setLabel('ファイル');
		$element->setDescription("※3MBまで");
		$element->setAttributes(array("class" => "up_file_name"));
		$this->add($element);
	}
}
