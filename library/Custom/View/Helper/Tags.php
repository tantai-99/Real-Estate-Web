<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Model\Lists;
require_once(base_path().'/library/template.inc');

class Tags extends  HelperAbstract
{
    static protected $_instance;
    
    protected $_storage = [];
    
    protected $_tpl;
    
    private $__BLOCK = [];
    
    static public function getInstance($companyId = null, $usePubTop = false, $pubTopSrcPath = null) {
		if (!static::$_instance) {
			static::$_instance = new static();
            
            if (!$companyId) {
                $companyId = getInstanceUser('cms')->getProfile()->id;
            }

            if($usePubTop && !is_null($pubTopSrcPath) && is_dir($pubTopSrcPath)) {
                static::$_instance->_tpl = new \Template($pubTopSrcPath);
            } else {
                static::$_instance->_tpl = new \Template(Lists\Original::getOriginalImportPath($companyId));
            }
		}
		return static::$_instance;
	}
    
    public function tags(){
        $instance = Tags::getInstance();
        
        return $instance;
    }
    
    public function __get($property)
    {
        if (!isset($this->_storage[$property])) {
            $this->_storage[$property] = '';
        }
        
        return $this->_storage[$property];
    }
    
    public function __set($property, $data='')
    {
        $this->_storage[$property] = $data;
    }
    
    private function getBlockName($file_name)
    {
        $name = count($this->__BLOCK) . '_' . md5($file_name);
        array_push($this->__BLOCK, $name);
        
        return $name;
    }
    
    public function newBlock($file_name)
    {
        $this->_tpl->set_file($this->getBlockName($file_name), $file_name);
    }
    
    public function printOutput()
    {
        if (count($this->__BLOCK) > 0) {
            $this->_tpl->parse("output", array_pop($this->__BLOCK));
        }
    }
    
    public function getOutput()
    {
        if (count($this->__BLOCK) > 0) {
            return $this->_tpl->parse("output", array_pop($this->__BLOCK));
        }
        
        return '';
    }
    
    public function assign($name, $value='')
    {
        if (count($this->__BLOCK) > 0) {
            $this->_tpl->set_var($name, $value);
        }
    }
    
    public function assignGroup($data)
    {
        if (is_array($data) && count($this->__BLOCK) > 0) {
            foreach ($data as $key => $val) {
                $this->assign($key, $val);
            }
        }
    }
}
