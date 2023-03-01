<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectChosonFromMultiCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/choson';
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
        if ($this->request->getPost('city')) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost('city'));
        }

        return $this->fetch($url);
    }

}



