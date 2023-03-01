<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class ResultChoson extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_CHOSON;
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page GET only

        $this->resetSessionCondition();

        // save condition
        $params = [
            SearchCondition::CHOSON => $this->request->getChosonFromUrl(4),
            SearchCondition::FROM    => $this->_s_type,
            SearchCondition::FULLTEXT => $this->request->getPost('fulltext'),
        ];
        session_start();
        $this->session->set($params);

        // fetch
        $url = $this->apiSearchUrl();
        $url .= $this->addParam(ApiGateway::KEY_CHOSON, $this->request->getChosonFromUrl(4));

        $data = [];
        $paramCondition      = $this->session->get(SearchCondition::CONDITION);
        $paramFulltext       = $this->session->get(SearchCondition::FULLTEXT);
        if ($paramCondition !== null || $paramFulltext !== null) {
            parse_str($paramCondition."&".$paramFulltext, $data);
        }
        $view = $this->post($url, $data);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}



