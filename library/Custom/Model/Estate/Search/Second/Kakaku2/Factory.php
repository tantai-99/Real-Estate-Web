<?php
namespace Library\Custom\Model\Estate\Search\Second\Kakaku2;
use Library\Custom\Model\Estate\Search\Second\Kakaku;
class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\Search\Second\AbstractList
	 */
	static public function get($estateType) {
		$master = Kakaku\Factory::get($estateType);
		if ($master) {
			$master->toKakaku2();
		}
		return $master;
	}
}
