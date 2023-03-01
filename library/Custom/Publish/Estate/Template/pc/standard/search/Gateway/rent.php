<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class Rent extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/rent';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

}