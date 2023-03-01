<?php

require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
class ApiConversion {

    const TIMEOUT = 30;
    const CONVERSIONAPI_TELTAP = '/api-conversion/teltap';

    private $conversionApis = array(
        self::CONVERSIONAPI_TELTAP,
    );

    public function __construct(viewHelper $viewHelper) {

        $this->request    = new Request();
        $this->validate   = new Validate();
        $this->ua         = new UserAgent();
        $this->logger     = new Log();
        $this->apiConfig  = new ApiConfing();

        $this->viewHelper = $viewHelper;

    }


    public function isConversionApi(){

        if( in_array($this->request->request_uri, $this->conversionApis) ){
            return true;
        }
        return false;

    }


    public function conversion(){

        switch($this->request->request_uri)
        {
            case self::CONVERSIONAPI_TELTAP:
                $this->telTap();
                break;

            default:
                break;
        }
    }


    /** 電話番号タップのコンバージョン
     *
     */
    public function telTap() {

        $protocol  = ($this->apiConfig->get('app_env')=='production') ? 'https://': 'http://';
        $this->apiHost = $protocol.$this->apiConfig->get('domain');

        $path = '/api/conversion/tel-tap';

        // base
        $url = $this->apiHost . $path . $this->getBaseApiParam();

        // page_url
        $pageUrl = parse_url($this->request->getPost('page_url'));
        $url .= $this->addParam('page_url', $pageUrl['path']);

        $respons = $this->curlGet($url);
        $respons = json_decode($respons);

        echo json_encode((array)$respons);
        return;

    }


    /**
     * get
     *
     * @param $url
     * @return string
     */
    public function curlGet($url, $timeout = self::TIMEOUT) {

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
     *
     */
    protected function getBaseApiParam(){

        $param='';

        // com_id
        $param .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $param .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $param .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $param .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        // ipアドレス
        $param .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);

        return $param;
    }

    /**
     * リクエストヘッダーの生成
     *
     * @return array
     */
    private function generateHeader($containExpect = false) {

        $res = ["HTTP_USER_AGENT: {$this->ua->useragent()}"];

        if ($containExpect) {
            $res[] = ['Expect:'];
        }
        return $res;
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

}