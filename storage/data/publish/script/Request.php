<?php

class Request {

    const POST = 'POST';
    const GET  = 'GET';

    public $current;
    public $parse;
    public $domain;
    public $request_uri;
    public $protcol;
    public $server_name;
    public $method;
    public $_POST;
    public $_COOKIE;
    public $referer;
    public $host;

    function __construct() {

        $this->domain      = $_SERVER['HTTP_HOST'];
        $this->request_uri = $_SERVER['REQUEST_URI'];
        $this->server_name = $_SERVER['SERVER_NAME'];
        $this->protcol     = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $this->method      = $_SERVER['REQUEST_METHOD'] === self::POST ? self::POST : self::GET;
        $this->_POST       = $_POST;
        $this->_COOKIE     = $_COOKIE;

        $this->current = $this->protcol.'://'.$this->domain.$this->request_uri;

        $pathinfo = pathinfo($this->current);

        $this->parse                = parse_url($this->current);
        $this->parse['dirname']     = $pathinfo['dirname'] ? $pathinfo['dirname'] : '';
        $this->parse['basename']    = $pathinfo['basename'] ? $pathinfo['basename'] : '';
        $this->parse['extension']   = $pathinfo['extension'] ? $pathinfo['extension'] : '';
        $this->parse['filename']    = $pathinfo['filename'] ? $pathinfo['filename'] : '';
        $this->parse['path_array']  = $this->path_array();
        $this->parse['query_array'] = $this->query_array();

        $this->referer = $_SERVER['HTTP_REFERER'];
        $this->host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    }

    private function path_array() {

        if ($this->parse['path'] === '/') {
            return [];
        }

        return explode('/', $this->trim_slash($this->parse['path']));
    }

    public function directory($num) {

        return isset($this->parse['path_array'][$num - 1]) ? $this->parse['path_array'][$num - 1] : null;
    }

    public function count_dir() {

        return count($this->parse['path_array']);
    }

    private function query_array() {

        $res = [];

        if (!isset($this->parse['query'])) {
            return $res;
        }

        foreach (explode('&', $this->parse['query']) as $query) {

            $key_value = explode('=', $query);

            $res[$key_value[0]] = $key_value[1];
        }
        return $res;
    }

    private function trim_slash($path) {

        if (preg_match('/^\//', $path)) {
            $path = ltrim($path, '/');
        }
        if (preg_match('/\/\z/', $path)) {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    public function remove_extension($url) {

        return substr($url, 0, mb_strlen($url) - (mb_strlen('.') + mb_strlen($this->parse['extension'])));
    }

    public function remove_filename($url) {

        return substr($url, 0, mb_strlen($url) - (mb_strlen('.') + mb_strlen($this->parse['basename'])));
    }

    public function remove_last_slash($url) {

        if ($this->has_last_slash($url)) {
            return substr($url, 0, mb_strlen($url) - (mb_strlen('/')));
        }
        return $url;
    }

    public function set_last_slash($url) {

        if (!$this->has_last_slash($url)) {
            return $url.'/';
        }
        return $url;
    }

    public function has_www() {

        return preg_match('/^www./', $this->domain);
    }

    public function has_last_slash($url) {

        return preg_match('/\/$/', $url);
    }

    public function has_url_params($url) {

        $parsed = parse_url($url);
        return $parsed['query'] !== null;
    }

    // post
    public function getPost($key) {

        if (!isset($this->_POST[$key])) {
            return null;
        }

        return $this->_POST[$key];
    }

    public function getPostAll() {

        return $this->_POST;
    }

    // cookie
    public function getCookie($key) {

        if (!isset($this->_COOKIE[$key])) {
            return null;
        }

        return $this->_COOKIE[$key];
    }

    private function getCookieListAll($key) {

        $res = [];

        $list = json_decode($_COOKIE[$key], true);
        krsort($list);

        if (count($list) < 1) {
            return null;
        }

        foreach ($list as $i => $val) {

            $res[$i] = $val;
        }

        return $res;
    }

    public function getHistoriesAll() {

        if(isset($this->_COOKIE['histories'])) {
            return $this->getCookieListAll('histories');
        } else {
            return $this->getCookieListAll('histories_save');
        }
    }

    public function getHistories($max) {

        $res = [];

        $histories = json_decode($_COOKIE['histories'], true);
        krsort($histories);

        if (count($histories) < 1) {
            return null;
        }

        $cnt = 0;
        foreach ($histories as $i => $val) {

            $res[] = $val;
            $cnt++;

            if ($max <= $cnt) {
                break;
            }
        }

        return $res;
    }

    public function getFavoriteAll() {

        if(isset($this->_COOKIE['favorite'])) {
            return $this->getCookieListAll('favorite');
        } else {
            return $this->getCookieListAll('favorite_save');
        }
    }

    public function getSortList() {

        return $this->getCookieListAll('search_config')['sort'];
    }

    public function getSort($key) {

        return $this->getSortList()[$key];
    }

    public function getTotal() {

        return $this->getCookieListAll('search_config')['total'];
    }

    // url params
    public function getBukkenIdFromUrl($num = 2) {

        if (preg_match('/^detail-/', $this->directory($num))) {
            return str_replace('detail-', '', $this->directory($num));
        }
        return null;
    }

    public function getRailwayFromUrl($num) {

        $path = $this->directory($num);

        // no extension
        if (preg_match('/-line\z/', $path)) {
            return str_replace('-line', '', $path);
        }

        // has extension
        if (preg_match('/-line.html\z/', $path)) {
            return str_replace('-line.html', '', $path);
        }

        return null;
    }

    public function getStationFromUrl($num) {

        if (preg_match('/-eki.html\z/', $this->directory($num))) {
            return str_replace('-eki.html', '', $this->directory($num));
        }
        return null;
    }

    public function getCityFromUrl($num) {

        if (preg_match('/-city.html\z/', $this->directory($num))) {
            return str_replace('-city.html', '', $this->directory($num));
        }
        else if (preg_match('/-city\z/', $this->directory($num))) {
            return str_replace('-city', '', $this->directory($num));
        }
        else if (preg_match('/-map.html\z/', $this->directory($num))) {
            return str_replace('-map.html', '', $this->directory($num));
        }

        return null;
    }

    public function getMcityFromUrl($num) {

        if (preg_match('/-mcity.html\z/', $this->directory($num))) {
            return str_replace('-mcity.html', '', $this->directory($num));
        }
        return null;
    }

    public function getChosonFromUrl($num) {

        if (preg_match('/-[0-9]+\.html\z/', $this->directory($num))) {
            return str_replace('.html', '', $this->directory($num));
        }
        return null;

    }

    public function getPage() {

        $res = 1;

        // get param
        if (($page = $this->getPost('page')) !== null) {
            $res = $page;
        }
        // get param
        elseif (($page = $this->parse['query_array']['page']) !== null) {
            $res = $page;
        }

        return $res < 1 ? 1 : (int)$res;
    }

    /**
     * ◯◯選択画面の「検索ボタン」生成用のベースURL
     * moblie専用
     *
     * @return string
     */
    public function getMobileSelectBaseUrl() {

        return "{$this->protcol}://{$this->domain}/{$this->directory(1)}/{$this->directory(2)}/";
    }

    /**
     * get canonical uri
     *
     * @return string
     */
    public function getCanonicalUri() {

        $route = new Route(new ViewHelper(new stdClass()));

        switch ($route->find()) {
            case SearchPages::RENT:
            case SearchPages::PURCHASE:
                return '/shumoku.html';
            case SearchPages::DETAIL_MAP:
                $uri = $this->request_uri;
                return substr($uri, 0, strlen($uri) - strlen('map.html'));
            default:
                return $this->request_uri;
        }
    }

    /**
     * アクセス元のドメインを取得
     *
     * @return null or string
     */
    public function accessFrom() {

        if ($this->referer === null) {
            return null;
        }

        $url = parse_url($this->referer);
        return $url['host'];
    }
}