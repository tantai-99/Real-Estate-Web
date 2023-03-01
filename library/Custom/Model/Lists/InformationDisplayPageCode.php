<?php

namespace Library\Custom\Model\Lists;

use Library\Custom\Model\Lists\ListAbstract;

class InformationDisplayPageCode extends ListAbstract
{
    protected $_list = array();

    public function __construct()
    {
        $this->_list = array(
            config('constants.information_display_page_code.PRIVATE_VIEW')     => '非公開',
            config('constants.information_display_page_code.LOGIN_BEFORE_VIEW')  => 'ログイン前表示',
            config('constants.information_display_page_code.AFTER_LOGGING_VIEW') => 'ログイン後表示',
            config('constants.information_display_page_code.ALL_VIEW')           => '全て表示'
        );
    }
}
