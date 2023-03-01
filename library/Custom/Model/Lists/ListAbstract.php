<?php
namespace Library\Custom\Model\Lists;

class ListAbstract {
    static protected $_instance;

    /**
     * @return Library\Custom\Model\Lists\ListAbstract
     */
    static public function getInstance() {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    protected $_list = array();

    public function get($key) {
        return isset($this->_list[$key]) ? $this->_list[$key] : NULL;
    }

    public function getAll() {
        return $this->_list;
    }

    protected $_chinese;
    protected $_english; // sample

    public function getLanguage($language) {

        $res = array();
        foreach ($this->_list as $i => $japanese) {
            if (!isset($this->{'_'.$language}[$i])) {
                continue;
            }
            $res[$japanese] = $this->{'_'.$language}[$i];
        }
        return $res;

    }
}