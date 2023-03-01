<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\HasSubParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element\Moving as PartMoving;

class Moving extends HasSubParts {

    protected $_is_unique = true;
    protected $_template = 'moving';

    protected $_title = '引っ越しのチェックポイント';

    protected $_presetTypes = array(
        'moving'
    );

    protected $_has_heading = false;

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
        if ($type == 'moving') {
            $element = new PartMoving();
        }

        return $element;
    }
}