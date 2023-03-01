<?php

require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
class KkApi {

    const TIMEOUT = 30;


    /**
     *
     */
    public function getAuthSession() {

        $this->apiConfig = new ApiConfing();

        $protocol  = ($this->apiConfig->get('app_env')=='production') ? 'https://': 'http://';
        $this->apiHost   = $protocol.$this->apiConfig->get('domain');
        $this->loginApi  = '/api/kk-api/get-auth-session/';

        $url = $this->apiHost.$this->loginApi;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        $respons = $this->curl($url);
        $respons = json_decode($respons);

        return $respons;

    }

    /**
     * get
     *
     * @param $url
     * @return string
     */
    private function curl($url, $timeout = self::TIMEOUT) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($curl);
        $info     = curl_getinfo($curl);
        curl_close($curl);
        return $response;
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

}