<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Url;

class BusinessContentItem extends ElementAbstract {
	protected $_columnMap = array(
			'image'			=> 'attr_1',
			'image_title'	=> 'attr_2',
			'business_name'	=> 'attr_3',
			'rubi'			=> 'attr_4',
			'description'	=> 'attr_5',
			'link_name'		=> 'attr_6',
			'url'			=> 'attr_7',
			'link_target_blank'	=> 'attr_8',
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);
		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setLabel('画像タイトル');
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('business_name', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('rubi', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 20;
		$element = new Element\Text('link_name', array('disableLoadDefaultDecorators'=>true));
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

		$element = new Element\Checkbox('link_target_blank', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('別窓で開く');
		$element->setChecked(true);
		$this->add($element);

	}

	public function isValid($data, $checkError = true) {
		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (isset($_data['image']) && $_data['image']) {
			$this->getElement('image_title')->setRequired(true);
		}

        $isValid = parent::isValid($data);
        
        if(empty($_data['link_name']) && !empty($_data['url'])) {
            $this->getElement('link_name')->setMessages(["リンク名を入力してください。"]);
            $isValid = false;
        }

        if(!empty($_data['link_name']) && empty($_data['url'])) {
            $this->getElement('url')->setMessages(["URLを入力してください。"]);
            $isValid = false;
        }
        return $isValid;
	}

	public function getUsedImages() {
		if ($image = $this->getElement('image')->getValue()) {
			return array($image);
		}
		return array();
	}
}