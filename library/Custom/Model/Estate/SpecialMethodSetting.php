<?php
namespace Library\Custom\Model\Estate;

class SpecialMethodSetting extends AbstractList {
	
    const METHOD_DEFAULT = 1;
    const METHOD_INVIDIAL = 2;
    const METHOD_RECOMMENED = 3;
    static protected $_instance;

	protected $_list = [
		self::METHOD_DEFAULT => '条件を指定して特集をつくる',
		self::METHOD_INVIDIAL => '個別に物件を選択して特集をつくる',
		self::METHOD_RECOMMENED => 'おすすめ公開中の特集をつくる',
    ];
    
    public function hasDefaultMethod($method) {
        return self::METHOD_DEFAULT == $method;
    }
    public function hasInvidialMethod($method) {
        return self::METHOD_INVIDIAL == $method;
    }
    public function hasRecommenedMethod($method) {
        return self::METHOD_RECOMMENED == $method;
    }
}