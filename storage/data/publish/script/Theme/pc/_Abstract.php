<?php

require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/Theme/pc/_Interface.php');

abstract class Theme_Abstract implements Theme_interface {

    protected $html;
    protected $doc;
    protected $baseTheme;
    protected $config;

    public function __construct($hp, $html, $pagePath = null, $config = null) {

        $this->hp       = $hp;
        $this->html     = $html;
        $this->doc      = phpQuery::newDocument($html);
        $this->pagePath = $pagePath;
        $this->config   = $config;


    }

    /**
     * カスタムタグを入れる
     *
     */
    protected function customTag() {

        if( !$this->pagePath ){
            return;
        }
        // </head>直上
        $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/tag.blade.php'));

        // <body>直下
        $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/tag_under_body_tag.blade.php'));

        // </doby>直上
        $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/tag_above_close_body_tag.blade.php'));

        //物件お問い合わせ用タグを入れる
        $this->customEstateContactTag();

    }
    /**
     * カスタム物件お問い合わせ用タグを入れる
     *
     */
    protected function customEstateContactTag() {

        if( !$this->pagePath ){
            return;
        }

        //  賃貸居住用 編集画面
        if($this->config['page_code']==SearchPages::KASI_KYOJUU_EDIT) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_residential_rental_input.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_residential_rental_input.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_residential_rental_input.blade.php'));

        //  賃貸居住用 完了画面
        }else if($this->config['page_code']==SearchPages::KASI_KYOJUU_COMPLETE) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_residential_rental_thanks.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_residential_rental_thanks.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_residential_rental_thanks.blade.php'));

        //  賃貸事業用 編集画面
        }else if($this->config['page_code']==SearchPages::KASI_JIGYOU_EDIT) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_business_rental_input.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_business_rental_input.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_business_rental_input.blade.php'));

        //  賃貸事業用 完了画面
        }else if($this->config['page_code']==SearchPages::KASI_JIGYOU_COMPLETE) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_business_rental_thanks.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_business_rental_thanks.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_business_rental_thanks.blade.php'));

        //  売買居住用 編集画面
        }else if($this->config['page_code']==SearchPages::URI_KYOJUU_EDIT) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_residential_sale_input.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_residential_sale_input.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_residential_sale_input.blade.php'));

        //  売買居住用 完了画面
        }else if($this->config['page_code']==SearchPages::URI_KYOJUU_COMPLETE) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_residential_sale_thanks.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_residential_sale_thanks.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_residential_sale_thanks.blade.php'));

        //  売買事業用 編集画面
        }else if($this->config['page_code']==SearchPages::URI_JIGYOU_EDIT) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_business_sale_input.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_business_sale_input.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_business_sale_input.blade.php'));

        //  売買事業用 完了画面
        }else if($this->config['page_code']==SearchPages::URI_JIGYOU_COMPLETE) {
            // </head>直上
            $this->doc['head']->append(file_get_contents($this->pagePath.'/View/header/above_close_head_tag_business_sale_thanks.blade.php'));

            // <body>直下
            $this->doc['body']->prepend(file_get_contents($this->pagePath.'/View/header/under_body_tag_business_sale_thanks.blade.php'));

            // </doby>直上
            $this->doc['body']->append(file_get_contents($this->pagePath.'/View/header/above_close_body_tag_business_sale_thanks.blade.php'));
        }
    }

    /**
     * パンくず要素を.inner直下から、.contentsの兄弟要素の位置に移動
     *
     */
    protected function moveBreadcrumb() {

        if ($this->doc['.breadcrumb']->parent()->hasClass('inner')) {
            $this->doc['.contents']->prepend($this->doc['.breadcrumb']);
        }
    }

    /**
     * .innerをもつエレメントに.inner-apiを追加
     */
    protected function addClassInnerApi() {

        $this->doc['.inner']->addClass('inner-api');
    }

    /**
     * 地図検索用ヘッダに変える
     */
    protected function updateMapHeader() {



        $pageHeader = $this->doc['.page-header'];
        $pageHeader->wrap('<div class="maps-header"></div>');
        $pageHeader->addClass('page-header-liquid')->addClass('close');

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

        $this->doc['.maps-header']->append($this->doc['.gnav']->attr('style','display:none;'));


        $logoS=$pageHeader->children('.inner')->children('.logo')->children('a')->append('<span class="company-link">トップページ</span>');


    }

}