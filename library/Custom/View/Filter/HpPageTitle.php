<?php
namespace Library\Custom\View\Filter;

class HpPageTitle
{

    private $view;

    public function filter($buffer)
    {
        if (strpos($buffer, '###page_title:') === false){
            return $buffer;
        }

        $cb = function ($matches) {
            // ViewHelperを呼び出し \Library\Custom\View\Helper\HpLink.hpLink()
            return $this->view->hpPageTitle($matches[1]);
        };

        return preg_replace_callback('/###page_title:(.+?)###/', $cb, $buffer);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
} 