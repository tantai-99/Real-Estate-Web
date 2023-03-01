<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;

class Select extends Element {

    protected $_valuesOptions = [];

    protected $_type = 'select';

    public function setValueOptions($valueOptions) {
        $this->_valuesOptions = $valueOptions;
    }

    public function getValueOptions() {
        return $this->_valuesOptions;
    }

    public function getValueOption($key) {
        if (isset($this->_valuesOptions[$key])) {
            return $this->_valuesOptions[$key];
        }
        return null;
    }
}