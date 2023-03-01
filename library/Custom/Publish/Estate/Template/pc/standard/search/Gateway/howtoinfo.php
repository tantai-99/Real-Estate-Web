<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/SearchCategory.php');

class Howtoinfo extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/howtoinfo';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        // テンプレート名
        $file = SearchCategory::getTemplateName($this->request->getPost('type'));
        if (!$file) {
            return false;
        }
        $this->view->tpl = $file;

        // 会社名取得
        ob_start();
        include_once(APPLICATION_PATH.'/common/pc/_header.html');
        $contents = ob_get_contents();
        ob_end_clean();

        // 文字化け対策
        $contents = mb_convert_encoding($contents, 'HTML-ENTITIES', 'auto');

        $domDocument = new DOMDocument();
        $domDocument->loadHTML($contents);
        $xpath = new DOMXPath($domDocument);

        $node                = $xpath->query('//span[@class="company-tx"]')->item(0);
        $this->view->company = $node !== null ? $node->nodeValue : '';

        // コピーライト取得
        $contents = file_get_contents(APPLICATION_PATH.'/common/pc/_footer.html');

        $domDocument->loadHTML($contents);
        $xpath = new DOMXPath($domDocument);

        $node                  = $xpath->query('//div[@class="copyright"]')->item(0);
        $this->view->copyright = $node !== null ? trim($node->nodeValue) : '';

        return $this->fetch($this->apiUrl());
    }
}



