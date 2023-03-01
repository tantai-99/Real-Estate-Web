<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Second;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\CategoryDescription as AbstractCategoryDescription;
use Library\Custom\Model\Estate\TypeList;

class CategoryDescription extends AbstractCategoryDescription {
	
	static protected $_instance;
	
	public function __construct() {
		$list = [];
		//$list['chikunensu'] = '※築年数の設定は、土地・駐車場には反映されませんので、設定に関わらず土地・駐車場は公開されます';
		$list['tesuryo']['default'] = '※手数料は「客付配分100%」が対象となります';
		$list['tesuryo'][TypeList::TYPE_MANSION] = '';
		$list['tesuryo'][TypeList::TYPE_KODATE] = '';
		$list['tesuryo'][TypeList::TYPE_URI_TOCHI] = '';
		$list['tesuryo'][TypeList::TYPE_URI_TENPO] = '';
		$list['tesuryo'][TypeList::TYPE_URI_OFFICE] = '';
		$list['tesuryo'][TypeList::TYPE_URI_OTHER] = '';
		$this->_list = $list;
	}
}