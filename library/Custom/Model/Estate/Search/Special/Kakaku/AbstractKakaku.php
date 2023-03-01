<?php
namespace Library\Custom\Model\Estate\Search\Special\Kakaku;
use Library\Custom\Model\Estate\AbstractList;

class AbstractKakaku extends AbstractList {
	
	public function toKakaku1() {
		$this->_list = [0 => '下限なし'] + $this->_list;
		return $this;
	}
	
	public function toKakaku2() {
		$this->_list = $this->_list + [0 => '上限なし'];
		return $this;
	}
	
	static public function getInstance() {
		return new static();
	}
}