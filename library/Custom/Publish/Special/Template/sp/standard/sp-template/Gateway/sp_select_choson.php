<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectChoson extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/choson';
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
        $city = $this->request->getCityFromUrl(3);
        $params = [
            SearchCondition::CITY => $city,
        ];
        session_start();
        $this->session->set($params);

        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_CITY, $city);

        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        // base url
        $view->baseUrl = $this->request->getMobileSelectBaseUrl();

        // populate
        $condition = $this->session->getAll();
        if (isset($condition['choson'])) {

            $content = $view->api->content;
            $doc     = phpQuery::newDocument($content);

            $chosonList = explode(',', $condition['choson']);
            foreach ($chosonList as $choson) {
                $doc["input[value=\"{$choson}\"]"]->attr('checked', 'checked');
            }

            $list = $doc['.list-select-set'];
            foreach ($list as $docList) {
                $docList = pq($docList);
                if ($docList[':checkbox:checked']->size() > 0 && $docList[':checkbox:checked']->size() + 1 === $docList[':checkbox:not(:disabled)']->size()) {
                    $docList[':checkbox:not(:disabled)']->attr('checked', 'checked');
                }
            }

            $view->api->content = $doc->htmlOuter();
        }

        return $view;
    }
}



