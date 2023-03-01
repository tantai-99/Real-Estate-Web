<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/Search.php');
require_once(APPLICATION_PATH.'/../script/Naming.php');
require_once(APPLICATION_PATH.'/../script/Session.php');
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');
require_once(APPLICATION_PATH.'/../script/Path.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

class AbstractController {

    private   $viewHelper;
    private   $config;
    private   $route;
    private   $request;
    private   $filename;
    protected $device;
    protected $path;
    private   $logger;

    public function __construct(ViewHelper $viewHelper, array $config) {

        $this->viewHelper = $viewHelper;
        $this->config     = $config;

        $this->route   = new Route($this->viewHelper);
        $this->request = new Request();
        $this->logger  = new Log();

        $this->filename = SearchPages::filename_by_code($this->config['page_code']);
    }

    public function run() {

        $gatewayPath = $this->path."/Gateway/{$this->filename}";
        $viewPath    = $this->path."/View/{$this->filename}";

        if (!file_exists($gatewayPath) || !file_exists($viewPath)) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_404); //log
            $this->route->forword404();
        }

        require_once($gatewayPath);
        $class = Naming::camelize(pathinfo($gatewayPath)['filename']);

        $view = (new $class($this->config))->run();

        // has error
        if (!$view) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_404); //log
            $this->route->forword404();
        }

        // change template
        if ($view->template !== null) {
            $viewPath = $this->path."/View/{$view->template}";
        }

        ob_start();
        include_once($viewPath);
        $html = ob_get_contents();
        ob_end_clean();

        // 各テーマごとのHTML差分を反映
        $theme = ucfirst($this->getTheme());

        //デザインパターン追加（カラー自由版）
        if(strpos($theme, '_custom_color') !== false) {
            $theme = str_replace('_custom_color', 'CustomColor', $theme);
        }
        //デザインパターン追加（カラー自由版）
        
        $class = "Theme_{$theme}";
        require_once(APPLICATION_PATH."/../script/Theme/{$this->device}/{$theme}.php");
        $hp = unserialize($this->viewHelper->getContentSettingFile('hp.txt'));
        $html = (new $class($hp, $html, $this->path, $this->config))->run();


        /*
        // 相対パスを絶対パスに変換
        $path = new Path();
        $html = $path->setAbsolutePath($html);
        */

        $this->logger->addAccessLog(Log::TYPE_RESPONSE); //log

        // header
        header('Access-control-allow-origin:*');
        header('Content-Type: text/html; charset=UTF-8');
        if (isset($view->customHeader) && count($view->customHeader) > 0){
            foreach ($view->customHeader as $header) {
                header($header);
            }
        }

        echo $html;
    }

    /**
     * テーマ名を取得
     *
     * @return string
     */
    private function getTheme() {

        $html = file_get_contents(APPLICATION_PATH."/{$this->device}/search/View/header/stylesheet.blade.php");
        $doc  = phpQuery::newDocument($html);
        return $doc['.css-theme-color']->attr('data-theme');
    }
}