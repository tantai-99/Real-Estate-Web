<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;

class MultiCheckbox extends Checkbox {
    protected $_type = 'multiCheckbox';
    protected $_registerInArrayValidator = true;
}