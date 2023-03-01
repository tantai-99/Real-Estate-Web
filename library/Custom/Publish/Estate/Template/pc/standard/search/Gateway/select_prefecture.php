<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SelectPrefecture extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/pref';
    protected $_namespace    = 'redirect';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        $url = $this->apiUrl();

        $allowRedirect = var_export($this->request->accessFrom() !== null, true);
        $url .= $this->addParam(ApiGateway::KEY_ALLOW_REDIRECT, $allowRedirect);

        $view = $this->fetch($url);

        if (!$view) {
            return false;
        }

        // redirect
        if (isset($view->api->redirect_to)) {
            session_start();
            $this->session->set(['redirect' => true]);
            $this->redirect($view->api->redirect_to);
        }

        if ($view->api->info->pref_count < 2 && $allowRedirect === 'false') {

            $this->_seo_tags[] = SeoTags::NOINDEX;
            $view->seoTags     = $this->_seo_tags;
        }

        return $view;
    }

}



