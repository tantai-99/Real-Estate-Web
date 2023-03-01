<?php
use Library\Custom\User\Admin;
use Library\Custom\User\Cms;
use Library\Custom\User\ACL;
use Library\Custom\User\UserAbstract;
if (!function_exists('getInstanceUser')) {
    function getInstanceUser($user)
    {
        $instance = null;
        // if (Auth::guard($user)->check()) {
            switch ($user) {
                case 'admin':
                    $instance = Admin::getInstance();
                    break;
                case 'cms':
                    $instance = Cms::getInstance();
                    break;
                
                default:
                    # code...
                    break;
            }
        // }

        return $instance;
    }
}

if (!function_exists('getRequestInfo')) {
    function getRequestInfo()
    {
        if (is_null(app('request')->route())) {
            return false;
        }
        $routeArray = app('request')->route()->getAction();
        $controllerAction = class_basename($routeArray['controller']);
        list($controller, $action) = explode('@', $controllerAction);

        $module =  str_replace('/', '', $routeArray['prefix']);

        return [
            'controller'    => strtolower(str_replace('Controller', '', $controller)),
            'action'        => $action,
            'module'       => $module == '' ? 'default' : $module
        ];
    }
}

if (!function_exists('getUser')) {
    function getUser() {
        $request = getRequestInfo();
        if (!$request) {
            return null;
        }
        return UserAbstract::factory($request['module'], $request['controller'], $request['action']);
    }
}