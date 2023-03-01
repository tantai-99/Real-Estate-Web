<?php
namespace Library\Custom\User\ACL;

use Library\Custom\User\UserAbstract;

class ACLAbstract {
	
	/**
	 * @var Custom_ACL
	 */
	static protected $_instance;
	
	protected $_list = array();
	
	protected function __construct() {
	}
	
	public function getAllowedPrivileges($module, $controller = null, $action = null) {
		if ($module instanceof Zend_Controller_Request_Abstract) {
			$request = $module;
			$module		= $request->getModuleName();
			$controller	= $request->getControllerName();
			$action		= $request->getActionName();
		}
		
		if ($controller && $action) {
            $key = $module.'.'.$controller.'.'.$action;
			if (isset($this->_list[$key])) {
				return $this->_list[$key];
			}
		}
		
		if ($controller) {
			$key = $module.'.'.$controller;
			if (isset($this->_list[$key])) {
				return $this->_list[$key];
			}
		}
		
		if (isset($this->_list[$module])) {
			return $this->_list[$module];
        }
		
		return array();
	}
	
	/**
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	public function isAllowed($module, $controller = null, $action = null) {

        
        $allowedPrivileges = $this->getAllowedPrivileges($module, $controller, $action);
        
		if (!$allowedPrivileges) {
			return true;
		}
		return UserAbstract::factory($module)->hasPrivilege($allowedPrivileges);
	}
	
	/**
	 * @return ACLAbstract
	 */
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
}