<?php

namespace Library\Custom\Model\Lists;

class SiteLogoType extends ListAbstract
{
    protected $_list;

    public function __construct()
    {
        $this->_list = array(
            1 => '画像のみ',
            2 => '画像とテキストの社名'
        );
    }
}
