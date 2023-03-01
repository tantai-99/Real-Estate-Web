<?php

namespace Library\Custom\Model\Lists;

use Library\Custom\Model\Lists\ListAbstract;

class InformationDisplayTypeCode extends ListAbstract
{
    protected $_list = array();

    public function __construct()
    {
        $this->_list = array(
            config('constants.information_display_type_code.URL')          => '指定ＵＲＬ',
            config('constants.information_display_type_code.DETAIL_PAGE')  => '詳細ページ',
            config('constants.information_display_type_code.FILE_LINK')    => 'ファイルリンク'
        );
    }
}
