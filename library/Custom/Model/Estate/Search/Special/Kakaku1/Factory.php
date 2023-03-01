<?php
namespace Library\Custom\Model\Estate\Search\Special\Kakaku1;
use Library\Custom\Model\Estate\Search\Special\Kakaku;

class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\Search\Special\AbstractList
	 */
	static public function get($estateType) {
		$master = Kakaku\Factory::get($estateType);
		if ($master) {
			$master->toKakaku1();
		}
		return $master;
	}
}
