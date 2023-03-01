<?php

namespace Library\Custom\Model\Lists;

class FooterLinkLevel extends ListAbstract
{
    protected $_list;

    public function __construct()
    {
        $this->_list = array(
            5 => 'すべて表示する（第5階層まで表示）',
            4 => '第4階層まで表示',
            3 => '第3階層まで表示',
            2 => '第2階層まで表示',
            0 => '表示しない',
        );
    }
}
