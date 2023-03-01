<?php
namespace Library\Custom\Model\Lists;

class FtpPasvMode extends ListAbstract
{

    protected $_list;

    public function __construct()
    {
        $this->_list = array(
            config('constants.ftp_pasv_mode.IN_FORCE') => '有効',
            config('constants.ftp_pasv_mode.INVALID')  => '無効'
        );
    }
}
