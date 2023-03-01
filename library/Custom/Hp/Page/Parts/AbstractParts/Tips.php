<?php
namespace Library\Custom\Hp\Page\Parts\AbstractParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element\Tip as PartTip;

class Tips extends HasElement {

    protected $_title = '';
    protected $_template = 'tips';

    protected $_has_heading = false;

    protected $_is_unique = true;

    protected $_presetTypes = array(
        'tip'
    );

    protected $_columnMap = array(
        'description' => 'attr_1',
    );

    public function init() {
        parent::init();

        $element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
        $element->setAttribute('rows', 6);
        $this->add($element);

    }

    protected function _createPartsElement($type) {
        $element = null;
        if ($type == 'tip') {
            $element = new PartTip();
        }
        return $element;
    }
}