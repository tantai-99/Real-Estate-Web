<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectChosonFromMultiCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/choson';
    protected $_namespace    = 'condition';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        // this page POST only

        session_start();
        $city = $this->session->get(SearchCondition::CITY);
        if (!$city) {
            return false;
        }

        // save condition
        $params = [
            SearchCondition::CONDITION => $this->request->getPost('condition'),
            SearchCondition::FULLTEXT => $this->request->getPost('fulltext'),
        ];
        $this->session->set($params);
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_CITY, $city);

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



