<?php

require_once(APPLICATION_PATH.'/../script/Route.php');
require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/ViewHelper.php');
require_once(APPLICATION_PATH.'/../script/ApiConversion.php');
require_once(APPLICATION_PATH.'/../script/Auth.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

class Front_Controller {

    public  $viewHelper;
    private $route;
    private $request;
    private $ua;
    private $auth;
    private $logger;
    
    public function __construct() {

        $this->request = new Request();
        $this->ua      = new UserAgent();
        $this->logger  = new Log();
        if ($this->logger->canUse()) {
            Log::$uid = uniqid();
        }

        $config              = new stdClass;
        $config->viewPath    = APPLICATION_PATH;
        $config->scriptPath  = APPLICATION_PATH.'/../script';
        $config->settingPath = APPLICATION_PATH.'/../setting';
        $config->uri         = $this->request->remove_last_slash($this->request->parse['path']);
        $config->device      = '/'.$this->ua->requestDevice();
        $config->siteType    = $this->siteType();

        $this->viewHelper = new ViewHelper($config);
        $this->route      = new Route($this->viewHelper);
        $this->auth       = new Auth($this->viewHelper);

        $this->apiConversion = new ApiConversion($this->viewHelper);
        
        $this->isDemoSite	= file_exists( APPLICATION_PATH . '/../../../isDemoSite' )	;
        $this->isDemoServer	= file_exists( APPLICATION_PATH . '/../../../../apache/api.apache.<<<--sales demo domain-->>>/addUser.php' )	;
    }

    public function run() {

        // log
        $this->logger->addAccessLog(Log::TYPE_REQUEST);// log

        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1);
        ini_set('session.gc_maxlifetime', 3600);

        // ITP2.3対応 - Start -
        $ckeys = ['favorite', 'favorite_config', 'histories']; // 対象cookieリスト
        $uidkey = 'user_id';
        $expire = time() + (60 * 60 * 24 * 30);                // 期限は30日
        foreach($ckeys as $key) {
            $ckey1 = $key;
            $ckey2 = sprintf("%s_save", $key);

            if(!isset($_COOKIE[$ckey1]) && !isset($_COOKIE[$ckey2])) {  // No.1
                setcookie($ckey1, '{}', $expire, "/", null, false, false);
                setcookie($ckey2, '{}', $expire, "/", null, true, true);
            } else if(!isset($_COOKIE[$ckey1])) {                       // No.2
                setcookie($ckey1, $_COOKIE[$ckey2], $expire, "/", null, false, false);
                setcookie($ckey2, $_COOKIE[$ckey2], $expire, "/", null, true, true);
            } else if(!isset($_COOKIE[$ckey2])) {                       // No.3
                setcookie($ckey1, $_COOKIE[$ckey1], $expire, "/", null, false, false);
                setcookie($ckey2, $_COOKIE[$ckey1], $expire, "/", null, true, true);
            } else {                                                    // No.4
                setcookie($ckey1, $_COOKIE[$ckey1], $expire, "/", null, false, false);
                setcookie($ckey2, $_COOKIE[$ckey1], $expire, "/", null, true, true);
            }
        }
        // ITP2.3対応 - End -

        // 本番サイトからのみユーザーIDをcookieに追加
        if ($this->siteType() === 'public') {
            setcookie($uidkey, isset($_COOKIE[$uidkey]) ? $_COOKIE[$uidkey] : md5(uniqid(mt_rand(), true)), $expire, "/", null, true, true);
        }

        if(strpos($this->request->parse['path'], '/images/customize-image-auto-resize/') !== false){
            $listpath=explode('/',$this->request->parse['path']);
            $param['src'] = array_pop($listpath);
            $param['height'] = array_pop($listpath);
            $param['width'] = array_pop($listpath);
            $this->viewHelper->outImage($param);
        }

        // コンバージョン系
        if($this->apiConversion->isConversionApi()){
            $this->apiConversion->conversion();
            exit;
        }

        // public
        if ($this->siteType() === 'public' && !$this->request->has_www()) {
            $to = $this->request->protcol.'://www.'.$this->request->domain.$this->request->request_uri;
            $this->route->redirect($to);
        }

        // test, substitute
        if (($this->siteType() === 'test' || $this->siteType() === 'substitute') && $this->request->has_www()) {
            $to = $this->request->protcol.'://'.str_replace('www.', '', $this->request->domain).$this->request->request_uri;
            $this->route->redirect($to);
        }

        // http or https
        if ($this->request->protcol === 'http' && 
            ( $this->isDemoServer == false )   &&
            ($this->viewHelper->protocol!=$this->request->protcol)
        ) {
            $to = 'https://'.$this->request->domain.$this->request->request_uri;
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->route->redirect($to);
        }

        // 物件検索・特集のルーティング
        $this->route->run();

        // remove index.html
        if ($this->request->parse['basename'] === 'index.html') {
            $to = $this->request->remove_filename($this->request->current);
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->route->redirect($to);
        }

        // remove extension
        if (mb_strlen($this->request->parse['extension']) > 0 && $this->request->request_uri !== '/' && !$this->hasExtension()) {
            $to = $this->request->remove_extension($this->request->current);
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->route->redirect($to);
        }

        // must have last slash
        if (!$this->request->has_last_slash($this->request->current) && !$this->request->has_url_params($this->request->current)) {
            $to = $this->request->set_last_slash($this->request->current);
            $this->logger->addAccessLog(Log::TYPE_REDIRECT, "to: {$to}");// log
            $this->route->redirect($to);
        }


        // path to html
        $path = APPLICATION_PATH.'/'.$this->ua->requestDevice().$this->request->remove_last_slash($this->request->parse['path']).'/index.html';

        // check member only
        $path = $this->auth->check($path);

        // 404
        if (!file_exists($path)) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_404);// log
            $this->route->forword404();
            exit;
        }

        // templateにview helper渡す 1Phの名残 美しくない
        $viewHelper = $this->viewHelper;

        $this->logger->addAccessLog(Log::TYPE_RESPONSE);// log

        // show
        header('Access-control-allow-origin:*');
        header('Cache-Control: max-age='.(60 * 60 * 24 * 100));
        include_once($path);
        exit;
    }

    private function isInputPage() {

        if (strpos($this->request->request_uri, '/edit/') !== false) {
            return true;
        }
        return false;
    }

    private function isThanksPage() {

        if (strpos($this->request->request_uri, '/complete/') !== false) {
            return true;
        }
        return false;
    }

    private function siteType() {

        $domain = $this->request->domain;

        if ($this->request->has_www()) {
            $domain = str_replace('www.', '', $domain);
        }

        if (strncmp($domain, 'test.', 4) === 0) {
            return 'test';
        }

        if (strncmp($domain, 'substitute.', 11) === 0) {
            return 'substitute';
        }
        return 'public';
    }

    private function hasExtension() {
        if ($this->request->parse['query_array'] && mb_strlen($this->request->parse['extension'])) {
            return true;
        }
        return false;
    }
}

?>
