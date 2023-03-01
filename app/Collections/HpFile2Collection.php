<?php

namespace App\Collections;

class HpFile2Collection extends CustomCollection
{
	public function toResponseArray()
	{
		$rows = array();
		foreach ($this as $row) {
			$rows[] = $row->toResponseArray();
		}
		return $rows;
	}
}
