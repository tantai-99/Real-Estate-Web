<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use Library\Custom\Model\Lists\StaffDetail as ListStaffDetail;
use Library\Custom\Model\Lists\StaffPosition;
use Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element as FormElement;
use Library\Custom\Model\Lists\Qualification;
use App\Rules\StringLength;
use App\Rules\NameHuriganaAndAlpha;

class StaffDetail extends HasElement {

	protected $_is_unique = true;

	protected $_title = 'スタッフ詳細';
	protected $_template = 'staff-detail';

	protected $_has_heading = false;

	protected $_presetTypes = array(
			'nickname',
			'age',
			'department',
			'blog',
			'pride',
			'skill',
			'experience'
	);

	protected $_freeTypes = array(
			'free',
	);

	protected function _createPartsElement($type) {

        $titles = ListStaffDetail::getInstance()->getAll();

		$element = null;
		switch ($type) {
			case 'nickname':
				$element = new Element\Text();
				$element->setTitle($titles[1]);
				break;
			case 'age':
				$element = new Element\Text();
				$element->setTitle($titles[2]);
				break;
			case 'department':
				$element = new Element\Text();
				$element->setTitle($titles[3]);
				break;
			case 'blog':
				$element = new Element\Url();
				$element->setTitle($titles[4]);
				break;
			case 'pride':
				$element = new Element\Text();
				$element->setTitle($titles[5]);
				break;
			case 'skill':
				$element = new Element\Text();
				$element->setTitle($titles[6]);
				break;
			case 'experience':
				$element = new Element\Text();
				$element->setTitle($titles[7]);
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
		'name'			=> 'attr_1',
		'kana'			=> 'attr_2',
		'image'			=> 'attr_3',
		'image_title'	=> 'attr_4',
		'birthplace'	=> 'attr_5',
		'hobby'			=> 'attr_6',
		'qualification'	=> 'attr_7',
		'pr'			=> 'attr_8',
		'position'		=> 'attr_9',
		'shop_name'		=> 'attr_10',
	);

	public function init() {

        $titles = ListStaffDetail::getInstance()->getAll();

		parent::init();

		$element = new FormElement\Select('position', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array('選択してください') + StaffPosition::getInstance()->getAll());
		$element->setLabel($titles[8]);
		$this->add($element);

		$max = 20;
		$element = new FormElement\Text('shop_name', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setLabel($titles[9]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new FormElement\Text('name', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setLabel($titles[10]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new FormElement\Text('kana', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setLabel($titles[11]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new NameHuriganaAndAlpha());
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new FormElement\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new FormElement\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel($titles[12]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new FormElement\Text('birthplace', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel($titles[13]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$max = 100;
		$element = new FormElement\Text('hobby', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel($titles[14]);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		// $element = new FormElement\MultiCheckbox('qualification', array('disableLoadDefaultDecorators'=>true));
		$element = new FormElement\Checkbox('qualification', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel($titles[15]);
		$element->setSeparator('');
		$element->setValueOptions(Qualification::getInstance()->getAll());
		$this->add($element);

		$element = new FormElement\Wysiwyg('pr', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->setLabel($titles[16]);
		$element->setAttribute('rows', 6);
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
		if ($image = $this->getElement('image')->getValue()) {
			$images[] = $image;
		}
		return $images;
	}

	protected function _beforeSave($data) {
		$qualificationCol = $this->_columnMap['qualification'];
		if (!isEmptyKey($data, $qualificationCol) && is_array($data[ $qualificationCol ])) {
			$data[ $qualificationCol ] = implode(',', $data[ $qualificationCol ]);
		}
		return $data;
	}

	public function setDefaults(array $values) {
		$qualificationCol = $this->_columnMap['qualification'];
		if (!isEmptyKey($values, $qualificationCol) && !is_array($values[ $qualificationCol ])) {
			$values[ $qualificationCol ] = explode(',', $values[ $qualificationCol ]);
		}

		parent::setDefaults($values);
	}
}