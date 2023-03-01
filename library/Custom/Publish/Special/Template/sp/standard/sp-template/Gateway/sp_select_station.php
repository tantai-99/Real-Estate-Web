<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectStation extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/eki';
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

        // this page GET only

        // reset condtion
        $this->resetSessionCondition();

        // save condition
        $params = [
            SearchCondition::RAILWAY => $this->request->getRailwayFromUrl(3),
        ];
        session_start();
        $this->session->set($params);
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->request->getRailwayFromUrl(3));
        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        // base url
        $view->baseUrl = $this->request->getMobileSelectBaseUrl();

        // populate
        $condition = $this->session->getAll();
        if (isset($condition['station'])) {

            $content = $view->api->content;
            $doc     = phpQuery::newDocument($content);

            $stationList = explode(',', $condition['station']);
            foreach ($stationList as $station) {
                $doc["input[value=\"{$station}\"]"]->attr('checked', 'checked');
            }

            $list = $doc['.list-select-set'];
            foreach ($list as $docList) {
                $docList = pq($docList);
                if ($docList[':checkbox:checked']->size() + 1 === $docList[':checkbox:not(:disabled)']->size()) {
                    $docList[':checkbox:not(:disabled)']->attr('checked', 'checked');
                }
            }

            $view->api->content = $doc->htmlOuter();
        }

        return $view;
    }
}



