<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');

class Theme_Japanese extends Theme_Abstract {

    public function run() {

        // 情報の見方は除く
        if ($this->config['page_code'] !== SearchPages::HOWTOINFO) {
            $this->wrapBodyChildren('<div>', ['class' => 'bg']);
        }

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }

    /**
     * bodyの子要素をすべてラップ
     *
     * @param $tag
     */
    private function wrapBodyChildren($tag, $attr = []) {
        $this->doc['body']->wrapInner('<div class="bg">');
        /*
        <body>
          <script>...</script>
          <script>...</script>
          <{$tag} {$attr}>
            <{$child}>...</{$child}>
            <{$child}>...</{$child}>
            <{$child}>...</{$child}>
            <{$child}>...</{$child}>
          </{$tag}>
          <script>...</script>
          <script>...</script>
        </body>
        */
/*
        $body     = $this->doc->find('body');
        $children = $body->children();

        // 子要素を{$tag}でラップ
        $parent = $children->wrapAll($tag);
        foreach ($attr as $attribute => $val) {
            // 属性付与
            $parent->attr($attribute, $val);
        }

        // <body>直下タグを移動
        $break = false;
        $tags  = [];
        for ($i = 0; $i < $children->length; $i++) {
            $child = pq($children->eq($i));
            foreach ($child as $el) {
                if (strtolower($el->tagName) === 'script') {
                    $tags[] = $child;
                    break;
                }
                $break = true;
            }
            if ($break) {
                break;
            }
        }
        if (count($tags) > 0) {
            array_reverse($tags);
            foreach ($tags as $child) {
                $child->prependTo('body');
            }
        }

        // </body>直上タグを移動
        $break = false;
        $tags  = [];
        for ($i = $children->length - 1; 0 <= $i; $i--) {
            $child = pq($children->eq($i));
            foreach ($child as $el) {
                if (strtolower($el->tagName) === 'script') {
                    $tags[] = $child;
                    break;
                }
                $break = true;
            }
            if ($break) {
                break;
            }
        }
        if (count($tags) > 0) {
            foreach ($tags as $child) {
                $child->appendTo('body');
            }
        }
*/
    }

    /**
     * 地図検索用ヘッダに変える
     */
    protected function updateMapHeader() {

        $pageHeader = $this->doc['.page-header'];
        $pageHeader->wrap('<div class="maps-header"></div>');
        $pageHeader->addClass('page-header-liquid');

        $pageHeaderInner = $pageHeader->children('.inner');
        $pageHeaderTopInner = $this->doc['.page-header-top']->children('.inner');

        // 地図用の小さいロゴ
        $logoS=$pageHeaderInner->children('.logo')->clone();
        $logoS->addClass('logo-s')->removeClass('logo');
        $logoS->children('a')->append('<span class="company-link">トップページ</span>');
        $logoS->children('a')->children('.company-img')->attr('style', 'display: none;');
        if ($logoS->children('a')->children('.company-tx')->length<=0) {
            $logoS->children('a')->prepend('<span class="company-tx"></span>');
        }
        $logoS->children('a')->children('.company-tx')->text($this->hp['outline']);
        $pageHeaderTopInner->prepend($logoS);

        // 地図用の電話番号
        $telS=$pageHeaderInner->children('.header-info')->children('.tel-time')->children('.tel')->clone();
        $telS->addClass('tel-s')->addClass('show')->removeClass('tel');
        $pageHeaderTopInner->append($telS);

        // SNSボタン
        $pageHeaderTopInner->append($pageHeaderInner->children('.header-sns'));

        $pageHeaderTopInner->children('.tx-explain')->attr('style', 'display: none;');
        $pageHeaderTopInner->children('.link2')->attr('style', 'display: none;');
        $pageHeaderTopInner->children('logo-s')->attr('style', 'display: block;');

        $this->doc['.maps-header']->append($this->doc['.gnav']->attr('style','display:none;'));


        $logoS=$pageHeader->children('.inner')->children('.logo')->children('a')->append('<span class="company-link">トップページ</span>');


    }

}