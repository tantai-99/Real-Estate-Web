<?php
namespace Library\Custom\Model\Estate;

class ShumokuSort extends AbstractList {

	static protected $_instance;

	protected $_list =  [
        // 賃貸(アパート・マンション・一戸建て)
        '1' => [17,18,19,24,25],
        // 貸ビル・貸倉庫・その他
        '6' => [20,21,61,22,23,26,27,28,29,30,31,32,33,34,35,36,37,38],
        // 一戸建て（新築・中古）
        '8' => [39,40],	
        // 売ビル・売倉庫・売工場・その他
        '12' => [13,14,15,47,48,49,50,51,52,53,54,55,56,57,58,59,60,16,'<br style="clear:both;"/>',41,42,43,44,45,46]
    ];
}