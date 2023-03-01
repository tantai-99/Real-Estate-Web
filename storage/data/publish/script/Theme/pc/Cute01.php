<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Cute01 extends Theme_Abstract {

    public function run() {

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }else{
            $this->moveGnav();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }

    /**
     * 'header'配下にgnavを置く
     *
     */
    private function moveGnav() {
        $this->doc['body']->children('#fb-root')->after('<header class="page-header" role="banner">');
        $this->doc['header']->append($this->doc['.page-header-top']);
        $this->doc['header']->append($this->doc['body']->children('.inner'));
        $this->doc['header']->append($this->doc['.gnav']);
    }

    /**
     * 地図検索用ヘッダに変える
     */
    protected function updateMapHeader() {

        $this->doc['body']->children('#fb-root')->after('<header class="page-header" role="banner">');
        $this->doc['header']->append($this->doc['.page-header-top']);
        $this->doc['header']->append($this->doc['body']->children('.inner'));

        $pageHeader = $this->doc['.page-header'];
        $pageHeader->wrap('<div class="maps-header"></div>');
        $pageHeader->addClass('page-header-liquid')->addClass('close');
        $pageHeader->append($this->doc['.gnav']->attr('style','display:none;'));

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
        $telS=$pageHeaderInner->children('.header-info')->children('.tel')->clone();
        $telS->addClass('tel-s')->addClass('show')->removeClass('tel');
        $pageHeaderTopInner->append($telS);

        // SNSボタン
        $pageHeaderTopInner->append($pageHeaderInner->children('.header-sns'));

        $pageHeaderTopInner->children('.tx-explain')->attr('style', 'display: none;');
        $pageHeaderTopInner->children('.link2')->attr('style', 'display: none;');
        $pageHeaderTopInner->children('logo-s')->attr('style', 'display: block;');



        $logoS=$pageHeader->children('.inner')->children('.logo')->children('a')->append('<span class="company-link">トップページ</span>');
    }

}