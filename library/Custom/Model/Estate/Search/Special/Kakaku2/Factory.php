<?php
namespace Library\Custom\Model\Estate\Search\Special\Kakaku2;
use Library\Custom\Model\Estate\Search\Special\Kakaku;

class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\Search\Special\AbstractList
	 */
	static public function get($estateType) {
		$master = Kakaku\Factory::get($estateType);
		if ($master) {
			$master->toKakaku2();
		}
		return $master;
	}
}
