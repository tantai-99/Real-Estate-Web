<?php

require_once(APPLICATION_PATH.'/../script/Route.php');

/**
 * 会員専用ページの認証チェック
 *
 * Class Auth
 */
class Auth {

    private $viewHelper;
    private $list;
    private $route;

    public function __construct($viewHelper) {

        $this->viewHelper = $viewHelper;
        $this->list       = $this->viewHelper->parseIniFileInSetting('member_only.ini', true);
        $this->route      = new Route($viewHelper);
    }

    /**
     * url取得
     *
     * @param $html_path
     *
     * @return string
     */
    public function check($path) {

        if (count($this->list) < 1) {
            return $path;
        }

        foreach ($this->list as $page) {

            $uri = $this->viewHelper->uri($page['path']);

            if (!strstr($this->viewHelper->_view->uri.'/', "/{$uri}/") || is_null($uri)) {
                continue;
            }

            $isLoginPage = $this->accessLoginPage($page);

            if (!$this->isAuthed($page) && !$isLoginPage) {
                $to = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://')."{$_SERVER['HTTP_HOST']}/{$uri}";
                $this->route->redirect($to);
            }

            if ($this->isAuthed($page) && $isLoginPage) {
                $to = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://')."{$_SERVER['HTTP_HOST']}/{$page['redirect_to']}";
                $this->route->redirect($to);
            }
        }

        return $path;
    }

    /**
     * ログイン処理後のリダイレクト。_script_before_head.htmlから呼び出される。
     */
    public function redirect() {
    	$to = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://')."{$_SERVER['HTTP_HOST']}{$_SERVER["REQUEST_URI"]}index.html";
    	$this->route->redirect($to);
    }

    /**
     * ログイン認証済みかチェック
     *
     * @return bool
     */
    private function isAuthed($page) {

        $uri = $this->viewHelper->uri($page['path']);
        session_start();
        if ($_SESSION[basename($uri)] === true) {
            return true;
        }

        return false;
    }

    /**
     * ログイン画面へのアクセスかチェック
     *
     * @param $page
     *
     * @return bool
     */
    private function accessLoginPage($page) {

        if (rtrim($this->viewHelper->_view->uri, '/') == '/'.$this->viewHelper->uri($page['path'])) {
            return true;
        }
        return false;
    }

    /**
     * 認証
     *
     * @param $id
     * @param $pass
     *
     * @return bool
     */
    public function verify($id, $pass) {

        $hash = hash('sha256', $id.$pass);

        if ($id == $this->id() && $hash == $this->pass()) {

            $db                                          = debug_backtrace();
            $_SESSION[basename(dirname($db[2]['file']))] = true;

            session_regenerate_id();

            return true;
        }
        return false;
    }

    private function id() {

        return $this->loginInfo(__FUNCTION__);
    }

    private function pass() {

        return $this->loginInfo(__FUNCTION__);
    }

    private function loginInfo($key) {

        foreach ($this->list as $page) {

            $uri = $this->viewHelper->uri($page['path']);

            if ($this->viewHelper->_view->uri != '/'.$uri || is_null($uri)) {
                continue;
            }

            return $page[$key];
        }
    }
}