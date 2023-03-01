<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class ResultTrainForm extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
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

        $this->resetSessionCondition();

        // update session
        $params = [
            SearchCondition::STATION => $this->request->getPost('station'),
            SearchCondition::FROM    => $this->_s_type,
            SearchCondition::FULLTEXT  => $this->request->getPost('fulltext'),
        ];

        session_start();
        $this->session->set($params);

        // must
        $url = $this->apiSearchUrl([ApiGateway::KEY_S_TYPE]);

        // station
        $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));

        // selected single
        if (count(explode(',', $this->request->getPost('station'))) < 2) {
            $this->_s_type = SearchPages::S_TYPE_RESULT_STATION;
        }
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->_s_type);

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



