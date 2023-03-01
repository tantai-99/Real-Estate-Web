<?php
namespace Library\Custom\Model\Estate\Search\Second\TatemonoMs;
use Library\Custom\Model\Estate\AbstractList;

class TatemonoMsAbstract extends AbstractList {
	
	public function toTatemonoMs1() {
		$this->_list = [0 => '下限なし'] + $this->_list;
		return $this;
	}
	
	public function toTatemonoMs2() {
		$this->_list = $this->_list + [0 => '上限なし'];
		return $this;
	}
	
	static public function getInstance() {
		return new static();
	}
}