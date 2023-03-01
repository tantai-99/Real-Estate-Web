<?php

require_once(APPLICATION_PATH.'/../script/_AbstractEstateContact.php'); 

class KasiKyojuuComplete extends _AbstractEstateContact {

    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    protected $_page_api_url  = '/v1api/inquiry/complete';
    protected $_namespace     = 'contact';
    protected $_contactName   = 'kasi-kyojuu';
    protected $_urlName       = 'kasi-kyojuu';


    public function run() {
        return $this->runComplete();
    }
}



