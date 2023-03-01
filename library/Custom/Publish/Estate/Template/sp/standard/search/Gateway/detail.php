<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class Detail extends _AbstractGateway {

    const DETAIL = 1;
    private   $tab           = self::DETAIL;
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];
    protected $_page_api_url = '/v1api/search/detail';

    public function run() {

        // url
        $url = $this->apiUrl();

        // bukken id
        $bukken_id = $this->request->getBukkenIdFromUrl();
        if($this->request->getPost('from-cms') && $this->request->getPost('from-cms') == 1) {
            $bukken_id.= ':cms';
        }
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $bukken_id);

        // tab
        $url .= $this->addParam(ApiGateway::KEY_TAB, $this->tab);

        // histories
        $histories = $this->request->getHistoriesAll();
        if ($histories !== null) {
            $url .= $this->addParam(ApiGateway::KEY_HISTORIES, implode(',', $histories));
        }

        // 特集からのアクセス
        if ($this->request->getPost('special-path')) {
            $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $this->request->getPost('special-path'));
        }

        // 地図検索からのアクセス
        if ($this->request->getPost('from_searchmap')) {
            $url .= $this->addParam(ApiGateway::KEY_FROM_SEARCHMAP, 'true');
        }

        // 4293: Add log detail FDP
        if ($this->request->referer) {
            $url .= $this->addParam('referer', 'true');
        }

        $view = $this->fetch($url);

        if (!$view) {
            return $view;
        }

        // 地図検索からのアクセス
        if ($this->request->getPost('from_searchmap') == 'true') {
            $configObject                 = json_decode($view->_config);
            $configObject->from_searchmap = true;
            $view->_config                = json_encode($configObject);
        }

        // closed estate
        if (isset($view->api->message_id)) {
			return null;
// Phase1の４０４ページに変更。今後の動静が怪しいので一旦コードを残す。
//             $this->config['page_code'] = SearchPages::DETAIL_CLOSED;

//             // update config
//             $view->_config = $this->getConfigByJson();

//             // overwrite template
//             $view->template = SearchPages::filename_by_code($this->config['page_code']);

//             // view properties
//             $view->headerText  = 'ページが見つかりません';
//             $view->api->head   = $this->generateHeadElement($view->headerText);
//             $view->api->header = $this->generateHeaderElement($view->headerText);

//             // seo tag
//             $this->setClosedSeoTags();
//             $view->seoTags = $this->_seo_tags;

//             // header
//             $view->customHeader = ['HTTP/1.1 404 Not Found'];

//             return $view;
        }
        $doc = phpQuery::newDocument($view->api->content);
        // 4707 Check peripheral
        if (ApiGateway::boolPeripheral($url)) {
            $doc['.btn-contact-fdp']->remove();
        }
        // end 4707
        $view->api->content = $doc->htmlOuter();

        return $view;
    }


    /**
     * 公開終了時のhead
     *
     * @param $text
     * @return string
     */
    private function generateHeadElement($text) {

        $head = '';

        $top = phpQuery::newDocument($this->loadToppageContents());

        // title
        $head .= $top['title']->prepend("{$text}｜")->htmlOuter();

        // keywords
        $keywords = $top['meta[name="keywords"]'];
        $head .= $keywords->attr('content', "{$text}, {$keywords->attr('content')}")->htmlOuter();

        // description
        $description = $top['meta[name="description"]'];
        $head .= $description->attr('content', "{$text}。{$description->attr('content')}")->htmlOuter();

        return $head;
    }

    /**
     * 公開終了時のheader
     *
     * @param $text
     * @return String
     */
    private function generateHeaderElement($text) {

        $top = phpQuery::newDocument($this->loadToppageContents());

        $h1 = phpQuery::newDocument('')->append('<h1>')->find('h1');
        $h1->addClass('tx-explain')->text("<<{$text}>>{$top->find('title')->text()}");
        return $h1->htmlOuter();
    }

    /**
     * 公開終了時のSEOタグ
     */
    private function setClosedSeoTags() {

        $this->_seo_tags = [
            SeoTags::NOINDEX,
            // SeoTags::NOFOLLOW,
            SeoTags::NOARCHIVE,
            SeoTags::CANONICAL,
            // SeoTags::REL,
            SeoTags::ALTERNATE,
        ];
    }

    private $toppageContents = null;

    /**
     * トップページのHTML読み込み
     *
     * @return null|string
     */
    private function loadToppageContents() {

        if ($this->toppageContents === null) {

            $contents              = file_get_contents(APPLICATION_PATH."/{$this->ua->requestDevice()}/index.html");
            $this->toppageContents = mb_convert_encoding($contents, 'HTML-ENTITIES', 'auto');
        }
        return $this->toppageContents;
    }

}



