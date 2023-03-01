<?php
namespace Library\Custom\Model\Estate\Search\Special;

class Factory {
	
	public function get($type, $categoryId, $itemId) {
		$name = pascalize($categoryId.'_'.$itemId);
		$factoryClass = 'Library\Custom\Model\Estate\Search\Special\\'.$name.'\Factory';
		if (!@class_exists($factoryClass)) {
			return null;
		}
		return call_user_func([$factoryClass, 'get'], $type);
	}
}