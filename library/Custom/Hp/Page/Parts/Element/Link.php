<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Url;

class Link extends ElementAbstract {

	protected $_columnMap = array(
		'name'			=> 'attr_1',
		'url'			=> 'attr_2',
		'description'	=> 'attr_3',
		'image'			=> 'attr_4',
		'image_title'	=> 'attr_5',
	);

	protected $_required_force = array(
			'name',
			'url',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('name', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 2000;
		$element = new Element\Text('url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Url());
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
		$this->add($element);


		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('画像のタイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}

    public function isValid($data, $checkError = true) {
		$isValid =parent::isValid($data);
        $_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

        if (isset($_data['image']) && $_data['image']) {
            $this->getElement('image_title')->setRequired(true);
        }

		if(array_key_exists('url', $data) && empty($data['url'])){
			$this->getElement('url')->setMessages(["URLを入力してください。"]);
			$isValid = false;
		}
		return $isValid;
    }


    public function getUsedImages() {
		$images = array();
		if ($image = $this->getElement('image')->getValue()) {
			$images[] = $image;
		}
		return $images;
	}

}