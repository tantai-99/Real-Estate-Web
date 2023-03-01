<?php
namespace Library\Custom\Model\Estate;

    class AbstractList {

    	static protected $_instance;
    	
    	/**
    	 * @return Library\Custom\Model\Estate\AbstractList
    	 */
    	static public function getInstance() {
    		if (!static::$_instance) {
    			static::$_instance = new static();
    		}
    		return static::$_instance;
    	}

        protected $_list = array();
        
        public function __construct() {
        	
        }

        public function get($key) {
            return isset($this->_list[$key]) ? $this->_list[$key] : NULL;
        }

        public function getAll() {
            return $this->_list;
        }
        
        public function pick($keys) {
        	$result = [];
        	foreach ($keys as $key) {
        		if (isset($this->_list[$key])) {
        			$result[$key] = $this->_list[$key];
        		}
        	}
        	return $result;
        }
        
        public function getFirstKey() {
        	$keys = array_keys($this->_list);
        	return $keys[0];
        }
        
        public function getLastKey() {
        	$keys = array_keys($this->_list);
        	return $keys[ count($keys) - 1 ];
        }
        
        public function gt($key) {
        	$result = [];
        	foreach ($this->_list as $k => $v) {
        		if ((int)$key < (int)$k) {
        			$result[$k] = $v;
        		}
        	}
        	return $result;
        }
        
        public function lt($key) {
        	$result = [];
        	foreach ($this->_list as $k => $v) {
        		if ((int)$key > (int)$k) {
        			$result[$k] = $v;
        		}
        	}
        	return $result;
        }
    }