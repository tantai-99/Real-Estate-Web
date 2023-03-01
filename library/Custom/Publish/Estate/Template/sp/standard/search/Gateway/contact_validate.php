<?php

require_once(APPLICATION_PATH.'/../script/_AbstractEstateContact.php');


class ContactValidate extends _AbstractEstateContact {
    protected $_page_api_url  = '/v1api/inquiry/edit';
    protected $_namespace     = 'contact';
    protected $_contactName   = '';
    protected $_token         = '';

    public function run() {
        $this->validateByAjax();
    }
}



