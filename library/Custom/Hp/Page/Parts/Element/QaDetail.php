<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class QaDetail extends ElementAbstract {

	protected $_columnMap = array(
		'q'				=> 'attr_1',
		'a'				=> 'attr_2',
		'link'			=> 'attr_4',
		'image'			=> 'attr_5',
		'image_title'	=> 'attr_6',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('q', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('a', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
		$this->add($element);

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
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
		return json_decode(file_get_contents(storage_path('data/samples/qa.json'), true));
	}
}