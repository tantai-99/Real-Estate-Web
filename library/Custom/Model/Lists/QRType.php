<?php

namespace Library\Custom\Model\Lists;

class QRType extends ListAbstract
{
    protected $_list;

    public function __construct()
    {
        $this->_list = array(
            1 => '全ページ共通',
            2 => '各ページ個別'
        );
    }
}
