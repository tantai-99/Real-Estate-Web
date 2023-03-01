<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element as PartsElement;
use App\Rules\StringLength;
use Library\Custom\Model\Master\PageList;

class ArticleTemplate extends HasSubParts {

	protected $_is_unique = true;
	protected $_template = 'articles-tempate';

	protected $_title = '雛形記事';

	protected $_presetTypes = array(
			'articles',
	);

	protected $_has_heading = false;

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
		if (in_array($this->getPage()->page_type_code, \App::make(HpPageRepositoryInterface::class)->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))) {
			$this->_title = \App::make(HpPageRepositoryInterface::class)->getTypeNameJp($this->getPage()->page_type_code);
		}

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

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'articles') {
			$element = new PartsElement\Articles(['page' => $this->getPage(), 'hp' => $this->getHp()]);
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