<?php
namespace Library\Custom\User;

use Library\Custom\Crypt\Password as CryptPassword;
use App\Models\Manager;
use Illuminate\Support\Facades\App;
use App\Repositories\Manager\ManagerRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use Auth;
use stdClass;

class UserAbstract {

    const LOCK_TIME = 600;

	static protected $_instance;
	
	protected $_session_namespace;

	protected $_sessionProfile;
	
	protected $_modelClass;
	
	protected $_last_actiontime;
	

	protected $_session;

	protected function __construct() {
		if (isset($this->_guard) && Auth::guard($this->_guard)->getRequest()->hasSession()) {
			$this->_session = Auth::guard($this->_guard)->getRequest()->session();
			$this->_sessionProfile = $this->_session->get($this->_session_namespace);
		}
		$this->_last_actiontime = $this->_getLastActionTime();
	}
	
	
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
	
	/**
	 * モジュールごとに使用するユーザオブジェクトを取得する
	 * @param string $module
	 * @return UserAbstract
	 */
	static public function factory($module, $controller = 'index', $action = 'index') {
		switch ($module) {
			case 'default':
                if ($controller == 'creator' && ($action == 'select-company' || $action == 'selectCompany')) {
                    return Agency::getInstance();
                } else{
    				return Cms::getInstance();
                }
			case 'admin':
				return Admin::getInstance();
			default:
				return Guest::getInstance();
		}
	}
	
	/**
	 * ログイン処理
	 * @return boolean
	 */
	public function login($login_id, $password) {
		if (!$this->_guard) {
			return false;
        }
        
        switch ($this->_guard) {
            case 'admin':
                $table = App::make(ManagerRepositoryInterface::class);
                $authTable = App::make(ManagerRepositoryInterface::class);
                break;
            case 'default':
                $table = App::make(CompanyRepositoryInterface::class);
                $authTable = App::make(CompanyAccountRepositoryInterface::class);
                break;
            default:
                # code...
                break;
		}
		$crypt = new CryptPassword();
        $rowByLoginId = $authTable->fetchRow([['login_id', '=', $login_id] ]);
        $row = $table->fetchLoginProfile($login_id, $crypt->encrypt($password));

        // アカウントロックチェック
		if ($rowByLoginId && !empty($rowByLoginId->locked_date)) {
            $lock_time_diff = strtotime('now') - strtotime($rowByLoginId->locked_date);
            // アカウントロック中
            if ($lock_time_diff < self::LOCK_TIME) {
                return false;
            } else {
                $table->unlockLoginAccount($rowByLoginId);
            }
        }

		if (!$row) {
            $table->failedLogin($rowByLoginId);
			return false;
		}
		
        Auth::guard($this->_guard)->login($row);

        $table->unlockLoginAccount($rowByLoginId);
		$this->clearCsrfToken();
		$this->setProfile($row);
		$this->setLoginCookie();
		
        $this->_session->regenerate();
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public function isLogin() {
		return !!$this->getProfile();
	}
	
	public function logout() {
		$this->_session->put($this->_session_namespace, null);
		$this->unsetLoginCookie();
		
        $this->_session->regenerate();
	}

	public function getProfile() {
		return $this->_sessionProfile ? isset($this->_sessionProfile->profile) ? $this->_sessionProfile->profile : null : null;
	}

	public function setProfile($row) {
		if (is_null($this->_sessionProfile)) {
			$this->_sessionProfile = new stdClass();
		}
		$this->_sessionProfile->profile = $row;
		$this->_session->put($this->_session_namespace, $this->_sessionProfile);
	}
	
	public function setAttemptedUri($request) {
		$this->_session->put('attemptedUri', $request->getRequestUri());
	}
	
	public function getAttemptedUri() {
		$uri = $this->_sessionProfile->attemptedUri;
		unset($this->_sessionProfile->attemptedUri);
		return $uri;
	}
	
	public function getPrivileges() {
		return array();
	}
	
	/**
	 * いずれかの権限を持っているか確認する
	 * @param string|array $privilges
	 * @return boolean
	 */
	public function hasPrivilege($privileges) {
        $privileges = (array)$privileges;
		return !!array_intersect($this->getPrivileges(), $privileges);
	}
	
	public function getCsrfToken() {
		if (!$this->_session->has('_token')) {
			$this->_session->put('_token', md5(uniqid(rand(),1)));
		}
		return $this->_session->get('_token');
	}

	public function isValidCsrfToken($token) {
		return $this->getCsrfToken() == $token;
	}
	
	public function clearCsrfToken() {
        if ($this->_session && $this->_session->has('_token')) {
            $this->_session->put('_token', null);
        }
	}
	
	/**
	 * ATHOME_HP_DEV-5105 「深刻度：重要」のCSRF対策を実施する
	 * CSRFトークンの再生成を行う
	 */
	public function regenerateCsrfToken() {
		$this->clearCsrfToken();
		return $this->getCsrfToken();
	}
	
	public function setLoginCookie() {
		setcookie($this->_getLoginCookieName(), 1, time() + (60 * 60 * 24 * 7), '/');
	}
	
	public function issetLoginCookie() {
		return isset($_COOKIE[$this->_getLoginCookieName()]);
	}
	
	public function unsetLoginCookie() {
		setcookie($this->_getLoginCookieName(), null, -1, '/');
	}
	
	protected function _getLoginCookieName() {
		return $this->_getCookieName('login');
	}
	
	public function setLastActionTime() {
		setcookie($this->_getLastActionTimeCookieName(), time(), time() + (60 * 60 * 24 * 7), '/');
	}
	
	public function checkLastActiontime() {
		if(is_null($this->_last_actiontime)) {
			return true;
		}
		if($this->_last_actiontime + (60 * 60) >= time()) {
			return true;
		}
		return false;
	}
	
	protected function _getLastActionTime() {
		return isset($_COOKIE[$this->_getLastActionTimeCookieName()]) ? $_COOKIE[$this->_getLastActionTimeCookieName()] : null;
	}
	
	public function isSessionTimeout() {
		return (!$this->isLogin()) &&
				($this->issetLoginCookie()) &&
				($this->_last_actiontime) &&
				$this->_last_actiontime + (60 * 60) < time();
	}
	
	protected function _getLastActionTimeCookieName() {
		return $this->_getCookieName('last_action');
	}
	
	protected function _getCookieName($name) {
		if ($this instanceof Cms) {
			$prefix = 'app_u';
		}
		else if ($this instanceof Admin) {
			$prefix = 'app_a';
		}
		else {
			$prefix = 'app_g';
		}
		
		return $prefix . $name;
	}

	public function updateLoginDate() {
	}

    /**
     * check setting use top original base on company
     *
     * @param integer $company_id
     * @return boolean
     */
    public function checkAvailableTopOriginal($company_id)
    {
        if (!property_exists($this, 'hasTop')) {
            $this->hasTop = new stdClass();
        }
        
        if (!property_exists($this->hasTop, $company_id)) {
            $this->hasTop->$company_id = false;
            
            $table = App::make(CompanyRepositoryInterface::class);
            $row = $table->find($company_id);
            
            if ($row) {
                $this->hasTop->$company_id = $row->checkTopOriginal();
            }         
        }
        
        return $this->hasTop->$company_id;
	}
	
	public function getGuardName() {
		return $this->_guard;
	}
}