<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;

class Button extends Element
{
    protected $_type = 'select';

    public function isValid($checkError = true)
    {
        return true;
    }
}
