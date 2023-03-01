<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class Moving extends SubParts {

    protected $_has_heading = false;

    protected $_columnMap = array(
        'point'		=> 'attr_1',
    );

    public function init() {
        parent::init();

        $max = 100;
        $element = new Element\Text('point', array('disableLoadDefaultDecorators'=>true));
        $element->setValidRequired(true);
        $element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
        $this->add($element);

    }

    protected $_presetTypes = array(
        'tip'
    );

    protected $_freeTypes = array(
    );

    protected function _createPartsElement($type) {
        $element = null;
        if ($type == 'tip') {
            $element = new Tip();
        }
        return $element;
    }
}