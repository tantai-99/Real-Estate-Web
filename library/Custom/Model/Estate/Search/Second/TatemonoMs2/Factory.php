<?php
namespace Library\Custom\Model\Estate\Search\Second\TatemonoMs2;
use Library\Custom\Model\Estate\Search\Second\TatemonoMs;

class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\Search\Second\AbstractList
	 */
	static public function get($estateType) {
		$master = TatemonoMs\Factory::get($estateType);
		if ($master) {
			$master->toTatemonoMs2();
		}
		return $master;
	}
}
