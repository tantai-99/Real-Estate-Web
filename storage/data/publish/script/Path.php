<?php

require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/Request.php');

class Path {

    public function __construct() {

        $this->request = new Request();
    }

    /**
     * html中の相対パスを絶対パスに変換
     *
     * @param $html
     * @return String
     */
    public function setAbsolutePath($html) {

        $doc = phpQuery::newDocument($html);
        foreach ($doc['a[href^="/"]'] as $val) {

            $elem = pq($val);
            $elem->attr('href', $this->makeAbsolutePath($elem->attr('href')));
        };
        return $doc->htmlOuter();
    }

    /**
     * 絶対パス生成
     *
     * @param $relativePath
     * @return string
     */
    private function makeAbsolutePath($relativePath) {

        $absolutePath = $this->getProtocol($relativePath).'://'.$this->request->domain.$relativePath;

        // トップページ
        if ($absolutePath === "http://{$this->request->domain}/") {
            // 最後の'/'削除
            return "http://{$this->request->domain}";
        }

        return $absolutePath;
    }

    /**
     * protocol取得
     *
     * - お問い合わせ系のみhttps
     *
     * @param $relativePath
     * @return string
     */
    private function getProtocol($relativePath) {

        // お問い合わせ
        $contact = '\/contact';
        if (preg_match("/^$contact/", $relativePath)) {
            return 'https';
        }

        // 物件お問い合わせ
        $contact = '\/inquiry';
        if (preg_match("/^$contact/", $relativePath)) {
            return 'https';
        }

        return 'http';
    }
}