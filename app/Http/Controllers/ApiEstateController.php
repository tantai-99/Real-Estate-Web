<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\V1api\Services\BApi\Client;
use Library\Custom\Registry;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Estate\Setting\SearchFilter\Special as SearchFilterSpecial;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Estate\Setting\SearchFilter\Second;
use App\Traits\JsonResponse;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use Library\Custom\Model\Estate\ApiGateway;
use Library\Custom\Controller\Action\InitializedCompany;

class ApiEstateController extends InitializedCompany
{

    use JsonResponse;
    const TIMEOUT = 20;
    protected $com_id;
    protected $api_key;
    protected $domain;

    public function init($request, $next)
    {
        return parent::init($request, $next);
    }

    public function shikugun(Request $request)
    {
        $ken_cd = $request->ken_cd;
        if (!$ken_cd) {
            throw new \Exception('パラメータ不正');
        }
        $params = [
            'media' => 'pc',
            'grouping' => 'locate_cd',
            'ken_cd' => $ken_cd
        ];
        $client = new Client();
        $response = $client->get('/shikugun/list.json', $params);

        $response->ifFailedThenThrowException();
        return $this->success(['shikuguns' => $response->data['shikuguns']]);
    }

    public function choson(Request $request)
    {
        $shikugun_cd = $request->shikugun_cd;
        if (!$shikugun_cd) {
            throw new \Exception('パラメータ不正');
        }
        $params = [
            'shikugun_cd' => $shikugun_cd,
            'oaza_fl' => '1',
            'choaza_fl' => '1',
            'kana_nm_sort_fl' => '1',
        ];
        $client = new Client();
        $response = $client->get('/choson/list.json', $params);

        $response->ifFailedThenThrowException();
        return $this->success(['shikuguns' => $response->data['shikuguns']]);
    }

    public function ensen(Request $request)
    {
        $ken_cd = $request->ken_cd;
        if (!$ken_cd) {
            throw new \Exception('パラメータ不正');
        }

        $params = [
            'media' => 'pc',
            'grouping' => '1',
            'ken_cd' => $ken_cd
        ];
        $client = new Client();
        $response = $client->get('/ensen/list.json', $params);

        $response->ifFailedThenThrowException();
        return $this->success(['ensens' => $response->data['ensens']]);
    }

    public function eki(Request $request)
    {
        $ensen_cd = $request->ensen_cd;
        $ken_cd = $request->ken_cd;
        if (!$ensen_cd) {
            throw new \Exception('パラメータ不正');
        }
        $params = [
            'media' => 'pc',
            'ensen_cd' => $ensen_cd
        ];
        if ($ken_cd) {
            $params['ken_cd'] = $ken_cd;
        }
        $client = new Client();
        $response = $client->get('/eki/list.json', $params);

        $response->ifFailedThenThrowException();
        return $this->success(['ensens' => $response->data['ensens']]);
    }

    public function specialSearchFilter(Request $request)
    {
        $data = [];
        $estateTypes = $request->estate_type;
        $estateTypes = explode(',', $estateTypes);
        foreach ($estateTypes as $type) {
            if (!TypeList::getInstance()->get($type)) {
                throw new \Exception('パラメータ不正');
            }
        }
        $searchFilter = new SearchFilterSpecial();
        $searchFilter->loadEnables($estateTypes);
        $searchFilter->asMaster();
        $data['categories'] = $searchFilter->categories;
        return $this->success($data);
    }

    public function secondSearchFilter(Request $request)
    {

        $estateClass = $request->estate_class;
        if (!ClassList::getInstance()->get($estateClass)) {
            throw new \Exception('パラメータ不正');
        }

        $searchFilter = new Second();
        $searchFilter->loadEnables($estateClass);
        $searchFilter->asMaster();
        $data['estate_types'] = $searchFilter->estate_types;
        $data['estateTypeMaster'] = TypeList::getInstance()->getByClass($estateClass);
        return $this->success($data);
    }

    public function initApi() {
        if (!$this->com_id) {
            $hp = getUser()->getCurrentHp();
            $company = App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hp->id);
            $this->com_id = $company->id;
        }
        if (!$this->api_key) {
            $comAcount = App::make(CompanyAccountRepositoryInterface::class)
            ->fetchRow([['company_id', $this->com_id]]);
            $this->api_key = $comAcount->api_key;
        }
        if (!$this->domain) {
            $this->domain = getConfigs('api')->api->domain ;
        }
    }
    
    public function houseAll(Request $request) {
        $this->initApi();
        $apiGateway = ApiGateway::getInstance();
        $url = 'http://'.$this->domain.'/v1api/house/house-all';
        $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_COM_ID'), $this->com_id, true);
        $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_API_KEY'), $this->api_key);
        $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_CMS_SPECIAL'), true);
        $publish = 1;
        if (getUser()->isCreator() || getUser()->isAgency()) {
            $publish = 3;
        }
        $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_PUBLISH'), $publish);
        if ($request->estateClass) {
            $types = $request->estateClass ;

            foreach ($types as $type) {
                $type_ct[] = TypeList::getInstance()->getUrl($type);
            }
            $type_ct = implode(',', $type_ct);
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_SHUMOKU'), $type_ct);
        }
        $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_PER_PAGE'), 10);
        if ($page = $request->page) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_PAGE'), $page);
        }
        if ($sort = $request->sort) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_SORT'), $sort);
        }
        if ($housesNo = $request->houses_no) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_BUKKEN_NO'), $housesNo);
        }
        if ($isModal = $request->isModal) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_IS_MODAL'), $isModal);
        }
        if ($isConfirm = $request->isConfirm) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_IS_CONFIRM'), $isConfirm);
        }
        if ($isCondition = $request->isCondition) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_IS_CONDITION'), $isCondition);
        }
        $data = [];
        if ($housesId = $request->houses_id) {
            $data[config('constants.api_gateway.KEY_BUKKEN_ID')] = implode(',', $housesId);
            // $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_BUKKEN_ID'), $housesId);
        }
        if ($setting = $request->setting) {
            $data[config('constants.api_gateway.KEY_SETTING')] = json_encode($setting);
            // $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_SETTING'), json_encode($setting));
        }
        if ($linkPage = $request->link_page) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_LINK_PAGE'), $linkPage);
        }
        if ($isTitle = $request->is_title) {
            $url .= $apiGateway->addParam(config('constants.api_gateway.KEY_IS_TITLE'), $isTitle);
        }
        $response = json_decode($apiGateway->post($url, $data));
        $results = [];
        $results['data'] = [];
        if (!$response->success) {
            $results['success'] = false;
            $results['error'] = 'システムエラーが発生しました。';
            $results['exception'] = $response->exception;
        } else {
            if (is_null($response->content) || is_null($response->info)) {
                $results['success'] = false;
                $results['error'] = 'システムエラーが発生しました。';
            } else {
                $results['success'] = true;
                $results['data']['content'] = $response->content;
                $results['data']['info'] = $response->info;
            }
        }

        $results['data']['apiUrl'] = $url;
        return response()->json($results);
    }
}
