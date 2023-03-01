<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Support\Facades\App;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Rules\StringLength;
use App\Rules\ImageContent;

class Image extends Form {
	
	public function __construct() {
		parent::__construct();

		$hp = getInstanceUser('cms')->getCurrentHp();
		
		$element = new Element\Hidden('hp_image_content_id');
		$element->setLabel('画像');
		$element->setRequired(true);
		$element->setAttributes([
			'class' => array('upload-file-id'),
			'data-upload-to' => '/api-upload/hp-image',
			'data-view' => '/image/hp-image',
		]);
		$element->addValidator(new ImageContent(App::make(HpImageContentRepositoryInterface::class), $hp->id));
		$this->add($element);
		
		$element = new Element\Text('title');
		$element->setLabel('画像タイトル');
		$element->setRequired(true);
		$element->setAttributes([
			'class' => array('watch-input-count'),
			'maxlength' => 30 ,
		]);
		$element->addValidator(new StringLength(array('max' => 30)));
		$this->add($element);
		
		$element = new Element\Select('category_id');
		$element->setLabel('画像カテゴリ');
		$element->setValueOptions(array(0 => '選択してください'));
		$this->add($element);
	}

	public function getMessages() {
		$messages = array();
	
		foreach ($this->getElements() as $name => $element) {
			if (!$element->hasErrors()) {
				continue;
			}
			$messages[$name] = $this->getGroupErrors(array($name));
		}
		return $messages;
	}
	
}