<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/Search.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
require_once(APPLICATION_PATH.'/../script/SearchTodofuken.php');

class Validate {

    const PERSON_TEL_DIGIT_ONE = 1;
    const PERSON_TEL_DIGIT_TWO = 2;

    private $request;

    public function __construct() {

        $this->request = new Request();
    }

    public function url() {

        return $this->request->parse['extension'] || preg_match('/\/\z/', $this->request->current) || $this->request->has_url_params($this->request->current);
    }

    public function protcol($page_code) {

        // apiはどちらも許容
        if ((in_array($page_code, SearchPages::category_map()[SearchPages::CATEGORY_API]))||
            (in_array($page_code, SearchPages::category_map()[SearchPages::CATEGORY_API_MAP])))
        {
            return true;
        };

        return $this->request->protcol === SearchPages::protocol($page_code);
    }

    public function isValidPersonTel($personTelCount) {
        return !($personTelCount === self::PERSON_TEL_DIGIT_ONE || $personTelCount === self::PERSON_TEL_DIGIT_TWO);
    }

    public static function errorMsgPersonTel() {
        return '電話番号を入力するときは3箇所全部に入力してください。';
    }
}