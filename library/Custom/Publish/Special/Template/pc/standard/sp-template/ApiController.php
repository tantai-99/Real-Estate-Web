<?php

require_once(APPLICATION_PATH.'/../script/_AbstractApiController.php');
require_once(APPLICATION_PATH.'/../script/Path.php');

class ApiController extends AbstractApiController {

    protected $name      = 'special';
    protected $searchUrl = '/v1api/special/result';

    public function searchAbstract($urlBase, $responseType = 'html') {

        header('Content-Type: application/json; charset=utf-8');

        // validation
        if ($this->request->method !== Request::POST) {
            $this->error('POST only');
            return;
        }

        if (is_null($this->apiConfig->get('com_id')) || is_null($this->apiConfig->get('publish')) || is_null($this->request->getPost('special_path')) || is_null($this->request->getPost('prefecture')) || is_null($this->request->getPost('s_type'))) {
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

        // special path
        $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $specilaPath = $this->request->getPost('special_path'));

        // prefecture
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost('prefecture'));

        // s_type
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->request->getPost('s_type'));

        // sort
        if ($sort = $this->request->getSort($specilaPath)) {
            $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
        };

        // count
        if ($total = $this->request->getTotal()) {
            $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $total);
        };

        // page
        $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPage());

        // pic
        $url .= $this->addParam(ApiGateway::KEY_PIC, $this->request->getPost('pic') !== null ? $this->request->getPost('pic') : 1);

        // area
        if ($this->request->getPost('area')) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost('area'));
        }

        // railway
        if ($this->request->getPost('railway')) {
            $url .= $this->addParam(ApiGateway::KEY_RAILWAY, $this->request->getPost('railway'));
        }

        // station
        if ($this->request->getPost('station')) {
            $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));
        }

        // mcity
        if ($this->request->getPost('mcity')) {
            $url .= $this->addParam(ApiGateway::KEY_MCITY, $this->request->getPost('mcity'));
        }

        // choson
        if ($this->request->getPost('choson')) {
            $url .= $this->addParam(ApiGateway::KEY_CHOSON, $this->request->getPost('choson'));
        }

        if ($this->request->getPost('only_choson_modal')) {
            $url .= $this->addParam('only_choson_modal', $this->request->getPost('only_choson_modal'));
        }

        // direct result
        if ($this->request->getPost('direct_result') === 'true') {
            $url .= $this->addParam(ApiGateway::KEY_DIRECT_ACCESS, (string)true);
        }

        // condition side
        $side = [];
        if ($this->request->getPost('condition_side')) {
            parse_str($this->request->getPost('condition_side'), $side);
        }

        // condition modal
        $modal = [];
        if ($this->request->getPost('condition_modal')) {
            parse_str($this->request->getPost('condition_modal'), $modal);
        }

        // merge
        // $data = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side, $modal) : array_merge($modal, $side);
        if (!empty($modal) && !empty($side)) {
            $data['search_filter'] = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side['search_filter'], $modal['search_filter']) : array_merge($modal['search_filter'], $side['search_filter']);
        } else if (!empty($side)) {
            $data = $side;
        } else {
            $data = $modal;
        }

        $apiResponse = json_decode($this->apiGateway->post($url, $data));

        if (!$apiResponse->success) {
            $this->logger->addAccessLog(Log::TYPE_RESPONSE, Log::ERROR_SYSTEM); //log

            // dev
            if ($this->apiConfig->get('dev')) {
                $this->error("access url : {$url}");
                return;
            }
            $this->error();
            return;
        }

        if ($responseType === 'html') {
            // $apiResponse = (new SearchResult($apiResponse))->html();
            $apiResponse->content = $this->moveElementByTheme($apiResponse->content);
            //$apiResponse->content = $this->setAbsolutePath($apiResponse->content);
        }

        $this->logger->addAccessLog(Log::TYPE_RESPONSE); //log
        echo json_encode((array)$apiResponse);
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

        $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $specilaPath = $this->request->getPost('special_path'));

        // prefecture
        if ($this->request->getPost('prefecture')) {
            $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost('prefecture'));
        }

        // area
        if ($this->request->getPost('city')) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost('city'));
        }

        // station
        if ($this->request->getPost('station')) {
            $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));
        }

        // choson
        if ($this->request->getPost('choson')) {
            $url .= $this->addParam(ApiGateway::KEY_CHOSON, $this->request->getPost('choson'));
        }

        if ($this->request->getPost('fulltext')) {
            $url .= $this->addParam(ApiGateway::KEY_FULLTEXT, $this->request->getPost('fulltext'));
        }

        // condition side
        $data = [];
        if ($this->request->getPost('condition_side')) {
            parse_str($this->request->getPost('condition_side'), $data);
        }

        $apiResponse = (array) json_decode($this->apiGateway->post($url, $data));
        echo json_encode((array)$apiResponse[$field]);

    }

}