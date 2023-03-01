<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element as PartsElement;
use App\Rules\StringLength;
use Library\Custom\Model\Master\PageList;

class OriginalTemplate extends HasSubParts {

	protected $_title = 'オリジナル記事';
	protected $_template = 'original-tempate';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_presetTypes = array(
			'original'
	);

	protected $_columnMap = array(
			'image' => 'attr_1',
			'image_title' => 'attr_2',
			'description' => 'attr_3',
	);

    protected $_required_force = array(
        'image',
        'image_title',
        'description',
    );

    protected $_hp;
    protected $_page;

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes(array('class'=>'watch-input-count','maxlength'=>$max));
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->getAttribute('rows',6);
		$this->add($element);

        $element = new Element\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
        $element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
        $this->add($element);
	}

	public function getPageType() {
		return $this->getPage()->page_type_code;
	}

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'original') {
			$element = new PartsElement\Original(['page' => $this->getPage(), 'hp' => $this->getHp()]);
		}
		return $element;
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

		// ATHOME_HP_DEV-5065 type=sampleの場合でも 子要素チェック
		// - type=sampleは自身と、その子要素に画像が存在する
		if ($this->hasElement()) {
			$forms = $this->getSubForm('elements')->getSubForms();
			foreach ($forms as $name => $form) {
				$ids = $form->getUsedImages();
				if ($ids) {
					$images = array_merge($images, $ids);
				}
			}
		}

		return $images;
	}
}