<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCategory.php');

class Howtoinfo extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/howtoinfo';
    protected $_seo_tags = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        // テンプレート名
        $file = SearchCategory::getTemplateName($this->request->getPost('type'));
        if (!$file) {
            return false;
        }
        $this->view->tpl = $file;

        return $this->fetch($this->apiUrl());
    }

}



