<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');

class selectStationFromMultiRailway extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/eki';
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

        // this page POST only from select railway page

        // save condition
        $params = [
            SearchCondition::RAILWAY => $this->request->getPost('railway'),
        ];
        session_start();
        $this->session->set($params);

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->request->getPost('railway'));

        // •¨Œ�ˆê——‰æ–Ê‚©‚ç‘JˆÚ‚µ‚Ä‚«‚½�ê�‡�AŒŸ�õ•û–@ƒ^ƒu‚ð–³Œø‚É‚µ�A‚±‚¾‚í‚è�ðŒ�‚ð’Ç‰Á‚·‚é
        $data = [];
        $this->session = new Session('condition');
        if ($this->session->get(SearchCondition::CONDITION) !== null) {
            parse_str($this->session->get(SearchCondition::CONDITION), $data);
        }

        //$view = $this->fetch($url);
        $view = $this->post($url, $data);
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



