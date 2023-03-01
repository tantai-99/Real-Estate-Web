<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectCondition extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/condition';
    protected $_namespace    = 'condition';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page POST only

        // validate
        if (!$this->request->getPost(SearchPages::FROM_STATION_SELECT) &&
            !$this->request->getPost(SearchPages::FROM_CITY_SELECT) &&
            !$this->request->getPost(SearchPages::FROM_RESULT) &&
            !$this->request->getPost(SearchPages::FROM_MAP_RESULT) &&
            !$this->request->getPost(SearchPages::FROM_CHOSON_SELECT)
        ) {
            return false;
        }

        // save condition
        session_start();
        if ($this->request->getPost(SearchPages::FROM_STATION_SELECT) !== null) {

            $params = [
                SearchCondition::STATION => $this->request->getPost('station'),
                SearchCondition::FROM    => SearchPages::S_TYPE_RESULT_STATION_FORM,
            ];
            $this->session->setNamespace('condition');
            $this->session->set($params);
        }

        if ($this->request->getPost(SearchPages::FROM_CITY_SELECT) !== null) {

            $params = [
                SearchCondition::CITY => $this->request->getPost('city'),
                SearchCondition::FROM => SearchPages::S_TYPE_RESULT_CITY_FORM,
            ];
            $this->session->setNamespace('condition');
            $this->session->set($params);
        }

        if ($this->request->getPost(SearchPages::FROM_MAP_RESULT) !== null) {
            // no change
            $params = [
                'center' => $this->request->getPost('center'),
                'zoom' => $this->request->getPost('zoom')
            ];
            $this->session->setNamespace('map_condition');
            $this->session->set($params);
        }

        if ($this->request->getPost(SearchPages::FROM_RESULT) !== null) {
            // no change
        }

        if ($this->request->getPost(SearchPages::FROM_CHOSON_SELECT) !== null) {
            // no change
            $params = [
                SearchCondition::CHOSON => $this->request->getPost('choson'),
            ];
            if ($this->request->getPost('choson')) {
                $params[ SearchCondition::FROM ] = SearchPages::S_TYPE_RESULT_CHOSON_FORM;
            } else {
                // 町名選択で何も選択せずに戻った場合、市区郡検索に戻す
                $params[ SearchCondition::FROM ] = SearchPages::S_TYPE_RESULT_CITY_FORM;
            }
            $this->session->setNamespace('condition');
            $this->session->set($params);
        }

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->session->get(SearchCondition::FROM));

        $this->session->setNamespace('condition');
        $param = $this->session->get(SearchCondition::RAILWAY);
        if ($param !== null) {
            $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $param);
        }
        $this->session->setNamespace('condition');
        $param = $this->session->get(SearchCondition::STATION);
        if ($param !== null) {
            $url .= $this->addParam(ApiGateway::KEY_STATION, $param);
        }
        $this->session->setNamespace('condition');
        $param = $this->session->get(SearchCondition::CITY);
        if ($param !== null) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $param);
        }

        $this->session->setNamespace('condition');
        $param = $this->session->get(SearchCondition::CHOSON);
        if ($param !== null) {
            $url .= $this->addParam(ApiGateway::KEY_CHOSON, $param);
        }
        if($this->request->getPost('fulltext')){
            $params = [
                SearchCondition::FULLTEXT => $this->request->getPost('fulltext'),
            ];
            $this->session->set($params);
        }

        $paramArray = [];
        $this->session->setNamespace('condition');
        $paramCondition      = $this->session->get(SearchCondition::CONDITION);
        $paramFulltext       = $this->session->get(SearchCondition::FULLTEXT);
        if ($paramCondition !== null || $paramFulltext !== null) {
            parse_str($paramCondition."&".$paramFulltext, $paramArray);
        }
        
        if ($paramCondition !== null && !empty($paramCondition)) {
            $url .= $this->addParam(ApiGateway::KEY_SP_SESSION, true);
        }

        $view = $this->post($url, $paramArray);
        if (!$view) {
            return $view;
        }
        // base url
        $view->baseUrl = $this->request->getMobileSelectBaseUrl();
        if ($this->request->getPost(SearchPages::FROM_MAP_RESULT) !== null) {
            $view->backUrl = $this->request->protcol . '://' . $this->request->domain . $this->request->getPost('back_path') . '/';
        }else{
            $view->backUrl = $view->baseUrl . 'result/';
            if (count(explode('/', parse_url($view->baseUrl, PHP_URL_PATH))) === 4) {
                $view->backUrl = str_replace('condition/', '', $view->backUrl);
            }
        }

        return $view;
    }

}



