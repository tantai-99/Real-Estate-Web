<?php

namespace Library\Custom\Model\Lists;

use Library\Custom\Model\Lists\ListAbstract;

class ManagerAccountAuthority extends ListAbstract
{
    protected $_list = array();

    public function __construct()
    {
        $this->_list = array(
            config('constants.manager_account_authority.PRIVILEGE_EDIT')    => "修正権限",
            config('constants.manager_account_authority.PRIVILEGE_MANAGE')   => "管理権限",
            config('constants.manager_account_authority.PRIVILEGE_CREATE')   => "代行作成権限",
            config('constants.manager_account_authority.PRIVILEGE_OPEN')     => "代行更新権限"
        );
    }

    public function getListPrivilegeByAgency()
    {
        return array(
            // config('constants.manager_account_authority.PRIVILEGE_EDIT')     => "修正権限",
            // config('constants.manager_account_authority.PRIVILEGE_MANAGE')   => "管理権限",
            config('constants.manager_account_authority.PRIVILEGE_CREATE')   => "代行作成権限",
            config('constants.manager_account_authority.PRIVILEGE_OPEN')     => "代行更新権限"
        );
    }
}
