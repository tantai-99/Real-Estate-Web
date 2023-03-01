<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;

class Checkbox extends Select {
    /**
     * Separator to use between options; defaults to '<br />'.
     * @var string
     */
    protected $_separator = '<br />';

    protected $_type = 'checkbox';

    protected $isArray = true;

    protected $checked = false;

    /**
     * Retrieve separator
     *
     * @return mixed
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    public function setSeparator($separator)
    {
        return $this->_separator = $separator;
    }

    /**
     * Set separator
     *
     * @param mixed $separator
     * @return self
     */
    // public function setSeparator($separator)
    // {
    //     $this->_separator = $separator;
    //     return $this;
    // }
    public function setIsArray($flg = false)
    {
        $this->isArray = $flg;
        return $this;
    }

    public function setChecked($flg = false)
    {
        $this->checked = $flg;
        return $this;
    }

    public function isChecked()
    {
        return $this->checked;
    }
}