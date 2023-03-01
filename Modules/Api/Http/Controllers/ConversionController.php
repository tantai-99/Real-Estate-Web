<?php
namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\Conversion\ConversionRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Traits\JsonResponse;

class ConversionController extends Controller {

    use JsonResponse;
    const TIMEOUT = 20;

    public function init($request, $next)
    {
        $this->errors = new \stdClass;
        $this->errors->param_invalid = false;
        $this->errors->auth_invalid = false;

        $this->_params =  $request->all();

        $company_id = $this->_params['com_id'];
        $auth_id    = $this->_params['api_key'];

        // 必須パラメータのチェック
        if ( is_null($company_id) || is_null($auth_id) ){
            $this->errors->param_invalid  = true;
            return $next($request);
        }

        // API認証
        if ( !$this->authApi($company_id, $auth_id)){
            $this->errors->auth_invalid  = true;
            return $next($request);
        }

        $this->_company = new \stdClass();
        $this->_company->companyId = $company_id;

        return $next($request);
    }

    // 電話番号タップコンバージョン
	public function telTap(Request $request) {

	    if( $this->errors->param_invalid || $this->errors->auth_invalid ){
           return $this->error('advans_auth_invalid');
        }

        // ・会員サイトのみ（テストサイト、代行作成サイトは不要）
        if ($this->_params['publish']==1){

            try {
                DB::beginTransaction();

                // 電話番号タップコンバージョン
                $contactCount = App::make(ConversionRepositoryInterface::class);
                $contactCount->saveTeltap(
                    $this->_params['page_url'],
                    $this->_params['media'],
                    $this->_params['user_ip'],
                    $this->getUserAgent(),
                    $this->_params['com_id']
                );

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }


        return $this->success([]);
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

    private static function getUserAgent() {
        $headers = getallheaders();
        $user_agent = isset($headers['HTTP_USER_AGENT']) ?
            $headers['HTTP_USER_AGENT'] :
            (isset($headers['User-Agent']) ? $headers['User-Agent'] : null);
        return $user_agent;
    }

}
