<?php
namespace Library\Custom\Model\Estate\Search\Second\TochiMs2;
use Library\Custom\Model\Estate\Search\Second\TochiMs; 
class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\Search\Second\AbstractList
	 */
	static public function get($estateType) {
		$master = TochiMs\Factory::get($estateType);
		if ($master) {
			$master->toTochiMs2();
		}
		return $master;
	}
}
