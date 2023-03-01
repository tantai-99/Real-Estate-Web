<?php
namespace Library\Custom\Estate\Setting\AreaSearchFilter;
use ArrayObject;

class AreaData extends ArrayObject {
    
    
    public function getDataByPref($prefCode) {
        return isset($this->{$prefCode})?
                    $this->{$prefCode}:
                    [];
    }
    
    public function getAll() {
        $result = [];
        foreach ($this as $codes) {
        	foreach ($codes as $code) {
        		$result[] = $code;
        	}
        }
        return $result;
    }
}