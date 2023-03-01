<?php
namespace Library\Custom\User;
use Exception;
use App;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use stdClass;
class Agency extends UserAbstract
{

    protected $_session_namespace = 'custom_user_cms';
    // protected $_modelClass = 'App\Repositories\Company\CompanyRepository';

    protected $_currentHp;

    public function __construct() {
        $this->_session = session();
        $this->_sessionProfile = $this->_session->get($this->_session_namespace);
        $this->_last_actiontime = $this->_getLastActionTime();
    }

    public function getMapOption() {
        return $this->_session->isMapOptionAvailable;
    }

    public function setProfile($row, $adminPrivileges = array()) {
		parent::setProfile($row);

		$this->_sessionProfile->isMapOptionAvailable = $row->isMapOptionAvailable();
		
		if ($adminPrivileges) {
			$privileges = $adminPrivileges;
		}
		else {
			$privileges = array();
			if ($row->isAnalyze()) {
				$privileges[] = ACL::PRIV_COMPANY_ANALYZE;
			}
			else {
				$privileges[] = ACL::PRIV_COMPANY;
			}
		}
		
		$this->_sessionProfile->privileges = $privileges;
        $this->_session->put($this->_session_namespace, $this->_sessionProfile);
		$this->clearAdminProfile();
	}
	
	public function getPrivileges() {
		return isset($this->_sessionProfile->privileges) && $this->_sessionProfile->privileges ? $this->_sessionProfile->privileges : array();
	}

    public function getCurrentHp() {
        if ($this->_currentHp === null) {
            $profile = $this->getProfile();
            if (!$profile) {
                $this->_currentHp = false;
            }
            else if ($this->isCreator()) {
                $this->_currentHp = $profile->getCurrentCreatorHp();
            }
            else {
                $this->_currentHp = $profile->getCurrentHp();
            }
        }
        return $this->_currentHp;
    }

    public function clearCurrentHpCache() {
        $this->_currentHp = null;
    }

    public function getSecondEstate() {
        $profile = $this->getProfile();
        if (!$profile) {
            return false;
        }
        return $profile->getSecondEstate();
    }

    public function isAvailableSecondEstate() {
        $secondEstate = $this->getSecondEstate();
        return $secondEstate && $secondEstate->isAvailable();
    }

    public function getBackupHp() {
        if (!$profile = $this->getProfile()) {
            return null;
        }
        
        return $profile->getBackupHp();
    }

    public function createHp() {
        $profile = $this->getProfile();
        if (!$profile) {
            return false;
        }

        $this->clearCurrentHpCache();

        return $profile->createHp();
    }

    public function loginAgency($memberNo) {
        if (!$adminProfile = $this->getAdminProfile()) {
            throw new Exception('can not login as agency');
        }

        $table = \App::make(CompanyRepositoryInterface::class);
        $row = $table->fetchLoginProfileByMemberNo($memberNo);

        if (!$row) {
            return false;
        }
        
        $privileges = array(ACL::PRIV_AGENCY);
		if ($adminProfile->privilege_edit_flg) {
			$privileges[] = ACL::PRIV_ADMIN_EDIT;
		}
		if ($adminProfile->privilege_manage_flg) {
			$privileges[] = ACL::PRIV_ADMIN_MANAGE;
		}
		if ($adminProfile->privilege_create_flg) {
			$privileges[] = ACL::PRIV_ADMIN_CREATE;
		}
		if ($adminProfile->privilege_open_flg) {
			$privileges[] = ACL::PRIV_ADMIN_OPEN;
		}

        $this->clearCsrfToken();
        $this->setProfile($row, $privileges);
        $this->setAdminProfile($adminProfile);
        $this->setLoginCookie();

        return true;
    }

    // public function logoutCompanyForAgency() {
        // $adminProfile = $this->getAdminProfile();
        // $this->logout();
        // $this->setAdminProfile($adminProfile);
    // }

    public function isAgency() {
        return $this->hasPrivilege(array(ACL::PRIV_AGENCY));
    }

    public function isCreator() {
        return false;
    }

    public function canCreate() {
        return $this->hasPrivilege(array(ACL::PRIV_ADMIN_CREATE));
    }

    public function canOpen() {
        return $this->hasPrivilege(array(ACL::PRIV_ADMIN_OPEN));
    }

    public function isDeputize() {
        return $this->isAgency();
    }

    public function setAdminProfile($adminProfile) {
        if (is_null($this->_sessionProfile)) {
            $this->_sessionProfile = new stdClass();
        }
        $this->_sessionProfile->adminProfile = $adminProfile;
        $this->_session->put($this->_session_namespace, $this->_sessionProfile);
    }

    public function getAdminProfile() {
        return $this->_sessionProfile && isset($this->_sessionProfile->adminProfile) ? $this->_sessionProfile->adminProfile : null;
    }

    public function clearAdminProfile() {
        unset($this->_sessionProfile->adminProfile);
    }

    public function updateLoginDate() {
        $ca = \App::make(CompanyAccountRepositoryInterface::class);
        $items = array();
        $items["login_date"] = date("Y-m-d H:i:s");
        $where = array(["id", $this->getProfile()->id], ["login_id", $this->getProfile()->login_id]);
        $ca->update($where, $items);
    }
}