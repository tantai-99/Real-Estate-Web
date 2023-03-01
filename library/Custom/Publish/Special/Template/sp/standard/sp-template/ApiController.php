<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
require_once(APPLICATION_PATH.'/../script/_AbstractApiController.php');

class ApiController extends AbstractApiController {

    public function searchAbstract($urlBase, $responseType = null) { }

    protected $session;
    protected $namespace = 'condition';

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

        $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $shumoku = $this->request->getPost('special_path'));

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
}
