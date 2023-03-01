<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Model\Lists\ShopDetail as ListShopDetail;
use Library\Custom\Form\Element as FormElement;
use App\Rules\StringLength;

class ShopDetail extends HasElement {

	protected $_is_unique = true;

	protected $_title = '店舗案内';
	protected $_template = 'shop-detail';
	
	protected $_has_heading = false;

	protected $_presetTypes = array(
			'adress',
			'tel',
			'access',
			'fax',
			'office_hour',
			'holiday',
	);

	protected $_freeTypes = array(
			'free',
	);
	
	protected $_requiredTypes = array(
			'adress',
			'tel',
	);

	protected function _createPartsElement($type) {

        $titles = ListShopDetail::getInstance()->getAll();

		$element = null;
		switch ($type) {
			case 'adress':
				$element = new Element\Text();
				$element->setTitle($titles[1]);
				break;
			case 'access':
				$element = new Element\Text();
				$element->setTitle($titles[2]);
				break;
			case 'tel':
				$element = new Element\Tel();
				$element->setTitle($titles[3]);
				break;
			case 'fax':
				$element = new Element\Fax();
				$element->setTitle($titles[4]);
				break;
			case 'office_hour':
				$element = new Element\Text();
				$element->setTitle($titles[5]);
				break;
			case 'holiday':
				$element = new Element\Text();
				$element->setTitle($titles[6]);
				break;
			case 'free':
				$element = new Element\TextFree();
				$element->setTitle('フリーテキスト');
				break;
			default:
				break;
		}

		if ($element && $type != 'free') {
			$element->setIsUnique(true);
		}

		return $element;
	}


	protected $_columnMap = array(
		'pr'			=> 'attr_1',
		'image1'		=> 'attr_2',
		'image1_title'	=> 'attr_3',
		'image2'		=> 'attr_4',
		'image2_title'	=> 'attr_5',
	);

	public function init() {
		parent::init();

		$element = new FormElement\Wysiwyg('pr', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setAttribute('rows', 6);
		$this->add($element);

		$element = new FormElement\Hidden('image1', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new FormElement\Text('image1_title', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('画像のタイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new FormElement\Hidden('image2', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new FormElement\Text('image2_title', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('画像のタイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (!isEmptyKey($_data, 'image1')) {
			$this->getElement('image1_title')->setRequired(true);
		}
		if (!isEmptyKey($_data, 'image2')) {
			$this->getElement('image2_title')->setRequired(true);
		}

		return parent::isValid($data);
	}

	public function getUsedImages() {
		$images = array();
		if ($image = $this->getElement('image1')->getValue()) {
			$images[] = $image;
		}
		if ($image = $this->getElement('image2')->getValue()) {
			$images[] = $image;
		}
		return $images;
	}
}