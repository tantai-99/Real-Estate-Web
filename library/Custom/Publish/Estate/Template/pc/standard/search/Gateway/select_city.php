<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SelectCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/city';
    protected $_namespace    = 'redirect';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // redirect from select prefecture
        if ($this->session->get('redirect')) {
            $url .= $this->addParam(ApiGateway::KEY_DIRECT_ACCESS, (string)true);
            $this->session->destroy();
        }

        return $this->fetch($url);
    }

}



