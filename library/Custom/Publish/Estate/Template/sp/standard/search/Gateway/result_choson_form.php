<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class ResultChosonForm extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_CHOSON_FORM;
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
            SearchCondition::CHOSON => $this->request->getPost('choson'),
            SearchCondition::FROM    => $this->_s_type,
        ];
        session_start();
        $this->session->set($params);

        // must
        $url = $this->apiSearchUrl([ApiGateway::KEY_S_TYPE]);

        // choson
        $url .= $this->addParam(ApiGateway::KEY_CHOSON, $this->request->getPost('choson'));

        // selected single
        if (count(explode(',', $this->request->getPost('choson'))) < 2) {
            $this->_s_type = SearchPages::S_TYPE_RESULT_CHOSON;
        }
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->_s_type);

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



