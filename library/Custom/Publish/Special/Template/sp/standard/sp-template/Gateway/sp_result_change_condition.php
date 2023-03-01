<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class SpResultChangeCondition extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_PREF; // セッションのデータ消えちゃった時はこれで…
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page POST only

        // update condition
        // no change
        session_start();
        if ($s_type = $this->session->get(SearchCondition::FROM)) {
            $this->_s_type = $s_type;
        }

        // update config
        $this->view->_config = $this->getConfigByJson();

        // fetch
        $url = $this->apiSearchUrl();
        if ($this->session->get(SearchCondition::CITY) !== null) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $this->session->get(SearchCondition::CITY));
        }
        if ($this->session->get(SearchCondition::RAILWAY) !== null) {
            $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->session->get(SearchCondition::RAILWAY));
        }
        if ($this->session->get(SearchCondition::STATION) !== null) {
            $url .= $this->addParam(ApiGateway::KEY_STATION, $this->session->get(SearchCondition::STATION));
        }
        if ($this->session->get(SearchCondition::MCITY) !== null) {
            $url .= $this->addParam(ApiGateway::KEY_MCITY, $this->session->get(SearchCondition::MCITY));
        }
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



