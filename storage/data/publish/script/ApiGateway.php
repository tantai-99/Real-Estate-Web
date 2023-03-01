<?php

require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

define('APOSTROPHE_MARKS', "'");
define('QUOTATION_MARKS', '"');
class ApiGateway {

    const TRY_MAX = 1; // tryCurlの最大試行回数
    const TIMEOUT = 30;

    private $ua;
    private $username;
    private $password;

    private $logger;

    const KEY_COM_ID         = 'com_id';
    const KEY_API_KEY        = 'api_key';
    const KEY_PUBLISH        = 'publish';
    const KEY_MEDIA          = 'media';
    const KEY_SHUMOKU        = 'type_ct';
    const KEY_PREFECUTURE    = 'ken_ct';
    const KEY_CITY           = 'shikugun_ct';
    const KEY_MCITY          = 'locate_ct';
    const KEY_RAILWAY        = 'ensen_ct';
    const KEY_STATION        = 'eki_ct';
    const KEY_PER_PAGE       = 'per_page';
    const KEY_SORT           = 'sort';
    const KEY_PAGE           = 'page';
    const KEY_BUKKEN_ID      = 'bukken_id';
    const KEY_HISTORIES      = 'history';
    const KEY_TAB            = 'tab';
    const KEY_SPECIAL_PATH   = 'special_path';
    const KEY_S_TYPE         = 's_type';
    const KEY_CONDITION_SIDE = 'search_filter';
    const KEY_PIC            = 'pic';
    const KEY_FROM_RECOMMEND = 'from_recommend';
    const KEY_FROM_SEARCHMAP = 'from_searchmap';
    const KEY_SPECIAL_ID     = 'special_id';
    const KEY_SORT_OPTION    = 'sort_option';
    const KEY_ROWS           = 'rows';
    const KEY_DIRECT_ACCESS  = 'direct_access';
    const KEY_ALLOW_REDIRECT = 'allow_redirect';
    const KEY_CONTACT_TYPE   = 'contact_type';
    const KEY_USER_IP        = 'user_ip';
    const KEY_OPERATION      = 'operation';
    const KEY_USER_ID        = 'user_id';

    const KEY_SW_LAT_LAN     = 'sw_lat_lan';
    const KEY_NE_LAT_LAN     = 'ne_lat_lan';

    const KEY_CHOSON         = 'choson_ct';
    const KEY_PANORAMA       = 'panorama';
    const KEY_FULLTEXT       = 'fulltext';
    const KEY_F_TYPE         = 'f_type';

    const KEY_COLUMNS        = 'columns';

    // key use for FDP
    const KEY_POS_STATION    = 'station';
    const KEY_POS_HOUSE      = 'house';
    const KEY_DISTANCE       = 'distance';
    const KEY_OVERVIEW_PATH  = 'overview_path';
    const KEY_SESSION_ID     = 'session_id';
    const KEY_TYPE_CHART     = 'type_chart';
    
    const KEY_SP_SESSION     = 'sp_session';

    const KEY_MAP_INITIALIZED = 'map_initialized';

    public function __construct($config) {

        $this->ua       = new UserAgent();
        $this->logger   = new Log();
        $this->username = isset($config['username']) ? $config['username'] : '';
        $this->password = isset($config['password']) ? $config['password'] : '';
        $this->apiConfig  = new ApiConfing();
    }

    /**
     * get
     *
     * @param $url
     * @return string
     */
    public function get($url, $timeout = self::TIMEOUT) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->generateHeader());
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($this->username !== '') {
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        $res = $this->tryCurl($curl, ['url' => $url,]);

        curl_close($curl);

        return $this->makeResponse($res['body'], $this->getStatusCode($res['header']));
    }

    /**
     * post
     *
     * @param       $url
     * @param array $queries
     * @return string
     */
    public function post($url, array $queries, $timeout = self::TIMEOUT) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->generateHeader(true));
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($queries));
        if ($this->username !== '') {
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        $res = $this->tryCurl($ch, ['url' => $url,]);
        curl_close($ch);

        return $this->makeResponse($res['body'], $this->getStatusCode($res['header']));
    }

    /**
     * curl 実行
     *
     * @param $ch
     * @return array
     */
    private function tryCurl($ch, array $option = []) {

        // log
        if ($this->logger->canUse() && count($option) > 0) {

            $this->logger->addApiLog(Log::TYPE_REQUEST, $option);
        }

        $cnt = 0;

        do {
            $cnt++;
            $timeStart = microtime(true);

            $response = curl_exec($ch);
            $info     = curl_getinfo($ch);

            $header = substr($response, 0, $info['header_size']);
            $body   = substr($response, $info['header_size']);

            $responseObject = json_decode($body);

            $hasError = //
                // レスポンスがない（連携サーバーにつながらない）
                !$response || //
                // エラーレスポンス && エラーID（物件APIへの接続エラー）
                ($responseObject instanceof stdClass && !$responseObject->success && $responseObject->error_id);
        }
        while ($hasError && $cnt < self::TRY_MAX);

        // log
        if ($this->logger->canUse() && count($option) > 0) {

            $option = ['time' => (microtime(true) - $timeStart).'s'];
            $this->logger->addApiLog(Log::TYPE_RESPONSE, $option);
        }

        return [
            'header' => $header,
            'body'   => $body,
        ];
    }

    /**
     * リクエストヘッダーの生成
     *
     * @return array
     */
    private function generateHeader($containExpect = false) {

        //$res = ["HTTP_USER_AGENT: {$this->ua->useragent()}"];
        $res = [
            "HTTP_USER_AGENT: {$this->ua->useragent()}",
            "X-FORWARDED-FOR: ".$_SERVER["REMOTE_ADDR"]
        ];
        if ($containExpect) {
            $res[] = ['Expect:'];
        }
        return $res;
    }

    /**
     * ステータスコード取得
     *
     * @param $http_response_header
     * @return mixed
     */
    private function getStatusCode($header) {

        preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $header, $matches);
        return $matches[1];
    }

    /**
     * 正常以外はステータスコードを返す
     *
     * @param $content
     * @param $statusCode
     * @return string
     */
    private function makeResponse($content, $statusCode) {

        return $statusCode === '200' ? mb_convert_encoding($content, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN') : json_encode(['status_code' => $statusCode]);
    }

    /**
     * 4707 Check peripheral exist
     * @param $url
     * @return true (not exist) |flase (exist)
     */
    public function boolPeripheral($url) {
        $parts = parse_url($url);
        parse_str($parts['query'], $params);
        $filename = '';
        switch ($params['type_ct']) {
            case "chintai":
                $filename = 'kasi-kyojuu';
                break;
            case "kasi-tenpo":
            case "kasi-office":
            case "parking":
            case "kasi-tochi":
            case "kasi-other":
                $filename = 'kasi-jigyou';
                break;
            case "mansion":
            case "kodate":
            case "uri-tochi":
                $filename = 'uri-kyojuu';
                break;
            case "uri-tenpo":
            case "uri-office":
            case "uri-other":
                $filename = 'uri-jigyou';
                break;
        }
        $path = glob(APPLICATION_PATH.'/../setting/contact_'.$filename.'*.ini');
        $path = empty($path) ? '' : $path[0];
        $config = parse_ini_file($path,true);
        return $config && !isset($config['item_peripheral']);
    }


    /**
     * Get Sitetype
     * @return string sitetype
     */
    public function getSitetype() {
        $sitetype = '';
        switch ($this->apiConfig->get(self::KEY_PUBLISH)) {
            case 1:
                $sitetype = 'public';
                break;
            case 2:
                $sitetype = 'test';
                break;
            case 3:
                $sitetype = 'substitute';
                break;
            default:
                break;
        }
        return $sitetype;
    }
}