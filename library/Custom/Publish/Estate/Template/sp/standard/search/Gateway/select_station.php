<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');

class SelectStation extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/eki';
    protected $_namespace    = 'condition';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page GET only

        // reset condition
        $this->resetSessionCondition();

        // save condition
        $params = [
            SearchCondition::RAILWAY => $this->request->getRailwayFromUrl(3),
        ];
        session_start();
        $this->session->set($params);

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->request->getRailwayFromUrl(3));
        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        // base url
        $view->baseUrl = $this->request->getMobileSelectBaseUrl();

        return $view;
    }

}



