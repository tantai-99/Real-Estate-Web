<?php

require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/Theme/sp/_Interface.php');

abstract class Theme_Abstract implements Theme_interface {

    protected $html;
    protected $doc;
    protected $config;

    public function __construct($hp, $html, $pagePath = null, $config = null) {

        $this->html   = $html;
        $this->doc    = phpQuery::newDocument($html);
        $this->config = $config;
        $this->pagePath = $pagePath;

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
           $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateForResultMap();
        }
    }

    /**
     * 地図検索用
     */
    protected function updateForResultMap() {

        // top配下を slide-map-cover でwrapする
        $top = $this->doc['#top'];
        $top->prepend('<div class="slide-map-cover"></div>');
        $slidMapCover = $top->children('.slide-map-cover');
        $slidMapCover->append($top->children('.page-header'));
        $slidMapCover->append($top->children('.gnav'));
        $slidMapCover->append($top->children('.map-option__list'));
        $slidMapCover->append($top->children('.map-option__change'));
        $slidMapCover->append($top->children('.map-main'));
        $slidMapCover->append($top->children('.map-bl-list__toggle'));
        //$top->wrapInner();


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
}