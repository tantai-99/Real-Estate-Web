<?php
require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/Validate.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/Search.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
require_once(APPLICATION_PATH.'/../script/SearchTodofuken.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

class Route {

    public  $viewHelper;
    private $request;
    private $validate;
    private $ua;

    public function __construct(viewHelper $viewHelper) {

        $this->request    = new Request();
        $this->validate   = new Validate();
        $this->ua         = new UserAgent();
        $this->logger     = new Log();
        $this->viewHelper = $viewHelper;
    }

    public function run() {

        if (!$this->validate->url()) {
            $to = "{$this->request->current}/";
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->redirect($to);
        };

        $page_code = $this->find();

        if ($page_code === null) {
            return null;
        }

        if (SearchPages::post_only($page_code) && $this->request->method === Request::GET) {
            $to = SearchPages::redirect_path($page_code, $this->request);
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->redirect($to);
        }

        if (!$this->validate->protcol($page_code)) {
            $to = SearchPages::protocol($page_code).'://'.$this->request->parse['host'].$this->request->parse['path'];
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->redirect($to);
        }

        $controller = $this->getController($page_code);
        if ($controller) {
            $controller->run();
            exit;
        }

        $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_404);// log
        $this->forword404();
    }

    public function redirect($to) {

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$to);
        exit;
    }

    public function forword404() {

        header('HTTP/1.1 404 Not Found');
        include(APPLICATION_PATH."/{$this->ua->device()}/404notFound/index.html");
        exit;
    }

    public function systemError() {

        header('HTTP/1.1 404 Not Found');

        include(APPLICATION_PATH."/{$this->ua->device()}/search/View/system_error.php");
        exit;
    }

    public function find() {

        // ─────────────────────────────────
        // 物件検索
        // ─────────────────────────────────

        switch ($this->request->parse['path']) {
            case '/shumoku.html':
                return SearchPages::SHUMOKU;
            case '/rent.html':
                return SearchPages::RENT;
            case '/purchase.html':
                return SearchPages::PURCHASE;
            default:
                break;
        }

        // search freeword hppage
        if (in_array($this->request->directory(1), SearchShumoku::dirname_freeword())) {
            if ($this->request->directory(2) === 'result') {
                return SearchPages::RESULT_FREEWORD;
            }
            if ($this->request->directory(2) === 'condition') {
                return SearchPages::MOBILE_SELECT_CONDITION;
            }
        }

        // {$物件種目}
        if (in_array($this->request->directory(1), SearchShumoku::dirname_all())) {

            // {$物件種目}/
            if ($this->request->directory(2) === null && $this->request->count_dir() === 1) {
                return SearchPages::SELECT_PREFECTURE;
            }

            // {$物件種目}/detail-****
            if (preg_match('/^detail-/', $this->request->directory(2))) {

                // {$物件種目}/detail-****/
                if ($this->request->directory(3) === null && $this->request->count_dir() === 2) {
                    return SearchPages::DETAIL;
                }

                // {$物件種目}/detail-****/map.html
                if ($this->request->directory(3) === 'map.html' && $this->request->count_dir() === 3) {
                    return SearchPages::DETAIL_MAP;
                }

                // {$物件種目}/detail-****/panorama.html
                if ($this->request->directory(3) === 'panorama.html' && $this->request->count_dir() === 3) {
                    return SearchPages::DETAIL_PANORAMA;
                }

                // {$物件種目}/detail-****/town.html
                if ($this->request->directory(3) === 'townstats.html' && $this->request->count_dir() === 3) {
                    return SearchPages::DETAIL_TOWN;
                }

                // {$物件種目}/detail-****/accesscount/{$カウントするコンテンツ:ex)panorama}
                if ($this->request->directory(3) === 'accesscount' && $this->request->count_dir() === 4) {
                    return SearchPages::API_ACCESSCOUNT;
                }

                return null;
            }

            // {$物件種目}/{$都道府県}
            if (in_array($this->request->directory(2), SearchTodofuken::dirname_all())) {

                // {$物件種目}/{$都道府県}/
                if ($this->request->directory(3) === null && $this->request->count_dir() === 2) {
                    return SearchPages::SELECT_CITY;
                }

                // {$物件種目}/{$都道府県}/line.html
                if ($this->request->directory(3) === 'line.html' && $this->request->count_dir() === 3) {
                    return SearchPages::SELECT_RAILWAY;
                }

                // {$物件種目}/{$都道府県}/map.html
                if ($this->request->directory(3) === 'map.html' && $this->request->count_dir() === 3) {
                    return SearchPages::SELECT_MAP_CITY;
                }

                // {$物件種目}/{$都道府県}/{$市区ID-city}/
                if (preg_match('/-city\z/', $this->request->directory(3)) && $this->request->count_dir() === 3) {
                    return SearchPages::SELECT_CHOSON;
                }

                // {$物件種目}/{$都道府県}/city/search/
                if ($this->request->directory(3) === 'city' && $this->request->directory(4) === 'search' && $this->request->count_dir() === 4) {
                    return SearchPages::SELECT_CHOSON_MULTI_CITY;
                }

                // {$物件種目}/{$都道府県}/{$沿線ID-line}/
                if (preg_match('/-line\z/', $this->request->directory(3)) && $this->request->count_dir() === 3) {
                    return SearchPages::SELECT_STATION;
                }

                // {$物件種目}/{$都道府県}/line/search/
                if ($this->request->directory(3) === 'line' && $this->request->directory(4) === 'search' && $this->request->count_dir() === 4) {
                    return SearchPages::SELECT_STATION_MULTI_RAYWAY;
                }

                // {$物件種目}/{$都道府県}/condition/
                if ($this->request->directory(3) === 'condition' && $this->request->count_dir() === 3 && $this->ua->requestDevice() === 'sp') {
                    return SearchPages::MOBILE_SELECT_CONDITION;
                }

                // {$物件種目}/{$都道府県}/result
                if ($this->request->directory(3) === 'result') {

                    // {$物件種目}/{$都道府県}/result/
                    if ($this->request->directory(4) === null && $this->request->count_dir() === 3) {

                        // 都道府県
                        if ($this->request->method === Request::GET || $this->request->getPost(SearchPages::FROM_PREFECTURE)) {
                            return SearchPages::RESULT_PREFECTURE;
                        }

                        // エリア
                        if ($this->request->getPost(SearchPages::FROM_CITY_SELECT)) {
                            return SearchPages::RESULT_AREA_MULTI;
                        }

                        // 町村
                        if ($this->request->getPost(SearchPages::FROM_CHOSON_SELECT)) {
                            return SearchPages::RESULT_CHOSON_MULTI;
                        }

                        // 沿線・駅
                        if ($this->request->getPost(SearchPages::FROM_STATION_SELECT)) {
                            return SearchPages::RESULT_STATION_MULTI;
                        }

                        if ($this->ua->requestDevice() === 'pc') {

                            return null;
                        }

                        // こだわり条件（SP）
                        if ($this->request->getPost(SearchPages::FROM_CONDITION)) {

                            return SearchPages::MOBILE_RESULT_FROM_CONDITION;
                        }

                        // 条件変更
                        if ($this->request->getPost(SearchPages::FROM_RESULT)) {

                            return SearchPages::MOBILE_RESULT_CHANGE_CONDITION;
                        }

                        return null;
                    }

                    // {$物件種目}/{$都道府県}/result/{$市区ID｜path}-city.html
                    if (preg_match('/-city.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_AREA;
                    }

                    // {$物件種目}/{$都道府県}/result/{$政令指定都市ID｜path}-mcity.html
                    if (preg_match('/-mcity.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_MCITY;
                    }

                    // {$物件種目}/{$都道府県}/result/{$市区ID｜path}-{$町村ID}.html
                    if (preg_match('/-[0-9]+\.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_CHOSON;
                    }

                    // {$物件種目}/{$都道府県}/result/{$駅ID｜path}-eki.html
                    if (preg_match('/-eki.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_STATION;
                    }

                    // {$物件種目}/{$都道府県}/result/{$沿線ID｜path}-line.html
                    if (preg_match('/-line.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_RAILWAY;
                    }
                    // {$物件種目}/{$都道府県}/result/{$沿線ID｜path}-map.html
                    if (preg_match('/-map.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::RESULT_MAP;
                    }

                    return null;
                }
                return null;
            }
            // {$物件種目}/here/result/map.html
            if (($this->request->directory(2)=='here') &&
                ($this->request->directory(3) === 'result') &&
                (preg_match('/-map.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4)){
                return SearchPages::RESULT_MAP;
            }
            return null;
        }

        // personal
        if ($this->request->directory(1) === 'personal') {

            // personal/favorite
            if ($this->request->directory(2) === 'favorite' && $this->request->count_dir() === 2) {
                return SearchPages::FAVORITE;
            }

            // personal/history
            if ($this->request->directory(2) === 'history' && $this->request->count_dir() === 2) {
                return SearchPages::HISTORY;
            }

            return null;
        }

        // howtoinfo
        if ($this->request->directory(1) === 'howtoinfo' && $this->request->count_dir() === 1) {

            return SearchPages::HOWTOINFO;
        }

        // ─────────────────────────────────
        // 特集
        // ─────────────────────────────────

        // sp-****/
        if (preg_match('/^sp-/', $this->request->directory(1))) {

            // {$特集}/
            if ($this->request->directory(2) === null && $this->request->count_dir() === 1) {
                return SearchPages::SP_SELECT_PREFECTURE;
            }

            // // {$特集}/detail-****
            // if (preg_match('/^detail-/', $this->request->directory(2))) {
            //     // {$物件種目}/detail-****/
            //     if ($this->request->directory(3) === null && $this->request->count_dir() === 2) {
            //         return SearchPages::SP_DETAIL;
            //     }
            //     // {$物件種目}/detail-****/map.html
            //     if ($this->request->directory(3) === 'map.html' && $this->request->count_dir() === 3) {
            //         return SearchPages::SP_DETAIL_MAP;
            //     }
            //     return null;
            // }

            // 特集の直接一覧用-----------------------------
            // {$特集}/condition/
            if ($this->request->directory(2) === 'condition' && $this->request->count_dir() === 2 && $this->ua->requestDevice() === 'sp') {
                return SearchPages::MOBILE_SP_SELECT_CONDITION;
            }
            if ($this->request->directory(2) === 'result' && $this->request->count_dir() === 2 && $this->ua->requestDevice() === 'sp') {
                return SearchPages::MOBILE_SP_RESULT_FROM_CONDITION;
            }
            // 特集の直接一覧用-----------------------------

            // {$特集}/{$都道府県}
            if (in_array($this->request->directory(2), SearchTodofuken::dirname_all())) {

                // {$特集}/{$都道府県}/
                if ($this->request->directory(3) === null && $this->request->count_dir() === 2) {
                    return SearchPages::SP_SELECT_CITY;
                }

                // {$特集}/{$都道府県}/line.html
                if ($this->request->directory(3) === 'line.html' && $this->request->count_dir() === 3) {
                    return SearchPages::SP_SELECT_RAILWAY;
                }

                // {$物件種目}/{$都道府県}/map.html
                if ($this->request->directory(3) === 'map.html' && $this->request->count_dir() === 3) {
                    return SearchPages::SP_SELECT_MAP_CITY;
                }

                // {$特集}/{$都道府県}/{$沿線ID-line}/
                if (preg_match('/-line\z/', $this->request->directory(3)) && $this->request->count_dir() === 3) {
                    return SearchPages::SP_SELECT_STATION;
                }

                // {$特集}/{$都道府県}/condition/
                if ($this->request->directory(3) === 'condition' && $this->request->count_dir() === 3 && $this->ua->requestDevice() === 'sp') {
                    return SearchPages::MOBILE_SP_SELECT_CONDITION;
                }

                // {$特集}/{$都道府県}/{$市区ID-city}/
                if (preg_match('/-city\z/', $this->request->directory(3)) && $this->request->count_dir() === 3) {
                    return SearchPages::SP_SELECT_CHOSON;
                }

                // {$特集}/{$都道府県}/city/search/
                if ($this->request->directory(3) === 'city' && $this->request->directory(4) === 'search' && $this->request->count_dir() === 4) {
                    return SearchPages::SP_SELECT_CHOSON_MULTI_CITY;
                }

                // {$特集}/{$都道府県}/line/search/
                if ($this->request->directory(3) === 'line' && $this->request->directory(4) === 'search' && $this->request->count_dir() === 4) {
                    return SearchPages::SP_SELECT_STATION_MULTI_RAYWAY;
                }

                // {$特集}/{$都道府県}/condition/
                if ($this->request->directory(3) === 'condition' && $this->request->count_dir() === 3 && $this->ua->requestDevice() === 'sp') {
                    return SearchPages::MOBILE_SP_SELECT_CONDITION;
                }

                // {$特集}/{$都道府県}/result
                if ($this->request->directory(3) === 'result') {

                    // {$特集}/{$都道府県}/result/
                    if ($this->request->directory(4) === null && $this->request->count_dir() === 3) {

                        // 都道府県
                        if ($this->request->method === Request::GET || $this->request->getPost(SearchPages::FROM_PREFECTURE)) {
                            return SearchPages::SP_RESULT_PREFECTURE;
                        }

                        // エリア
                        if ($this->request->getPost(SearchPages::FROM_CITY_SELECT)) {
                            return SearchPages::SP_RESULT_AREA_MULTI;
                        }

                        // 町村
                        if ($this->request->getPost(SearchPages::FROM_CHOSON_SELECT)) {
                            return SearchPages::SP_RESULT_CHOSON_MULTI;
                        }

                        // 沿線・駅
                        if ($this->request->getPost(SearchPages::FROM_STATION_SELECT)) {
                            return SearchPages::SP_RESULT_STATION_MULTI;
                        }

                        // 以下、SPのみ
                        if ($this->ua->requestDevice() === 'pc') {
                            return null;
                        }

                        // こだわり条件（SP）
                        if ($this->request->getPost(SearchPages::FROM_CONDITION)) {

                            return SearchPages::MOBILE_SP_RESULT_FROM_CONDITION;
                        }

                        // 条件変更
                        if ($this->request->getPost(SearchPages::FROM_RESULT)) {

                            return SearchPages::MOBILE_SP_RESULT_CHANGE_CONDITION;
                        }

                        return null;
                    }

                    // {$特集}/{$都道府県}/result/{$市区ID｜path}-city.html
                    if (preg_match('/-city.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_AREA;
                    }

                    // {$特集}/{$都道府県}/result/{$政令指定都市ID｜path}-mcity.html
                    if (preg_match('/-mcity.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_MCITY;
                    }

                    // {$物件種目}/{$都道府県}/result/{$市区ID｜path}-{$町村ID}.html
                    if (preg_match('/-[0-9]+\.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_CHOSON;
                    }

                    // {$特集}/{$都道府県}/result/{$駅ID｜path}-eki.html
                    if (preg_match('/-eki.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_STATION;
                    }

                    // {$特集}/{$都道府県}/result/{$沿線ID｜path}-line.html
                    if (preg_match('/-line.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_RAILWAY;
                    }
                    // {$物件種目}/{$都道府県}/result/{$沿線ID｜path}-map.html
                    if (preg_match('/-map.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4) {
                        return SearchPages::SP_RESULT_MAP;
                    }
                    return null;
                }
                return null;
            }
            // {$物件種目}/here/result/map.html
            if (($this->request->directory(2)=='here') &&
                ($this->request->directory(3) === 'result') &&
                (preg_match('/-map.html\z/', $this->request->directory(4)) && $this->request->count_dir() === 4)){
                return SearchPages::SP_RESULT_MAP;
            }
            return null;
        }

        // ─────────────────────────────────
        // お問い合わせフォーム
        // ─────────────────────────────────
        if ($this->request->directory(1) === 'inquiry') {

            if ($this->request->directory(2) === 'kasi-kyojuu') {

                if ($this->request->directory(3) === 'edit' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_KYOJUU_EDIT;
                }

                if ($this->request->directory(3) === 'confirm' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_KYOJUU_CONFIRM;
                }

                if ($this->request->directory(3) === 'complete' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_KYOJUU_COMPLETE;
                }

                if ($this->request->directory(3) === 'validate' && $this->request->count_dir() === 3) {
                    return SearchPages::CONTACT_VALIDATE;
                }

                //                if ($this->request->directory(3) === 'error' && $this->request->count_dir() === 3) {
                //                    return SearchPages::KASI_KYOJUU_ERROR;
                //                }

                return null;
            }

            if ($this->request->directory(2) === 'kasi-jigyou') {

                if ($this->request->directory(3) === 'edit' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_JIGYOU_EDIT;
                }

                if ($this->request->directory(3) === 'confirm' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_JIGYOU_CONFIRM;
                }

                if ($this->request->directory(3) === 'complete' && $this->request->count_dir() === 3) {
                    return SearchPages::KASI_JIGYOU_COMPLETE;
                }

                if ($this->request->directory(3) === 'validate' && $this->request->count_dir() === 3) {
                    return SearchPages::CONTACT_VALIDATE;
                }

                //                if ($this->request->directory(3) === 'error' && $this->request->count_dir() === 3) {
                //                    return SearchPages::KASI_JIGYOU_ERROR;
                //                }

                return null;
            }

            if ($this->request->directory(2) === 'uri-kyojuu') {

                if ($this->request->directory(3) === 'edit' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_KYOJUU_EDIT;
                }

                if ($this->request->directory(3) === 'confirm' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_KYOJUU_CONFIRM;
                }

                if ($this->request->directory(3) === 'complete' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_KYOJUU_COMPLETE;
                }

                if ($this->request->directory(3) === 'validate' && $this->request->count_dir() === 3) {
                    return SearchPages::CONTACT_VALIDATE;
                }

                //                if ($this->request->directory(3) === 'error' && $this->request->count_dir() === 3) {
                //                    return SearchPages::URI_KYOJUU_ERROR;
                //                }

                return null;
            }

            if ($this->request->directory(2) === 'uri-jigyou') {

                if ($this->request->directory(3) === 'edit' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_JIGYOU_EDIT;
                }

                if ($this->request->directory(3) === 'confirm' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_JIGYOU_CONFIRM;
                }

                if ($this->request->directory(3) === 'complete' && $this->request->count_dir() === 3) {
                    return SearchPages::URI_JIGYOU_COMPLETE;
                }

                if ($this->request->directory(3) === 'validate' && $this->request->count_dir() === 3) {
                    return SearchPages::CONTACT_VALIDATE;
                }

                //                if ($this->request->directory(3) === 'error' && $this->request->count_dir() === 3) {
                //                    return SearchPages::URI_JIGYOU_ERROR;
                //                }

                return null;
            }

            if ($this->request->directory(2) === 'bukken' && $this->request->directory(3) === 'error' && $this->request->count_dir() === 3) {

                return SearchPages::CONTACT_ERROR;
            }

            return null;
        }

        // ─────────────────────────────────
        // api
        // ─────────────────────────────────

        if ($this->request->directory(1) === 'api') {


            // 特集：物件検索API
            if (preg_match('/^sp-/', $this->request->directory(2)) && $this->request->count_dir() === 3) {
                if ( $this->request->directory(3) == 'mapcenter' ||
                    $this->request->directory(3) == 'updatemap'  ||
                    $this->request->directory(3) == 'mapsidelist'||
                    $this->request->directory(3) == 'maplist'||
                    $this->request->directory(3) == 'mapkkauth'  ){
                    return SearchPages::SP_API_MAP;
                }
            }

            if ( $this->request->directory(2) == 'mapcenter'  ||
                 $this->request->directory(2) == 'updatemap'  ||
                 $this->request->directory(2) == 'mapsidelist'||
                 $this->request->directory(2) == 'maplist'||
                 $this->request->directory(2) == 'mapkkauth' ){
                return SearchPages::API_MAP;
            }

            // 物件検索API
            if ($this->request->count_dir() === 2) {
                return SearchPages::API;
            }

            // 特集：物件検索API
            if (preg_match('/^sp-/', $this->request->directory(2)) && $this->request->count_dir() === 3) {
                return SearchPages::SP_API;
            }
        }

        return null;
    }

    private function getController($page_code) {

        $controller = 'Controller';
        if (SearchPages::category_by_code($page_code) === SearchPages::CATEGORY_API) {
            $controller = 'Api'.$controller;
        }
        elseif (SearchPages::category_by_code($page_code) === SearchPages::CATEGORY_API_MAP) {
            $controller = 'ApiMap'.$controller;
        }

        $dirname = 'search';
        if (SearchPages::category_by_code($page_code) === SearchPages::CATEGORY_SPECIAL) {
            $dirname = $this->request->directory(1);

            /**
             * 以下の条件時は、_sp-common 以下のファイルを使用
             * ---------------------------------------------------------
             * 1. 特集用のフォルダが存在する
             * 2. _sp-commonも存在する
             */
            if(is_dir(APPLICATION_PATH."/{$this->ua->requestDevice()}/{$dirname}")
            && is_dir(APPLICATION_PATH."/{$this->ua->requestDevice()}/_sp-common")) {
                $dirname = "_sp-common";
            }
        }
        elseif ($page_code === SearchPages::SP_API || $page_code === SearchPages::SP_API_MAP) {
            $dirname = $this->request->directory(2);

            /**
             * 以下の条件時は、_sp-common 以下のファイルを使用
             * ---------------------------------------------------------
             * 1. 特集用のフォルダが存在する
             * 2. _sp-commonも存在する
             */
            if(is_dir(APPLICATION_PATH."/{$this->ua->requestDevice()}/{$dirname}")
            && is_dir(APPLICATION_PATH."/{$this->ua->requestDevice()}/_sp-common")) { 
                $dirname = "_sp-common";
            }
        }

        $path = APPLICATION_PATH."/{$this->ua->requestDevice()}/{$dirname}/{$controller}.php";

        if (file_exists($path)) {

            require_once($path);

            $config = [
                'page_code' => $page_code,
            ];

            return new $controller($this->viewHelper, $config);
        }

        return null;
    }
}













