<?php
namespace Library\Custom\Model\Lists;

class LogType extends ListAbstract {

    static protected $_instance;
    protected $_list;

    public function __construct()
    {
        $this->_list = array(
            config('constants.log_type.LOGIN')   => '代行ログイン操作ログ',
            config('constants.log_type.CREATE')   => '代行作成操作ログ',
            config('constants.log_type.COMPANY')  => '会員操作ログ',
            config('constants.log_type.PUBLISH')  => '公開処理ログ'
        );
    }

}