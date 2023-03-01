<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element\SampleModel as PartSampleModel;
use App\Rules\StringLength;

class SampleModel extends HasElement {

    protected $_title = '';
    protected $_template = 'sample_model';

    protected $_has_heading = false;

    protected $_is_unique = true;

    protected $_presetTypes = array(
        'sample_model'
    );

    protected $_columnMap = array(
        'title' => 'attr_1',
        'image' => 'attr_2',
        'image_title' => 'attr_3',
        'description' => 'attr_4',
    );

    public function init() {
        parent::init();


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

        $element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
        $element->setAttribute('rows', 6);
        $this->add($element);

    }

    protected function _createPartsElement($type) {
        $element = null;
        if ($type == 'sample_model') {
            $element = new PartSampleModel();
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