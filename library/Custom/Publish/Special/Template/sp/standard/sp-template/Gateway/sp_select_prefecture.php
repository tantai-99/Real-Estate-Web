<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectPrefecture extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/pref';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        if ($this->request->method === Request::GET) {

            // reset condition
            $this->resetSessionCondition();

            // fetch
            $url = $this->apiUrl();
            if ($sort = $this->request->getSort($this->request->directory(1))) {
                $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
            };
            if ($total = $this->request->getTotal()) {
                $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $total);
            };
            $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPage());
            $url .= $this->addParam(ApiGateway::KEY_ALLOW_REDIRECT, $allowRedirect = var_export($this->request->accessFrom() !== null, true));
            $view = $this->fetch($url);

            // 404
            if (!$view) {
                return $view;
            }

            // 直接一覧（検索方法 検索画面なし）
            // forward
            if ($view->api->directResult) {

                // page code
                $this->config['page_code'] = SearchPages::SP_RESULT_DIRECT_RESULT;

                // seo tags
                $view = $this->setSeoTagsForListPage($view);

                // update config
                $view->_config = $this->updateConfig($view->api->info->type);

                // overwrite template
                $view->template = SearchPages::filename_by_code($this->config['page_code']);
                return $view;
            }

            // redirect
            if ($view->api->info->pref_count < 2 && $allowRedirect === 'false') {

                $this->_seo_tags[] = SeoTags::NOINDEX;
                $view->seoTags     = $this->_seo_tags;
                return $view;
            }

            return $view;
        }

        if ($this->request->method === Request::POST) {

            // change sort, paging
            // sp_result_change_condition.php
            session_start();
            if ($this->request->getPost(SearchPages::FROM_RESULT)) {

                // update condition
                // no change

                // overwrite page info
                $this->config['page_code'] = SearchPages::MOBILE_SP_RESULT_CHANGE_CONDITION;
                $this->_s_type             = $this->session->get(SearchCondition::FROM);

                // fetch
                $url = $this->apiSearchUrl([ApiGateway::KEY_PREFECUTURE]);
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

                // update config
                $view->_config = $this->updateConfig($view->api->info->type);

                // seo tags
                $view = $this->setSeoTagsForListPage($view);

                // overwrite template
                $view->template = SearchPages::filename_by_code($this->config['page_code']);

                return $view;
            }

            /*
            // from condition
            if ($this->request->getPost(SearchPages::FROM_CONDITION)) {

                // save condition
                $params = [
                    SearchCondition::CONDITION => $this->request->getPost('condition'),
                ];
                $this->session->set($params);

                // overwrite page info
                $this->config['page_code'] = SearchPages::MOBILE_SP_RESULT_FROM_CONDITION;
                $this->_s_type             = $this->session->get(SearchCondition::FROM);

                // fetch
                $url = $this->apiSearchUrl([ApiGateway::KEY_PREFECUTURE]);
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

                // update config
                $view->_config = $this->updateConfig($view->api->info->type);

                // seo tags
                $view = $this->setSeoTagsForListPage($view);

                // overwrite template
                $view->template = SearchPages::filename_by_code($this->config['page_code']);

                return $view;
            }
            */
        }
    }

    /**
     * 物件一覧のタグをセット
     *
     * @param $view
     * @return mixed
     */
    private function setSeoTagsForListPage($view) {

        $this->_seo_tags = [
            // SeoTags::NOINDEX,
            // SeoTags::NOFOLLOW,
            SeoTags::NOARCHIVE,
            SeoTags::CANONICAL,
            SeoTags::REL,
            SeoTags::ALTERNATE,
        ];

        $view->seoTags = $this->getSeoTags();

        return $this->setNoindexIfNoEstate($view);
    }

    /**
     * 物件一覧のconfigをセット
     *
     * @param $type
     * @return string
     */
    private function updateConfig($type){

        $options       = [
            'shumoku'       => $type,
            'direct_result' => true,
        ];
        return $this->getConfigByJson($options);
    }
}



