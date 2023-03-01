<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\TochiMs;
use Library\Custom\Model\Estate\AbstractList;

class TochiMsAbstract extends AbstractList {
	
	public function toTochiMs1() {
		$this->_list = [0 => '下限なし'] + $this->_list;
		return $this;
	}
	
	public function toTochiMs2() {
		$this->_list = $this->_list + [0 => '上限なし'];
		return $this;
	}
	
	static public function getInstance() {
		return new static();
	}
}