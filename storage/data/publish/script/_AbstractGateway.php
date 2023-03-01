<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/Session.php');
require_once(APPLICATION_PATH.'/../script/Search.php');
require_once(APPLICATION_PATH.'/../script/SearchPages.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
require_once(APPLICATION_PATH.'/../script/SearchTodofuken.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/Naming.php');
require_once(APPLICATION_PATH.'/../script/SeoTags.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

abstract class _AbstractGateway {

    // argument
    protected $config;

    // instance
    protected $apiGateway;
    protected $apiConfig;
    protected $request;
    protected $ua;
    protected $view;
    protected $session;
    protected $logger;

    // define
    protected $apiHost;
    protected $_page_api_url;
    protected $_config;
    protected $_namespace;
    protected $_s_type;
    protected $_seo_tags = [];

    public function __construct($config) {

        $this->config = $config;

        $this->apiGateway = new ApiGateway($config);
        $this->apiConfig  = new ApiConfing();
        $this->request    = new Request();
        $this->ua         = new UserAgent();
        $this->view       = new stdClass();
        $this->session    = new Session($this->_namespace);
        $this->logger     = new Log();

        $this->apiHost         = 'http://'.$this->apiConfig->get('domain');
        $this->view->seoTags   = $this->_seo_tags;
        $this->view->request   = $this->request;
        $this->view->apiConfig = $this->apiConfig;

        $this->view->_config = $this->getConfigByJson();
    }

    /**
     * @return bool|stdClass
     */
    public function run() {

        return $this->fetch($this->apiUrl());
    }

    /**
     * 連携サーバーにアクセス用のURL生成
     *
     * @return string
     */
    protected function apiUrl() {

        $url = $this->apiHost.$this->_page_api_url;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        // shumoku or special
        $key = null;
        if (in_array($this->request->directory(1), SearchShumoku::dirname_all())) {
            $key = ApiGateway::KEY_SHUMOKU; // shumoku
        }
        elseif (preg_match('/^sp-/', $this->request->directory(1))) {
            $key = ApiGateway::KEY_SPECIAL_PATH; // special
        }

        if ($key) {
            $url .= $this->addParam($key, $this->request->directory(1));
        }

        // ipアドレス
        $url .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);

        return $url;
    }

    /**
     * 連携サーバーにアクセス用のURL生成（物件検索）
     *
     * @param array $removeList
     * @return string
     */
    protected function apiSearchUrl(array $removeList = []) {

        $url = $this->apiUrl();

        // prefecture
        if (!in_array(ApiGateway::KEY_PREFECUTURE, $removeList)) {

            $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));
        }
        // s_type
        if (!in_array(ApiGateway::KEY_S_TYPE, $removeList)) {
            $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->_s_type);
        }
        // sort
        if (!in_array(ApiGateway::KEY_SORT, $removeList)) {
            if ($sort = $this->request->getSort($this->request->directory(1))) {
                $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
            };
        }
        // count
        if (!in_array(ApiGateway::KEY_PER_PAGE, $removeList)) {
            if ($total = $this->request->getTotal()) {
                $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $total);
            };
        }
        // page
        if (!in_array(ApiGateway::KEY_PAGE, $removeList)) {
            $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPage());
        }
        // pic
        if (!in_array(ApiGateway::KEY_PIC, $removeList)) {
            $url .= $this->addParam(ApiGateway::KEY_PIC, $this->request->getPost('pic') !== null ? $this->request->getPost('pic') : 1);
        }

        return $url;
    }

    /**
     * URLパラメータ追加
     *
     * @param      $key
     * @param      $value
     * @param bool $first
     * @return string
     */
    protected function addParam($key, $value, $first = false) {

        $and = '&';
        if ($first) {
            $and = '?';
        }

        if (is_array($value)) {

            $value = implode(',', $value);
        }

        return $and.(string)$key.'='.urlencode((string)$value);
    }

    /**
     * 連携サーバーにアクセス（get）
     *
     * @param $url
     * @return bool|stdClass
     */
    protected function fetch($url) {

        if ($this->apiConfig->get('dev')) {
            $this->devUrl = $url;
        }

        return $this->afterFetch($this->apiGateway->get($url));
    }

    /**
     * 連携サーバーにアクセス（post）
     *
     * @param       $url
     * @param array $data
     * @return bool|stdClass
     */
    protected function post($url, array $data) {

        if ($this->apiConfig->get('dev')) {
            $this->devUrl = $url;
        }

        return $this->afterFetch($this->apiGateway->post($url, $data));
    }

    /**
     * 連携サーバー通信後の処理
     * @param $apiResponse
     * @return bool|stdClass
     */
    private function afterFetch($responseJson) {

        $responseObject = json_decode($responseJson);

        // 通信成功
        if ($responseObject instanceof stdClass) {

            // エラーコード
            if (isset($responseObject->status_code)) {

                // エラーコードありは404
                return false;
            }

            // エラーあり
            if (!$responseObject->success) {

                // システムエラー
                $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log
                $this->showSystemError();
            }

            // リダイレクトあり
            if (isset($responseObject->redirectUrl)) {

                $to = $responseObject->redirectUrl;
                $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}"); //log
                $this->redirect($to);
            }

            // 通常のレスポンス
            $this->view->api = $responseObject;

            // 特集用の物件種目ローマ字をconfigにセット
            $this->view->_config = $this->addShumokuCtForSpecial($this->view);

            if ($this->apiConfig->get('dev')) {
                $this->view->devUrl = $this->devUrl;
            }
            $this->view->request = $this->request;

            return $this->view;
        }

        // 通信失敗
        $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log
        $this->showSystemError(); // システムエラー
        exit;
    }

    /**
     * 特集用の物件種目文字列をセット
     *
     * @param $view
     * @return string
     */
    private function addShumokuCtForSpecial($view) {

        if (!isset($view->api->info->type)) {
            return $view->_config;
        }

        $configObject          = json_decode($view->_config);
        $configObject->shumoku = $view->api->info->type;
        return json_encode($configObject);
    }

    /**
     * リダイレクト
     * @param $to
     */
    protected function redirect($to) {

        header('HTTP/1.1 301 Moved Permanently');
        $location = $this->request->protcol.'://'.$this->request->domain.$to;
        header("Location: {$location}");
        exit;
    }

    /**
     * システムエラー画面表示
     */
    private function showSystemError() {

        $view = $this->view;
        if ($this->apiConfig->get('dev')) {

            $view->devUrl = $this->devUrl;
        }

        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=UTF-8');
        include(APPLICATION_PATH."/{$this->ua->requestDevice()}/search/View/system_error.php");
        exit;
    }

    public $devUrl;

    /**
     *
     * セッションの検索条件を削除
     *
     */
    protected function resetSessionCondition($boolean = false) {

        // get access
        if ($boolean || $this->request->method === Request::GET) {

            // reset session "condition"
            session_start();
            (new Session('condition'))->destroy();
        }
    }

    /**
     * 物件が0件の場合にnoindexを追加
     *
     * @param $view
     * @return mixed
     */
    protected function setNoindexIfNoEstate($view) {

        if (isset($view->api->info->total_count) && $view->api->info->total_count < 1) {
            $this->_seo_tags[] = SeoTags::NOINDEX;
            $view->seoTags     = $this->_seo_tags;
        }
        return $view;
    }

    protected function getConfigByJson(array $options = []) {

        $params = [];

        $params['page_code'] = $this->config['page_code'];
        $params['page_name'] = pathinfo(SearchPages::filename_by_code($this->config['page_code']))['filename'];

        if ($this->_s_type) {
            $params['s_type'] = $this->_s_type;

            // 町名検索の場合、町村コードを付与する
            switch ($this->_s_type) {
                case SearchPages::S_TYPE_RESULT_CHOSON:
                case SearchPages::S_TYPE_RESULT_CHOSON_FORM:
                    if ($postChosonCts = $this->request->getPost('choson')) {
                        $params['chosons'] = explode(',', $postChosonCts);
                    } elseif ($chosonCt = $this->request->getChosonFromUrl(4)) {
                        $params['chosons'] = [$chosonCt];
                    }
                    break;
            }
        }

        foreach ($options as $i => $v) {
            $params[$i] = $v;
        }
        return json_encode($params);
    }

    protected function getSeoTags() {

        return $this->_seo_tags;
    }

    protected function getPrivacyFilename() {
        $pages =  unserialize(file_get_contents(APPLICATION_PATH.'/../setting/pages.txt'));
        $privacyPage = null;
        foreach($pages as $page){
            // プライバシーポリシー
            if($page['page_type_code'] == 16){
                $privacyPage = $page;
                break;
            }
        }
        if( !$privacyPage ){
            return null;
        }
        return $privacyPage['filename'];
    }
}