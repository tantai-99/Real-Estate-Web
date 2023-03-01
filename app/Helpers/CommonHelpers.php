<?php
use Illuminate\Support\Str;
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
            'controller'    => Str::snake(str_replace('Controller', '', $controller), '-'),
            'action'        => $action,
            'module'       => $module == '' ? 'default' : $module
        ];
    }
}
if (!function_exists('isCurrent')) {
    function isCurrent($action = null, $controller = null, $module = null, $params = array()) {
        $request = getRequestInfo();
        if (!$request) {
            return false;
        }
        return  ($module === null || $module == $request['modude']) &&
                ($controller === null || $controller == $request['controller']) &&
                ($action === null || $action == $request['action']) &&
                (sameParams($params));
    }
}
if (!function_exists('sameParams')) {
    function sameParams($params) {
        $request = app('request');
        foreach ($params as $key => $param) {
            if ($request->get($key) != $param) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('getControllerName')) {
    function getControllerName() {
        if (getRequestInfo()) {
            return getRequestInfo()['controller'];
        }
        return '';
    }
}

if (!function_exists('getActionName')) {
    function getActionName() {
        if (getRequestInfo()) {
            return getRequestInfo()['action'];
        }
        return '';
    }
}

if (!function_exists('getModuleName')) {
    function getModuleName() {
        if (getRequestInfo()) {
            return getRequestInfo()['module'];
        }
        return '';
    }
}

if (!function_exists('getConfigs')) {
    function getConfigs($name) {
        if (in_array($name, array('mail'))) {
            return json_decode(json_encode(config($name)));
        }
        $explodeName = explode('.', $name);
        if (count($explodeName) > 1) {
            $name = $explodeName[0] . '.environment.' . $explodeName[1];
        } else {
            $name = 'environment.'.$name;
        }
        return json_decode(json_encode(config($name)));
    }
}
if (!function_exists('urlSimple')) {
    function urlSimple($action, $controller = null, $module = null, array $params = null)
    {
        $url = '';
        if (null === $controller) {
            $controller = getControllerName();
        }

        if (null === $module) {
            $module = getModuleName();
        }

        if ($action == 'index' && $controller == 'index') {
            $controller = '';
        }
        if ($controller != '') {
            $url .= $controller . '/';
        }
        if ($action != 'index') {
            $url = $controller . '/' . $action;
        }
        
        if ($module != 'default') {
            $url = $module . '/' . $url;
        }

        if (null !== $params) {
            $paramPairs = array();
            foreach ($params as $key => $value) {
                $paramPairs[] = urlencode($key) . '/' . urlencode($value);
            }
            $paramString = implode('/', $paramPairs);
            $url .= '/' . $paramString;
        }

        $url = '/' . ltrim($url, '/');

        return $url;
    }
}
