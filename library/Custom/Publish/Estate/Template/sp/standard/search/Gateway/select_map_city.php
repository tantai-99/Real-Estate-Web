<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SelectMapCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/spatial-city';
    protected $_namespace    = 'redirect';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // redirect from select prefecture
        session_start();
        if ($this->session->get('redirect')) {
            $url .= $this->addParam(ApiGateway::KEY_DIRECT_ACCESS, (string)true);
            $this->session->destroy();
        }

        // 物件一覧画面から遷移してきた場合、検索方法タブを無効にし、こだわり条件を追加する
        $data = [];
        if ($this->request->getPost(SearchPages::FROM_MAP_RESULT) !== null) {
            $url .= $this->addParam('disable_s_type_tab', (string)true);

            // condition
            $this->session = new Session('condition');
            if ($this->session->get(SearchCondition::CONDITION) !== null) {
                parse_str($this->session->get(SearchCondition::CONDITION), $data);
            }

        }

//        $view = $this->fetch($url);
        $view = $this->post($url, $data);



        return $view;
    }

}



