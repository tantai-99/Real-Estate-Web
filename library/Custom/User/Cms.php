<?php
namespace Library\Custom\User;

use App;
use Library\Custom\User\ACL;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use stdClass;
use Exception;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Model\Lists\LogEditType;

class Cms extends UserAbstract {

	protected $_session_namespace = 'custom_user_cms';

    protected $_guard = 'default';
	
    protected $_currentHp;
    
    protected $_has_backupdata;

    protected $_has_draft;
    
	private $hasTopOriginal;
	
	public function getMapOption()
	{
		return $this->_sessionProfile->isMapOptionAvailable;
	}
	
	public function setProfile($row, $adminPrivileges = array()) {
		parent::setProfile($row);

		$this->_sessionProfile->isMapOptionAvailable = $row->isMapOptionAvailable();
		
		if ($adminPrivileges) {
			$privileges = $adminPrivileges;
		}
		else {
			$privileges = array();
            if ($this->isAgency()) {
                $privileges = array(ACL::PRIV_AGENCY);
                $adminProfile = $this->getAdminProfile();
                if ($adminProfile) {
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
                }
            }
			if ($row->isAnalyze()) {
				$privileges[] = ACL::PRIV_COMPANY_ANALYZE;
			}
			else {
				$privileges[] = ACL::PRIV_COMPANY;
			}
		}
		$this->_sessionProfile->privileges = $privileges;
		$this->_session->put($this->_session_namespace, $this->_sessionProfile);
		
		$this->clearCurrentHpCache();
		$this->clearTantoCD();
        if (!$this->isAgency()) {
            $this->clearAdminProfile();
        }
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
			else if ($this->isCreator() || $this->isAgency()) {
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
	
    /**
     * check Custom_User_Cms has top original on setting profile
     *
     * @return boolean $hasTopOriginal
     */
    public function checkHasTopOriginal()
    {
        if (null === $this->hasTopOriginal) {
            $this->hasTopOriginal = false;
            
            $profile = $this->getProfile();
            if ($profile) {
                $this->hasTopOriginal = $profile->checkTopOriginal();
            }
        }
        
        return $this->hasTopOriginal;
    }
    
	/**
	 * 2次広告自動公開設定を取得する
	 */
	public function getSecondEstate() {
		$profile = $this->getProfile();
		if (!$profile) {
			return false;
		}
		return $profile->getSecondEstate();
	}
	
	/**
	 * 2次広告自動公開利用可能かチェックする
	 */
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
    
    public function hasBackupData() {
        if (!is_null($this->_has_backupdata)) {
    		return $this->_has_backupdata;
    	}
    	
    	$this->_has_backupdata = !!$this->getBackupHp();
    	return $this->_has_backupdata;
    }

    public function hasChanged() {
        if (!is_null($this->_has_draft)) {
    		return $this->_has_draft;
    	}
    	
        $hp = $this->getCurrentHp();
    	$this->_has_draft = ($hp && $hp->hasChanged());
    	return $this->_has_draft;
    }
	
	public function createHp() {
		$profile = $this->getProfile();
		if (!$profile) {
			return false;
		}
		
		$this->clearCurrentHpCache();
		
		return $profile->createHp();
	}
	
	public function loginAgent($memberNo, $tantoCD) {
		$table = App::make(CompanyRepositoryInterface::class);
		$row = $table->fetchLoginProfileByMemberNo($memberNo);
	
		if (!$row) {
			return false;
		}
		
		$this->clearCsrfToken();
		$this->setProfile($row, array(ACL::PRIV_AGENT));
		$this->setTantoCD($tantoCD);
		$this->setLoginCookie();
		
		// ログインログ
        CmsOperation::getInstance()->deputizeLog(LogEditType::LOGIN);
		
		return true;
	}
	
	public function loginCreator($memberNo) {
		if (!$adminProfile = $this->getAdminProfile()) {
			throw new Exception('can not login as creator');
		}
		
		$table = App::make(CompanyRepositoryInterface::class);
		$row = $table->fetchLoginProfileByMemberNo($memberNo);
	
		if (!$row) {
			return false;
		}
		
		$privileges = array();
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
		
		// ログインログ
        CmsOperation::getInstance()->deputizeLog(LogEditType::LOGIN);
		
		return true;
	}
	
	public function logoutCompanyForCreator() {
		$adminProfile = $this->getAdminProfile();
		$this->_sessionProfile->profile = null;
		$this->logout();
		$this->setAdminProfile($adminProfile);
	}

	public function loginAgency($memberNo) {

        if (!$adminProfile = $this->getAdminProfile()) {
            throw new Exception('can not login as agency');
        }

        $table = App::make(CompanyRepositoryInterface::class);
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
	
	public function isAgent() {
		return $this->hasPrivilege(array(ACL::PRIV_AGENT));
	}
	
	public function isCreator() {
		return $this->hasPrivilege(array(ACL::PRIV_ADMIN_CREATE, ACL::PRIV_ADMIN_OPEN));
	}
	
	public function canCreate() {
		return $this->hasPrivilege(array(ACL::PRIV_ADMIN_CREATE));
	}
	
	public function canOpen() {
		return $this->hasPrivilege(array(ACL::PRIV_ADMIN_OPEN));
	}
	
	public function isDeputize() {
		return $this->isAgent() || $this->isCreator();
	}
	
    /**
     * check User is nerfed when using top original
     *
     * @return boolean
     */
    public function isNerfedTop()
    {
        $nerfed = true;
        if ($this->isAgency() || !$this->checkHasTopOriginal()) {
            $nerfed = false;
        }
        
        return $nerfed;
    }
    
	public function getTantoCD() {
		return $this->_session->has('tantoCD') ? $this->_session->get('tantoCD') : null;
	}
	
	public function setTantoCD($cd) {
		$this->_session->put('tantoCD', $cd);
	}
	
	public function clearTantoCD() {
        $this->_session->put('tantoCD', null);
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
        $this->_sessionProfile->adminProfile = null;
		$this->_session->put($this->_session_namespace, $this->_sessionProfile);
	}


	/*
	* CMSユーザーのログイン日を設定
	*/
	public function updateLoginDate() {

		$ca = App::make(CompanyAccountRepositoryInterface::class);
		$items = array();
		$items["login_date"] = date("Y-m-d H:i:s");
		$where = array(["company_id", $this->getProfile()->id], ["login_id", $this->getProfile()->companyAccount()->first()->login_id]);
		$ca->update($where, $items);
	}

    public function isAgency() {
        return $this->hasPrivilege(array(ACL::PRIV_AGENCY));
    }
}