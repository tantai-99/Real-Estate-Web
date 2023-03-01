<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');

class Log {

    static public $uid;

    private $config;
    private $path;
    private $canUse;

    const ACCESS_LOG_FILE_NAME = 'access.log';

    const TYPE_REQUEST  = 'request';
    const TYPE_RESPONSE = 'response';
    const TYPE_REDIRECT = 'redirect';

    const ERROR_404    = '404';
    const ERROR_SYSTEM = 'system error';

    public function __construct() {

        $this->path = APPLICATION_PATH.'/../log';

        $this->config  = parse_ini_file(APPLICATION_PATH.'/../setting/log.ini');
        $this->request = new Request();
        $this->ua      = new UserAgent();

        $this->canUse = in_array($this->request->domain, $this->config['domain']);

        if ($this->canUse() && !file_exists($this->path)) {
            mkdir($this->path);
        }
    }

    /**
     * ログを使用可能かチェック
     *
     * @return bool
     */
    public function canUse() {

        return $this->canUse;
    }

    /**
     * ログの保存
     *
     * @param $filename
     * @param $contents
     * @return bool
     */
    public function add($filename, $contents) {

        if (!$this->canUse()) {
            return false;
        }

        file_put_contents("{$this->path}/{$filename}", $contents.PHP_EOL, FILE_APPEND);
    }

    /**
     * アクセスログ保存専用
     *
     * @param        $type
     * @param string $content
     * @return bool
     */
    public function addAccessLog($type, $content = null) {

        if (!$this->canUse()) {
            return false;
        }

        $datetime = date("Y-m-d H:i:s");
        $uid      = self::$uid;

        $requestInfo = '';
        $ua          = '';
        if ($type === self::TYPE_REQUEST) {
            $referer     = $this->request->referer ? $this->request->referer : 'null';
            $requestInfo = "\"{$this->request->method} {$this->request->current}\" \"from: {$referer}\"";
            $ua          = "\"{$this->ua->useragent()}\"";
        }

        if ($type === self::TYPE_RESPONSE && !$content) {
            $content = 'Success';
        }

        $body = "[{$datetime}][{$uid}][{$this->ua->requestDevice()}][{$type}] {$requestInfo} \"{$content}\" {$ua}";
        $body = preg_replace("/[　\s]+/", " ", $body);
        $this->add(self::ACCESS_LOG_FILE_NAME, $body);
    }

    /**
     * 連携サーバーへのリクエストログ
     *
     * @param       $type
     * @param array $option
     */
    public function addApiLog($type, array $option = []) {

        $datetime = date("Y-m-d H:i:s");
        $uid      = self::$uid;
        $date     = date('ymd');
        $filename = "api_access_{$date}.log";

        if ($type === self::TYPE_REQUEST) {
            if (!isset($option['url'])) {
                $option = ['url' => 'null'];
            }
            $this->add($filename, "[{$datetime}][$uid][{$type}][{$option['url']}]");
            return;
        }

        if ($type === self::TYPE_RESPONSE) {
            if (!isset($option['time'])) {
                $option = ['time' => 'null'];
            }
            $this->add($filename, "[{$datetime}][$uid][{$type}][{$option['time']}]");
            return;
        }
    }

}