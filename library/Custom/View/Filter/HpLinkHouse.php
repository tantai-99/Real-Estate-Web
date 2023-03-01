<?php
namespace Library\Custom\View\Filter;

class HpLinkHouse
{

    private $view;

    public function filter($buffer)
    {
        if (strpos($buffer, '###link_house_url:') === false){
            return $buffer;
        }

        $cb = function ($matches) {
            // ViewHelperを呼び出し \Library\Custom\View\Helper\hpLinkHouse.hpLinkHouse()
            return $this->view->hpLinkHouse($matches[1]);
        };

        return preg_replace_callback('/###link_house_url:(.+?)###/', $cb, $buffer);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
} 