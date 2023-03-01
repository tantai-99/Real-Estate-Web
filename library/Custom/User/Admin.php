<?php
namespace Library\Custom\User;

use Library\Custom\User\ACL;

class Admin extends UserAbstract {
    protected $_session_namespace = 'custom_user_admin';
    protected $_guard = 'admin';
	
	public function setProfile($row) {
		parent::setProfile($row);
		
		$privileges = array(ACL::PRIV_ADMIN);
        
        $canOpenAgency = $row->privilege_open_flg;
        $canCreateAgency = $row->privilege_create_flg;
        $canCreateAccount = $row->privilege_manage_flg;
        
        if (!$this->checkIsSuperAdmin($row) && ($canOpenAgency || $canCreateAgency)) {
            $privileges = array(ACL::PRIV_AGENCY);
            
            $row->privilege_edit_flg = 1;
            $row->privilege_manage_flg = 0;

            if ($canCreateAccount) {
                $row->privilege_manage_flg = 1;
            }
        }
        
		if ($row->privilege_edit_flg) {
			$privileges[] = ACL::PRIV_ADMIN_EDIT;
		}
		if ($row->privilege_manage_flg) {
			$privileges[] = ACL::PRIV_ADMIN_MANAGE;
		}
		if ($row->privilege_create_flg) {
			$privileges[] = ACL::PRIV_ADMIN_CREATE;
		}
		if ($row->privilege_open_flg) {
			$privileges[] = ACL::PRIV_ADMIN_OPEN;
		}
		$this->_sessionProfile->privileges = $privileges;
        $this->_session->put($this->_session_namespace, $this->_sessionProfile);
	}
	
    public function checkIsSuperAdmin($data) {
        return 1 == $data->id;
    }
    
	public function getPrivileges() {
		return $this->_sessionProfile->privileges ? $this->_sessionProfile->privileges : array();
	}
    
    public function isAgency() {
		return $this->hasPrivilege(ACL::PRIV_AGENCY);
	}
    
    public function getUserId() {
        $profile = $this->getProfile();
		return $profile ? $profile->id : null;
	}
    
    public function setAgency($objectAgency){
        $this->_agency=$objectAgency;
    }
    
    public function getAgency(){
        return $this->_agency;
    }
}