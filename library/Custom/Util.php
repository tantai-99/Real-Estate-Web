<?php
namespace Library\Custom;
class Util {
    
    static public function isEmptyKey($params, $key) {
        return (!isset($params[ $key ])) || (empty($params[ $key ]) && !is_numeric($params[$key]));
    }
    
    static public function pascalize($str) {
    	return str_replace(' ', '', ucwords( preg_replace('/[\s\._]+/', ' ', strtolower($str)) ));
    }
    
    static public function camelize($str) {
    	$str = self::pascalize($str);
    	$str[0] = strtolower($str[0]);
    	return $str;
    }
    
    static public function isCli() {
    	return PHP_SAPI == 'cli';
    }
    
    static public function h($str) {
    	return htmlspecialchars($str);
    }
    
    static public function isEmpty($value) {
    	return empty($value) && !is_numeric($value);
    }
    
    static public function toNumericOrNull($value) {
    	return self::isEmpty($value) ? null : (int)$value;
    }
}