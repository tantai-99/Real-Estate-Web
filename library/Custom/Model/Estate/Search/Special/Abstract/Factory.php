<?php
namespace Library\Custom\Model\Estate\Search\Special\Abstract;
use Library\Custom\Model\Estate\TypeList;

class Factory {
	
	/**
	 * @param string $estateType
	 * @return Library\Custom\Model\Estate\AbstractList
	 */
	static public function get($estateType) {
		$consts = TypeList::getInstance()->getKeyConstWithComposite();
		$consts = array_flip($consts);
		if (!isset($consts[$estateType])) {
			return null;
		}
		
		$name = str_replace('TYPE_', '', $consts[$estateType]);
        $name = str_replace('COMPOSITE', 'COMPOSITE_', $name);
		$className = get_called_class();
		$className = preg_replace('/Factory$/', '', $className);
		$className .= pascalize($name);
		
		if (!@class_exists($className)) {
			return null;
		}
		return call_user_func([$className, 'getInstance']);
	}
}