<?php
namespace Library\Custom\View\Helper;

class Route extends  HelperAbstract
{
    public function route($action = null, $controller = null, $module = null, $params = array())
    {
        $route = '/';
        $routes = [];
        if (!$module) {
            $module = getModuleName();
        }
        if (!$controller) {
            $controller = getControllerName();
        }
        if ($action == 'index' && $controller == 'index') {
            $controller = '';
        }
        if ($module != 'default') {
            $routes[] = $module;
        }
        if($controller != '') {
            $routes[] = $controller;
        }
        if ($action != 'index') {
            $routes[] = $action;
        }
        return $route.implode('/', $routes);
    }
}
