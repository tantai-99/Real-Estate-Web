<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class CompanyFdpApply extends Form
{

    protected $_riyo;

    public function __construct($riyo = null)
    {
        $this->_riyo = $riyo;
        

        $element = new Element\Text('fdp_start');
        $element->setLabel('利用開始日');
        $element->setValue($this->_riyo['riyoStart']);
        $element->setAttributes(array(
            "class" => "is-lock",
            "disabled" => "is-disabled"
        ));
        $this->add($element);

        $element = new Element\Text('fdp_stop');
        $element->setLabel('利用停止日');
        $element->setValue($this->_riyo['riyoStop']);
        $element->setAttributes(array(
            "class" => "is-lock",
            "disabled" => "is-disabled"
        ));
        $this->add($element);
    }
}
