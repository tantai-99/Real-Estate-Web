<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SelectRailway extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/line';
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

        // reset condition (GET only)
        $this->resetSessionCondition();

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        
        session_start();
        if ($_SESSION['redirect']) {
            // redirect from select prefecture
            $url .= $this->addParam(ApiGateway::KEY_DIRECT_ACCESS, (string)true);
            unset($_SESSION['redirect']);
        }

        // 物件一覧画面から遷移してきた場合、検索方法タブを無効にし、こだわり条件を追加する
        $data = [];
        if ($this->request->getPost(SearchPages::FROM_RESULT) !== null) {
            $url .= $this->addParam('disable_s_type_tab', (string)true);

            // condition
            $this->session = new Session('condition');
            if ($this->session->get(SearchCondition::CONDITION) !== null) {
                parse_str($this->session->get(SearchCondition::CONDITION), $data);
            }
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
        if (isset($condition['railway'])) {

            $content = $view->api->content;
            $doc     = phpQuery::newDocument($content);

            $railwayList = explode(',', $condition['railway']);
            foreach ($railwayList as $railway) {
                $doc["input[value=\"{$railway}\"]"]->attr('checked', 'checked');
            }

            $view->api->content = $doc->htmlOuter();
        }

        return $view;
    }

}



