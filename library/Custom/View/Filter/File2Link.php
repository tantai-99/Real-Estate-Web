<?php
namespace Library\Custom\View\Filter;

class File2Link
{

    private $view;

    public function filter($buffer)
    {
        if (strpos($buffer, '###link_file_id:') === false){
            return $buffer;
        }

        $cb = function ($matches) {
            // ViewHelperを呼び出し \Library\Custom\View\Helper\HpFile2.hpFile2()
            return $this->view->hpFile2($matches[1]);
        };

        return preg_replace_callback('/###link_file_id:(.+?)###/', $cb, $buffer);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
} 