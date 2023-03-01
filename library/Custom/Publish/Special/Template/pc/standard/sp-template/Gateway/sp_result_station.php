<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class SpResultStation extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/result';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        SeoTags::REL,
        SeoTags::ALTERNATE,
    ];
    protected $_s_type       = SearchPages::S_TYPE_RESULT_STATION;

    public function run() {

        // base
        $url = $this->apiSearchUrl();

        // station
        $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getStationFromUrl(4));

        $data = [];
        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}
