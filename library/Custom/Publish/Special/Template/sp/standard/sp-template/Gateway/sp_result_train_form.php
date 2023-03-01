<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class SpResultTrainForm extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_STATION_FORM;
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

        // save condition
        $params = [
            SearchCondition::STATION => $this->request->getPost('station'),
            SearchCondition::FROM    => $this->_s_type,
            SearchCondition::FULLTEXT  => $this->request->getPost('fulltext'),
        ];
        session_start();
        $this->session->set($params);

        // fetch
        $url = $this->apiSearchUrl();
        $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));

        $data = [];
        if ($this->session->get(SearchCondition::FULLTEXT) !== null || $this->session->get(SearchCondition::CONDITION) !== null) {
            parse_str($this->session->get(SearchCondition::FULLTEXT)."&".$this->session->get(SearchCondition::CONDITION), $data);
        }
        $view = $this->post($url, $data);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}

