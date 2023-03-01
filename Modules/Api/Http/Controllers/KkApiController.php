<?php
namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Traits\JsonResponse;

class KkApiController extends Controller {

    use JsonResponse;
    const TIMEOUT = 20;
    protected $_companyId;

    public function init($request, $next)
    {

        $this->errors = new \stdClass;
        $this->errors->param_invalid = false;
        $this->errors->auth_invalid = false;

        $company_id = $request->com_id;
        $auth_id    = $request->api_key;

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

        $this->_companyId = $company_id;
        return $next($request);


    }

	public function getAuthSession() {

	    if( $this->errors->param_invalid || $this->errors->auth_invalid ){
            return $this->error('advans_auth_invalid');
        }

        $data = $this->_getAuthSession();
        return response()->json($data);
	}

    /* 国際航業APIへのログイン
     *
     *  ・暗号化前の平文文字列の書式：
     *	 [clientid]:[アクセス日時]:[atbbユーザーID]:[IV値]:
     *	 ＃例）「athome:1444729101:atbb000001:HPsLdtqw:」
     *
     *  ・暗号化後の文字列書式：
     *	 [iv値(8文字)][平文文字列をblowfish CBCモードで暗号化した結果]
     *	 ＃共有キーを使ってblowfish CBCモードで平文文字列を暗号化し、
     *	   暗号化されたデータとIV値をurlsafe base64エンコードした文字列を指定。
     *
     */
    private function _getAuthSession() {

        $urlBase = config('environment.kk_api.api.url');
        $authUrl = config('environment.kk_api.auth.url');

        // 認証情報
        $clientId    = 'athome2';
        $accessTime  = time();
        $iv          = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8); // 8桁
        //$usrid       = 'atbb000001';       //atbb
        $usrid       = str_pad($this->_companyId, 10, "0", STR_PAD_LEFT);

        $key         = 'TEMBRVJGFQBPTQXE'; //
        $authPlain   = $clientId.':'.$accessTime.':'.$usrid.':'.$iv.':';
        $authPlain .= str_repeat(chr(0), (8 - ( strlen($authPlain) % 8)) % 8);
        // $authEncoded = mcrypt_encrypt( MCRYPT_BLOWFISH, $key, $authPlain, MCRYPT_MODE_CBC, $iv );
        $authEncoded = openssl_encrypt($authPlain, 'BF-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        $authEncoded = base64_encode($iv.$authEncoded);
        $authEncoded = (str_replace(array('/','+','='),array('_','-','.'),$authEncoded));
        $loginurl = $authUrl.'?client='.$clientId.'&auth='.$authEncoded.'&userid='.$usrid;

        $respons = $this->curl($loginurl);
        $respons = json_decode($respons);
        $respons->url_base = $urlBase;

        $data = [];
        $data['success'] = ($respons->status == 'success') ? true : false ;
        $data['sessionid'] = $respons->sessionid;
        $data['url_base'] = $respons->url_base;
        $data['userid'] = $usrid;

        return $data;
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

    /**
     * API認証
     *
     *
     */
    protected function authApi($company_id, $api_key){

        $model = \App::make(CompanyAccountRepositoryInterface::class);
        $rows  = $model->getDataForCompanyId($company_id);
        $row = $rows[0];

        if ($row->api_key == $api_key){
            return true;
        }
        return false;
    }

}
