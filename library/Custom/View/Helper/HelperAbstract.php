<?php
namespace Library\Custom\View\Helper;

class HelperAbstract {
    protected $_view;
    public function setViewParams($view) {
        $this->_view = $view;
    }
}