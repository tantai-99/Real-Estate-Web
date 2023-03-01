<?php

require_once(APPLICATION_PATH.'/../script/_AbstractEstateContact.php');
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');

class ContactError extends _AbstractGateway {

    protected $_page_api_url = '/v1api/inquiry/error';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];
    
}




