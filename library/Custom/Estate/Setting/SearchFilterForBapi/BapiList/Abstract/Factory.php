<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Abstract;

use Library\Custom\Model\Estate;

class Factory {
	
	/**
	 * @param string $estateClass
	 */
	static public function get($estateType) {
		$consts = Estate\TypeList::getInstance()->getKeyConst();
		$consts = array_flip($consts);
		if (!isset($consts[$estateType])) {
			return null;
		}
		
		$name = str_replace('TYPE_', '', $consts[$estateType]);
		$className = get_called_class();
		$className = preg_replace('/Factory$/', '', $className);
		$className .= pascalize($name);
		
		if (!@class_exists($className)) {
			return null;
		}
		return call_user_func([$className, 'getInstance']);
	}
}