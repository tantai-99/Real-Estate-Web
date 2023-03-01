<?php

namespace Library\Custom\Model\Estate;

class ApiGateway
{

    const TIMEOUT = 20;
    const TRY_MAX = 1;
    static protected $_instance;

    /**
     * @return Library\Custom\Model\Lists\ListAbstract
     */
    static public function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
    /**
     * get
     *
     * @param $url
     * @return string
     */
    public function get($url, $timeout = self::TIMEOUT)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

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
    public function post($url, array $queries, $timeout = self::TIMEOUT)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($queries));

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
    private function tryCurl($ch, array $option = [])
    {


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
        } while ($hasError && $cnt < self::TRY_MAX);

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
    private function getStatusCode($header)
    {
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
    private function makeResponse($content, $statusCode)
    {

        return $statusCode === '200' ? mb_convert_encoding($content, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN') : json_encode(['status_code' => $statusCode]);
    }

    /**
     * URLパラメータ追加
     *
     * @param      $key
     * @param      $value
     * @param bool $first
     * @return string
     */
    public function addParam($key, $value, $first = false)
    {

        $and = '&';
        if ($first) {
            $and = '?';
        }

        if (is_array($value)) {

            $value = implode(',', $value);
        }

        return $and . (string)$key . '=' . urlencode((string)$value);
    }
}
