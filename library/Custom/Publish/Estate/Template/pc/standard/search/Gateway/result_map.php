<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class ResultMap extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/spatial-map';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_MAP;
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        // base
        $url = $this->apiSearchUrl();

        // city
        $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getCityFromUrl(4));

        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}
