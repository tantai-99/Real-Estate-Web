<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\Element\ColumnDetail as ColDetail;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class ColumnDetail extends Table {

	protected $_is_unique = true;

	protected $_title = 'コラム';
	protected $_template = 'column_detail';

	protected $_has_heading = false;

	protected $_presetTypes = array(
			'column_detail'
	);
   	protected $_required_force = array(
  			'image',
  			'image_title',
  			'read_content',
  	);
	protected $_columnMap = array(
			'image' => 'attr_1',
			'image_title' => 'attr_2',
			'read_content' => 'attr_3',
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$max = 30;
		$element = new  Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);

		$element = new  Element\Wysiwyg('read_content', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows', 6);
		$element->setRequired(true);
		$this->add($element);
		
		$this->setisRequired(true);
	}

	protected function _createPartsElement($type) {

		$element = null;
		if ($type == 'column_detail') {
			$element = new ColDetail();
		}
		return $element;
	}

    public function getUsedImages() {
        $elementImg = parent::getUsedImages();
        $images = array();
        if ($image = $this->getElement('image')->getValue()) {
            $images[] = $image;
        }
        return array_merge($images, $elementImg);
    }
}