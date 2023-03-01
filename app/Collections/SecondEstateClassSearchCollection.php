<?php

namespace App\Collections;

class SecondEstateClassSearchCollection extends CustomCollection {
    
	/**
	 * @var int SQL_CALC_FOUND_ROWSの結果
	 */
	protected $_found_rows;
	
	/**
	 * SQL_CALC_FOUND_ROWSの結果をセットする
	 * @param int $rows
	 */
	public function setFoundRows($rows) {
		$this->_found_rows = $rows;
	}
	
	/**
	 * SQL_CALC_FOUND_ROWSの結果を取得する
	 * @return int
	 */
	public function getFoundRows() {
		return $this->_found_rows;
	}
}