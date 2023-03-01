<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class CustomCollection extends Collection
{
	/**
	 * @var int SQL_CALC_FOUND_ROWSの結果
	 */
	protected $_found_rows;

	/**
	 * SQL_CALC_FOUND_ROWSの結果をセットする
	 * @param int $rows
	 */
	public function setFoundRows($rows)
	{
		$this->_found_rows = $rows;
	}

	/**
	 * SQL_CALC_FOUND_ROWSの結果を取得する
	 * @return int
	 */
	public function getFoundRows()
	{
		return $this->_found_rows;
	}

	public function toAssocBy($colname)
	{
		$assoc = [];
		foreach ($this as $row) {
			$assoc[$row->{$colname}] = $row;
		}
		return $assoc;
	}
}
