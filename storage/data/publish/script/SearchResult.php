<?php
/*
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');

class SearchResult {

    private $apiResponse;

    public function __construct($apiResponse) {

        $this->apiResponse = $apiResponse;
    }

    public function html() {

        $docContent = phpQuery::newDocument($this->apiResponse->content);
        $docHidden  = phpQuery::newDocument($this->apiResponse->hidden);

        $selector = 'div.search-modal-detail';

        $docHidden[$selector]->after($docContent[$selector]->htmlOuter())->remove();
        $docContent[$selector]->remove();

        $this->apiResponse->content = $docContent->htmlOuter();
        $this->apiResponse->hidden  = $docHidden->htmlOuter();

        return $this->apiResponse;
    }
}
*/