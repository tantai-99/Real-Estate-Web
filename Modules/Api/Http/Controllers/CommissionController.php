<?php
namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;

class CommissionController extends Controller {

    protected $writer;
    protected $logger;

    public function init($request, $next)
    {
		$this->logger = \Log::channel('commit');
        $this->errors = new \stdClass;
        $this->errors->param_invalid = false;
        $this->errors->auth_invalid = false;
        $company_id = $request->com_id;
        $auth_id    = $request->api_key;

        // 必須パラメータのチェック
        if ( is_null($company_id) || is_null($auth_id) ){
            $this->errors->param_invalid  = true;
            echo json_encode($this->data);
            die();
        }

        // API認証
        if ( !$this->authApi($company_id, $auth_id)){
            $this->errors->auth_invalid  = true;
            echo json_encode($this->data);
           die();
        }
        return $next($request);
    }

    /**
     * API認証
     *
     *
     */
    protected function authApi($company_id, $api_key){

        $model = App::make(CompanyAccountRepositoryInterface::class);
        $rows  = $model->getDataForCompanyId($company_id);
        $row = $rows[0];

        if ($row->api_key == $api_key){
            return true;
        }
        return false;
    }

    private function _info($message) {
        $mTimeArr = explode('.',microtime(true));
        $mTime    = date('H:i:s', $mTimeArr[0]) . '.' .str_pad($mTimeArr[1],4,0);
        $this->logger->info("[ ".$mTime." ] ".$message);
    }

    public function trackingAction(){

        $data = new \stdClass();
        $data->success = true ;
        $data->message   = 'success';
        $data->code   = '201';
        header("Content-Type: application/json; charset=utf-8");
        /*
        $com_id = $request->com_id;
        $http_host = $request->http_host;
        $http_user_agent = $request->http_user_agent;
        $remote_addr = $request->remote_addr;
        $remote_addr_net = $request->remote_addr_net;
        $server_addr = $request->server_addr;
        $server_port = $request->server_port;
        $http_referer = $request->http_referer;
        $access = $request->access;
        */
        $param = $this->_request->getParams();
        unset($param['module']);
        unset($param['controller']);
        unset($param['action']);
        unset($param['api_key']);
        $this->_info($this->showInfo($param));
        echo json_encode($data);
    }

    private function showInfo($param){
        $show = array();
        foreach ($param as $key => $value) {
            $show[] = $key.':'.$value;
        }
        return implode(' - ',$show );
    }

}
