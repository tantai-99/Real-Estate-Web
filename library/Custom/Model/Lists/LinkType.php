<?php
namespace Library\Custom\Model\Lists;

class LinkType extends ListAbstract {

	static protected $_instance;
    
    protected $_list = array();

    public function __construct() {
        $this->_list = array(
            config('constants.link_type.PAGE') => '既存ページから選ぶ',
            config('constants.link_type.URL') => 'URL'					,
            config('constants.link_type.FILE') => 'ファイルをリンクする'	,
            config('constants.link_type.HOUSE') => '物件詳細を選ぶ'	,
        );
    }
}