<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');

class DetailMap extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/detail';

    const AROUND = 2;
    private   $tab       = self::AROUND;
    protected $_seo_tags = [
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

        // histories
        $histories = $this->request->getHistoriesAll();
        if ($histories !== null) {
            $url .= $this->addParam(ApiGateway::KEY_HISTORIES, implode(',', $histories));
        }

        // fetch HTML
        $view = $this->fetch($url);

        if (!$view) {
            return $view;
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

        $doc = phpQuery::newDocument($view->api->content);

        $view->api->hidden = $doc['p.photo-zoom']->htmlOuter();
        $doc['p.photo-zoom']->remove();
        // 4707 Check peripheral
        if (ApiGateway::boolPeripheral($url)) {
            $doc['.btn-contact-fdp']->remove();
        }
        // end 4707
        $view->api->content = $doc->htmlOuter();

        return $view;
    }

}



