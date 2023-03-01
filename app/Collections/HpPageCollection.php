<?php

namespace App\Collections;

class HpPageCollection extends CustomCollection
{
	public function toSiteMapArray()
	{
		$rows = array();
		foreach ($this as $row) {
			$rows[] = $row->toSiteMapArray();
		}
		return $rows;
	}

	public function toSiteMapIndexArray()
	{
		$rows = array();
		foreach ($this as $row) {
			$rows[] = $row->toSiteMapIndexArray();
		}
		return $rows;
	}

	public function findRow($id)
	{

		foreach ($this as $i => $row) {
			if ((int)$row->id === (int)$id) {
				return $row;
			}
		}
		return null;
	}
}
