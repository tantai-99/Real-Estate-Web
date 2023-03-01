<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
require_once(APPLICATION_PATH.'/../script/_AbstractApiController.php');
require_once(APPLICATION_PATH.'/../script/KkApi.php');

class ApiController extends AbstractApiController {

    public function searchAbstract($urlBase, $responseType = null) { }
    protected $session;
    protected $namespace = 'condition';

    /**
     * 物件コマ要素取得
     */
    protected function komaAction() {

        $path = '/v1api/parts/koma';

        header('Content-Type: application/json; charset=utf-8');

        // base
        $url = $this->apiHost.$path;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        // special-path
        $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $this->request->getPost('special-path'));

        // rows
        $url .= $this->addParam(ApiGateway::KEY_ROWS, $this->request->getPost('rows'));

        // sort-option
        $url .= $this->addParam(ApiGateway::KEY_SORT_OPTION, $this->request->getPost('sort-option'));

        $apiResponse = json_decode($this->apiGateway->get($url));

        $this->convertProtocol($apiResponse);


        if (!$apiResponse->success) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log
            if ($this->apiConfig->get('dev')) {
                $this->error("access url : {$url}");
                return;
            }
            $this->error();
            return;
        }

        $apiResponse->content = $this->moveElementByTheme($apiResponse->content);
        //$apiResponse->content = $this->setAbsolutePath($apiResponse->content);

        $this->logger->addAccessLog(Log::TYPE_RESPONSE); //log
        echo json_encode((array)$apiResponse);
    }

    /**
     * 行動情報を保存
     */
    protected function saveAction() {

        $path = '/v1api/parts/save-operation';

        header('Content-Type: application/json; charset=utf-8');

        // 本番サイト以外は保存しない
        if ($this->apiGateway->getSitetype() != 'public') {
            echo json_encode(array('status' => 'success'));
            return;
        }

        // 必要なパラメータが空の場合は処理を行わない
        $com_id = $this->apiConfig->get(ApiGateway::KEY_COM_ID);
        $api_key = $this->apiConfig->get(ApiGateway::KEY_API_KEY);
        if (empty($com_id) || empty($api_key)) {
            $this->error();
            return;
        }

        // base
        $url = $this->apiHost.$path;
    
        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $com_id, true);
    
        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $api_key);
    
        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));
    
        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        // operation
        $url .= $this->addParam(ApiGateway::KEY_OPERATION, $this->request->getPost(ApiGateway::KEY_OPERATION));

        // user_id
        $url .= $this->addParam(ApiGateway::KEY_USER_ID, $this->request->getCookie(ApiGateway::KEY_USER_ID));
    
        // bukken_id
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $this->request->getPost(ApiGateway::KEY_BUKKEN_ID));

        $apiResponse = json_decode($this->apiGateway->get($url));
    
        $this->convertProtocol($apiResponse);
        if (!$apiResponse->success) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log
            if ($this->apiConfig->get('dev')) {
                $this->error("access url : {$url}");
                return;
            }
            $this->error();
            return;
        }
    
        $this->logger->addAccessLog(Log::TYPE_RESPONSE); //log
        echo json_encode((array)$apiResponse);
    }

    public function accessCount($countType = null) {
        $path = '/v1api/access-count/'. $countType;
        header('Content-Type: application/json; charset=utf-8');
        // base
        $url = $this->apiHost.$path;
        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);
        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));
        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));
        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());
        // ipアドレス
        $url .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);
        // bukken_id
        $bukkenId = $this->request->parse['path_array'][1];
        $bukkenId = preg_replace("/^detail\-/", "", $bukkenId);
        $url .= $this->addParam('bukken_id', $bukkenId);
        $apiResponse = json_decode($this->apiGateway->get($url));
        if (!$apiResponse->success) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log
            if ($this->apiConfig->get('dev')) {
                $this->error("access url : {$url}");
                return;
            }
            $this->error();
            return;
        }
        $res = [
            'success' => true,
            'message' => '',
        ];
        echo json_encode($res);
    }

    private function convertProtocol($response)
    {

        if(isset($response->content) && $this->request->protcol == 'https' && !is_object($response->content) ){

            $pattern = '~((src|data-src|data-original)=["\'])(https?://)~iU';
            $replacement = '$1https://';
            $response->content = preg_replace($pattern, $replacement, $response->content);
        }
    }

    public function suggestCountAbstract($urlBase, $field, $responseType = 'html')
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->request->method !== Request::POST) {
            $this->error('POST only');
            return;
        }

        if (is_null($this->apiConfig->get('com_id')) || is_null($this->apiConfig->get('publish'))) {
            $this->error();
            return;
        }

        // base
        $url = $this->apiHost.$urlBase;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        // ipアドレス
        $url .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);

        $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $shumoku = $this->request->getPost('shumoku'));

        // prefecture
        session_start();
        $this->session = new Session($this->namespace);
        if ($this->request->getPost('prefecture')) {
            $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost('prefecture'));
            // area
            if ($this->session->get('city')) {
                $url .= $this->addParam(ApiGateway::KEY_CITY, $this->session->get('city'));
            }
            if ($this->request->getPost('city')) {
                $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost('city'));
            }

            // choson
            if ($this->request->getPost('choson')) {
                $url .= $this->addParam(ApiGateway::KEY_CHOSON, $this->request->getPost('choson'));
            }

            // station
            if ($this->session->get('station')) {
                $url .= $this->addParam(ApiGateway::KEY_STATION, $this->session->get('station'));
            }
            if ($this->request->getPost('station')) {
                $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));
            }
        }
        if ($this->request->getPost('fulltext')) {
            $url .= $this->addParam(ApiGateway::KEY_FULLTEXT, $this->request->getPost('fulltext'));
        }
        if ($this->request->getPost('type_freeword')) {
            $url .= $this->addParam(ApiGateway::KEY_F_TYPE, $this->request->getPost('type_freeword'));
        }
        
        if ($this->request->getPost('from_searchmap')) {
            $url .= $this->addParam(ApiGateway::KEY_FROM_SEARCHMAP, $this->request->getPost('from_searchmap'));
        }

        if ($this->request->getPost('condition')) {
            $params = [
                SearchCondition::CONDITION => $this->request->getPost('condition'),
            ];
            $this->session->set($params);
        }
        $data = [];
        if ($this->session->get('condition')) {
            parse_str($this->session->get('condition'), $data);
        }
        
        $apiResponse = (array) json_decode($this->apiGateway->post($url, $data));
        echo json_encode((array)$apiResponse[$field]);

    }

    public function mapkkauthAction(){
        $kkApi = new KkApi();
        $authInfo = $kkApi->getAuthSession();

        echo json_encode((array)$authInfo);
    }

}