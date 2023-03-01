<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectStationFromMultiRailway extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/eki';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        $url = $this->apiUrl();

        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // railway
        if ($this->request->getPost('railway')) {
            $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->request->getPost('railway'));
        }

        return $this->fetch($url);
    }

}



