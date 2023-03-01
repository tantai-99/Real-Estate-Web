<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\HudousanyougoYomi;

class Terminology extends ElementAbstract {

	protected $_columnMap = array(
		'word'			=> 'attr_1',
		'kana'			=> 'attr_2',
		'description'	=> 'attr_3',
		'image'			=> 'attr_4',
		'image_title'	=> 'attr_5',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('word', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->setAttributes([
			'data-name' => 'word',
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('kana', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new HudousanyougoYomi());
		$element->setAttributes([
			'data-name' => 'kana',
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setAttributes([
			'data-name' => 'description',
			'rows' => 6,
		]);
		$this->add($element);

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('data-name', 'image');
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->setAttributes([
			'data-name' => 'image_title',
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}
	
	public function isReplaceNotEmptyMessage() {
		return false;
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image')) {
			$this->getElement('image_title')->setRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		$images = array();
		if ($id = $this->getElement('image')->getValue()) {
			$images[] = $id;
		}
		return $images;
	}
	
	public function getSamples() {
		return json_decode(file_get_contents(storage_path('data/samples/terminology.json')), true);
	}
}