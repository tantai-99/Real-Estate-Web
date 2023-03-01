<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class SpResultPrefecture extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/result';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        SeoTags::REL,
        SeoTags::ALTERNATE,
    ];
    protected $_s_type       = SearchPages::S_TYPE_RESULT_PREF;

    public function run() {

        // must
        $url = $this->apiSearchUrl();
        $data = [];
        if ($this->request->getPost('detail')) {
            parse_str($this->request->getPost('detail'), $data);
            $view = $this->post($url, $data);
        } else {
            $view = $this->fetch($url);
        }

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);
        return $view;
    }
}



