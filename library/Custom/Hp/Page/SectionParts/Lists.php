<?php
namespace Library\Custom\Hp\Page\SectionParts;

class Lists extends SectionPartsAbstract {

	const PAGING_SIZE = 20;

	protected $_rowset;
	protected $_paginator;

    public function init() {

    	if ($this->_page->hasMultiPageType()) {
    		$p = isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
    		$count = static::PAGING_SIZE;

            $offset = max($p - 1, 0) * $count;

            $this->_rowset = $this->_page->fetchAllDetail(true, $count, $offset);
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator($this->_rowset, $this->_page->countDetail(), $count, $p);
            $path = '';
            $url = '/' . \Request::path();
            $path =  $path ? $path : $url;
            $paginator->setPath($path);
            $this->_paginator = $paginator;
    	}
    	else {
    		$this->_rowset = $this->_page->fetchAllDetail(true);
    	}
    }

    public function getRowset() {
    	return $this->_rowset;
    }

    public function getPaginator() {
    	return $this->_paginator;
    }
}