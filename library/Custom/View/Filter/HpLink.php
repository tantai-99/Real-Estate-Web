<?php
namespace Library\Custom\View\Filter;

class HpLink
{
    private $view;

    public function filter($buffer)
    {
        if (strpos($buffer, '###link_page_id:') === false){
            return $buffer;
        }

        $cb = function ($matches) {
            // ViewHelperを呼び出し \Library\Custom\View\Helper\HpLink.hpLink()
            return $this->view->hpLink($matches[1]);
        };

        return preg_replace_callback('/###link_page_id:(.+?)###/', $cb, $buffer);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
} 