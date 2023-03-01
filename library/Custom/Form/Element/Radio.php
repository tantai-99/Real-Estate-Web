<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;

class Radio extends Checkbox {
    protected $_type = 'radio';

    protected $isArray = false;

    protected $_registerInArrayValidator = true;
}