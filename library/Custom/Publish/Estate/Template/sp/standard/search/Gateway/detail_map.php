<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class DetailMap extends _AbstractGateway {

    const AROUND = 2;
    private $tab = self::AROUND;

    protected $_page_api_url = '/v1api/search/detail';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // url
        $url = $this->apiUrl();

        // bukken id
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $this->request->getBukkenIdFromUrl());

        // tab
        $url .= $this->addParam(ApiGateway::KEY_TAB, $this->tab);

        $view = $this->fetch($url);

        if (!$view) {
            return false;
        }

        // closed estate
        if (isset($view->api->message_id)) {

            $this->config['page_code'] = SearchPages::DETAIL_CLOSED;

            // update config
            $view->_config = $this->getConfigByJson();

            // overwrite template
            $view->template = SearchPages::filename_by_code($this->config['page_code']);

            return $view;
        }

        $view->detailUrl = "{$this->request->parse['dirname']}/";

        return $view;
    }

}



