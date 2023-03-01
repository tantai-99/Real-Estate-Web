<?php
namespace Library\Custom\View\Helper;

class Acl extends  HelperAbstract
{
	public function acl() {
		return $this;
	}
	
	public function isAllowed($action = null, $controller = null, $module = null) {
		$request = getRequestInfo();
        if (!$action) {
            $action = $request['action'];
        }
        if (!$controller) {
            $controller = $request['controller'];
        }
        if (!$module) {
            $module = $request['module'];
        }
	
		return \Library\Custom\User\ACL::getInstance()->isAllowed($module, $controller, $action);
	}
}