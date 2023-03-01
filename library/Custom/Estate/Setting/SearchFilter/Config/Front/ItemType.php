<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Front;

use Library\Custom\Estate\Setting\SearchFilter\Config\Special;
/* アイテムタイプ
 * 検索条件定義はCMS特集設定をベースにする
 *
 * */
class ItemType
	extends Special\ItemType {
	
	static protected $_instance;
	
	public function __construct() {

	    // 特集の定義をベースにする
	    parent::__construct();

        /*　リフォーム・リノベーションは特集とフロントで定義が異なる
         * 　　特集   ：特集はラジオ
         * 　　フロント：特集はチェックボックス
         * */
        unset($this->_list['reform_renovation']);

        // フロントは面積の 『以下』不要
        //unset($this->_list['menseki'][3]);
        //unset($this->_list['menseki'][4]);
	}
}