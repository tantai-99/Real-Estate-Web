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
    protected $_s_type       = SearchPages::S_TYPE_RESULT_DIRECT_RESULT;

    public function run() {

        $url = $this->apiUrl();

        // sort
        if ($sort = $this->request->getSort($this->request->directory(1))) {
            $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
        };

        // count
        if ($total = $this->request->getTotal()) {
            $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $total);
        };

        // page
        if ($this->request->getPage()) {
            $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPage());
        };

        $allowRedirect = var_export($this->request->accessFrom() !== null, true);
        $url .= $this->addParam(ApiGateway::KEY_ALLOW_REDIRECT, $allowRedirect);

        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        // 検索方法 検索画面なし
        // forward
        if ($view->api->directResult) {

            // page code
            $this->config['page_code'] = SearchPages::SP_RESULT_DIRECT_RESULT;

            // seo tags
            $this->_seo_tags = [
                // SeoTags::NOINDEX,
                // SeoTags::NOFOLLOW,
                SeoTags::NOARCHIVE,
                SeoTags::CANONICAL,
                SeoTags::REL,
                SeoTags::ALTERNATE,
            ];

            // update config
            $options       = [
                'shumoku'       => $view->api->info->type,
                'direct_result' => true,
            ];
            $view->_config = $this->getConfigByJson($options);

            $view->seoTags = $this->getSeoTags();
            $view          = $this->setNoindexIfNoEstate($view);

            // overwrite template
            $view->template = SearchPages::filename_by_code($this->config['page_code']);

            return $view;
        }

        // redirect
        if ($view->api->info->pref_count < 2 && $allowRedirect === 'false') {

            $this->_seo_tags[] = SeoTags::NOINDEX;
            $view->seoTags     = $this->_seo_tags;
        }

        // show select prefecture
        return $view;
    }

}



