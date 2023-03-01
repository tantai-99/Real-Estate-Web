<?php

require_once(APPLICATION_PATH.'/../script/_AbstractApiController.php');
require_once(APPLICATION_PATH.'/../script/Path.php');
require_once(APPLICATION_PATH.'/../script/KkApi.php');

class ApiMapController extends AbstractApiController
{

    protected $name = 'search';
    protected $searchUrl = '/v1api/search/result';


    /**
     * 連携サーバーからデータ取得
     *
     * @param        $urlBase
     * @param string $responseType
     */
    public function searchAbstract($urlBase, $responseType = 'html')
    {

        header('Content-Type: application/json; charset=utf-8');

        // validation
        if ($this->request->method !== Request::POST) {
            $this->error('POST only');
            return;
        }

        if (is_null($this->apiConfig->get('com_id')) || is_null($this->apiConfig->get('publish')) || is_null($this->request->getPost('prefecture')) || is_null($this->request->getPost('s_type'))) {
            $this->error();
            return;
        }

        // base
        $url = $this->apiHost . $urlBase;

        // com_id
        $url .= $this->addParam(ApiGateway::KEY_COM_ID, $this->apiConfig->get(ApiGateway::KEY_COM_ID), true);

        // api key
        $url .= $this->addParam(ApiGateway::KEY_API_KEY, $this->apiConfig->get(ApiGateway::KEY_API_KEY));

        // publish_type
        $url .= $this->addParam(ApiGateway::KEY_PUBLISH, $this->apiConfig->get(ApiGateway::KEY_PUBLISH));

        // media
        $url .= $this->addParam(ApiGateway::KEY_MEDIA, $this->ua->requestDevice());

        if ($this->name === 'search') {

            // shumoku
            $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $shumoku = $this->request->getPost('shumoku'));

            // sort
            if ($sort = $this->request->getSort($shumoku)) {
                $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
            };
        }

        if ($this->name === 'special') {

            // special-path
            $url .= $this->addParam(ApiGateway::KEY_SPECIAL_PATH, $specilaPath = $this->request->getPost('special_path'));
            // sort
            if ($sort = $this->request->getSort($specilaPath)) {
                $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
            };
        }

        // prefecture
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost('prefecture'));

        // s_type
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->request->getPost('s_type'));

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
        $data = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side, $modal) : array_merge($modal, $side);

        $apiResponse = json_decode($this->apiGateway->post($url, $data));

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

        if ($responseType === 'html') {
            // $apiResponse = (new SearchResult($apiResponse))->html();
            $apiResponse->content = $this->moveElementByTheme($apiResponse->content);
            //$apiResponse->content = $this->setAbsolutePath($apiResponse->content);
        }
        $this->logger->addAccessLog(Log::TYPE_RESPONSE); //log
        echo json_encode((array)$apiResponse);
    }

    /**
     * 地図検索中心位置取得
     */
    protected function mapcenterAction()
    {

        header('Content-Type: application/json; charset=utf-8');

        $path = '/v1api/search/spatial-mapcenter';

        // base
        $url = $this->apiHost . $path . $this->getBaseApiParam();

        // shumoku
        $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $this->request->getPost(ApiGateway::KEY_SHUMOKU));

        // pref
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost(ApiGateway::KEY_PREFECUTURE));

        // city
        $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost(ApiGateway::KEY_CITY));

        $apiResponse = json_decode($this->apiGateway->get($url));

        $this->convertProtocol($apiResponse);

        echo json_encode((array)$apiResponse);

    }

    /**
     * 地図更新
     */
    protected function updatemapAction()
    {

        header('Content-Type: application/json; charset=utf-8');

        $path = '/v1api/search/spatial-estate';

        // base
        $url = $this->apiHost . $path . $this->getBaseApiParam();

        // shumoku
        $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $this->request->getPost(ApiGateway::KEY_SHUMOKU));

        // pref
        if ($this->request->getPost(ApiGateway::KEY_PREFECUTURE)) {
            $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->getPost(ApiGateway::KEY_PREFECUTURE));
        }

        // city
        if ($this->request->getPost(ApiGateway::KEY_CITY)) {
            $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getPost(ApiGateway::KEY_CITY));
        }

        // sw_lat_lan
        $url .= $this->addParam(ApiGateway::KEY_SW_LAT_LAN, $this->request->getPost(ApiGateway::KEY_SW_LAT_LAN));

        // ne_lat_lan
        $url .= $this->addParam(ApiGateway::KEY_NE_LAT_LAN, $this->request->getPost(ApiGateway::KEY_NE_LAT_LAN));

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
//        $data = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side, $modal) : array_merge($modal, $side);
        if (!empty($modal) && !empty($side)) {
            $data['search_filter'] = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side['search_filter'], $modal['search_filter']) : array_merge($modal['search_filter'], $side['search_filter']);
        } else if (!empty($side)) {
            $data = $side;
        } else {
            $data = $modal;
        }

        $apiResponse = json_decode($this->apiGateway->post($url, $data));

        $this->convertProtocol($apiResponse);

        echo json_encode((array)$apiResponse);

    }

    /**
     * 地図上の物件リストを取得する
     */
    protected function maplistAction()
    {
        header('Content-Type: application/json; charset=utf-8');

        $path = '/v1api/parts/estatelist';

        // base
        $url = $this->apiHost . $path . $this->getBaseApiParam();

        // shumoku
        $shumoku = $this->request->getPost(ApiGateway::KEY_SHUMOKU);
        $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $this->request->getPost(ApiGateway::KEY_SHUMOKU));

        // sw_lat_lan
        $url .= $this->addParam(ApiGateway::KEY_SW_LAT_LAN, $this->request->getPost(ApiGateway::KEY_SW_LAT_LAN));

        // ne_lat_lan
        $url .= $this->addParam(ApiGateway::KEY_NE_LAT_LAN, $this->request->getPost(ApiGateway::KEY_NE_LAT_LAN));

        // page
        $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPost(ApiGateway::KEY_PAGE));

        // per page
        $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $this->request->getPost(ApiGateway::KEY_PER_PAGE));

        // sort
        if ($sort = $this->request->getSort($shumoku)) {
            $url .= $this->addParam(ApiGateway::KEY_SORT, $sort);
        };

        // pic
        $url .= $this->addParam(ApiGateway::KEY_PIC, $this->request->getPost('pic') !== null ? $this->request->getPost('pic') : 1);

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
//        $data = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side, $modal) : array_merge($modal, $side);
        if (!empty($modal) && !empty($side)) {
            $data['search_filter'] = $this->request->getPost('side_or_modal') === 'modal' ? array_merge($side['search_filter'], $modal['search_filter']) : array_merge($modal['search_filter'], $side['search_filter']);
        } else if (!empty($side)) {
            $data = $side;
        } else {
            $data = $modal;
        }

        //$apiResponse = json_decode($this->apiGateway->get($url));
        $apiResponse = json_decode($this->apiGateway->post($url, $data));

        $this->convertProtocol($apiResponse);

        echo json_encode((array)$apiResponse);


    }


    /**
     * 地図サイド物件リストを取得する
     */
    protected function mapsidelistAction()
    {
        header('Content-Type: application/json; charset=utf-8');

        $path = '/v1api/search/spatial-estatelist';

        // base
        $url = $this->apiHost . $path . $this->getBaseApiParam();

        // shumoku
        $url .= $this->addParam(ApiGateway::KEY_SHUMOKU, $this->request->getPost(ApiGateway::KEY_SHUMOKU));

        // per page
        $url .= $this->addParam(ApiGateway::KEY_PER_PAGE, $this->request->getPost(ApiGateway::KEY_PER_PAGE));

        // page
        $url .= $this->addParam(ApiGateway::KEY_PAGE, $this->request->getPost(ApiGateway::KEY_PAGE));

        // bukken id(post)
        //$data = [];
        //$data[ApiGateway::KEY_BUKKEN_ID] = $this->request->getPost(ApiGateway::KEY_BUKKEN_ID);
        //$apiResponse = json_decode($this->apiGateway->post($url, $data));

        // bukken id(get)
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $this->request->getPost(ApiGateway::KEY_BUKKEN_ID));
        $url .= $this->addParam(ApiGateway::KEY_FULLTEXT, $this->request->getPost(ApiGateway::KEY_FULLTEXT));
        $apiResponse = json_decode($this->apiGateway->get($url));

        $this->convertProtocol($apiResponse);

        echo json_encode((array)$apiResponse);

    }

    public function mapkkauthAction(){
        $kkApi = new KkApi();
        $authInfo = $kkApi->getAuthSession();

        echo json_encode((array)$authInfo);


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

        //s_type
        $param .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->request->getPost(ApiGateway::KEY_S_TYPE));

        // ipアドレス
        $param .= $this->addParam(ApiGateway::KEY_USER_IP, $_SERVER["REMOTE_ADDR"]);

        return $param;
    }


    private function convertProtocol($response)
    {

        if(isset($response->content) && $this->request->protcol == 'https' && !is_object($response->content) ){

            $pattern = '~((src|data-src|data-original)=["\'])(https?://)~iU';
            $replacement = '$1https://';
            $response->content = preg_replace($pattern, $replacement, $response->content);
        }
    }

}
