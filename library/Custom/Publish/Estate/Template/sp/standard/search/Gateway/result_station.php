<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class ResultStation extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_STATION;
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page GET only

        $this->resetSessionCondition();

        // save condition
        $params = [
            SearchCondition::STATION => $this->request->getStationFromUrl(4),
            SearchCondition::FROM    => $this->_s_type,
        ];
        session_start();
        $this->session->set($params);

        // fetch
        $url = $this->apiSearchUrl();
        $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getStationFromUrl(4));

        $data = [];
        if ($this->session->get(SearchCondition::CONDITION) !== null) {
            parse_str($this->session->get(SearchCondition::CONDITION), $data);
        }
        $view = $this->post($url, $data);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}



