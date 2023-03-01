<?php
namespace Library\Custom\View;
use Library\Custom\Model\Lists\TopOriginalMsg;

class TopOriginalLang
{
    public function topOriginalLang(){
        return $this;
    }

    protected $text = array();

    public function __construct() {
        $this->text = TopOriginalMsg::$MESSAGES;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function get($prefix = ''){
        return (isset($this->text[$prefix]))?$this->text[$prefix]:'dummy-text';
    }

    /**
     * [ text1 => abc, text2 => def ]
     * use for multi array
     * @param array $array
     * @param string $autoKey
     * @return array $array
     */
    public function getMultiKeyValue($array = array(),$autoKey = ''){
        $data = array();
        $count = 1;
        foreach($array as $k => $v){
            $key = sprintf($this->get($autoKey),$count);
            $data[$key] = $this->get($v);
            $count++;
        }
        return $data;
    }
}