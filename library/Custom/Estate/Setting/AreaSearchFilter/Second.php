<?php
namespace Library\Custom\Estate\Setting\AreaSearchFilter;
use Library\Custom\Model\Estate\SecondSearchTypeList;
class Second extends Basic {
	
	/**
	 * @var int
	 */
	public $search_type;
	
	public function init() {
		parent::init();
		$this->search_type = null;
	}
	
	public function getDisplaySearchType() {
	    if ($this->search_type == SecondSearchTypeList::TYPE_AREA && $this->canChosonSearch()) {
	        return '町名を対象にする';
        }
		return SecondSearchTypeList::getInstance()->get($this->search_type);
	}
	
}