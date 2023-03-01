<?php
if (!function_exists('isEmptyKey')) {
    function isEmptyKey($params, $key) {
        return (!isset($params[ $key ])) || (empty($params[ $key ]) && !is_numeric($params[$key]));
    }
}
if (!function_exists('pascalize')) {
    function pascalize($str) {
        return str_replace(' ', '', ucwords( preg_replace('/[\s\._]+/', ' ', strtolower($str)) ));
    }
}
if (!function_exists('camelize')) {
    function camelize($str) {
        $str = pascalize($str);
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
if (!function_exists('isCli')) {
    function isCli() {
        return PHP_SAPI == 'cli';
    }
}
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str);
    }
}
if (!function_exists('isEmpty')) {
    function isEmpty($value) {
        return empty($value) && !is_numeric($value);
    }
}
if (!function_exists('toNumericOrNull')) {
    function toNumericOrNull($value) {
        return isEmpty($value) ? null : (int)$value;
    }
}