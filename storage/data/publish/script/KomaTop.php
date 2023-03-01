<?php 
require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
class KomaTop {

    public function run($config) {
        $this->apiConfig = new  ApiConfing();
        $this->ua        = new UserAgent();
        $path = '/v1api/parts/koma-top';
        
        // base
        $url = 'http://'.$this->apiConfig->get('domain').$path;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $config['media']);

        $url .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);

        // special-path
        $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $config['special-path']);

        // rows
        $url .= $this->addParam(ApiGateway::KEY_ROWS, $config['rows']);
        
        // columns
        $url .= $this->addParam(ApiGateway::KEY_COLUMNS, $config['columns']);

        // sort-option
        $url .= $this->addParam(ApiGateway::KEY_SORT_OPTION, $config['sort-option']);
        
       $result = json_decode($this->get($url)) ; 
       
       return $result->content;
    }

    private function get($url, $timeout = 20) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->generateHeader());
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($curl);
        $info     = curl_getinfo($curl);
        $body   = substr($response, $info['header_size']);

        curl_close($curl);

        return $body;
    }

    private function addParam($key, $value, $first = false) {

        $and = '&';
        if ($first) {
            $and = '?';
        }

        if (is_array($value)) {

            $value = implode(',', $value);
        }

        return $and.(string)$key.'='.urlencode((string)$value);
    }

    private function generateHeader($containExpect = false) {

        $res = ["HTTP_USER_AGENT: {$this->ua->useragent()}"];

        if ($containExpect) {
            $res[] = ['Expect:'];
        }
        return $res;
    }

}


?>