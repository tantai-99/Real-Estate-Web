<?php
namespace Modules\V1api\Models\BApi;

use Modules\V1api\Services\BApi\Client;
use Modules\V1api\Services\ServiceUtils;

abstract class AbstractBApi
{
    protected $logger;

    const URL_FUNC_SHIKUGUN      = '/shikugun/search.json';
    const URL_FUNC_SHIKUGUN_LIST = '/shikugun/list.json';
    const URL_FUNC_CHOSON        = '/choson/search.json';
    const URL_FUNC_CHOSON_LIST   = '/choson/list.json';
    const URL_FUNC_ENSEN         = '/ensen/search.json';
    const URL_FUNC_ENSEN_LIST    = '/ensen/list.json';
    const URL_FUNC_EKI           = '/eki/search.json';
    const URL_FUNC_EKI_LIST      = '/eki/list.json';
    const URL_FUNC_BUKKEN_SEARCH = '/kensaku_er_bukken/search.json';
    const URL_FUNC_BUKKEN        = '/bukken/';
    const URL_FUNC_BUKKEN_SPATIAL_SEARCH = '/kensaku_er_bukken/spatial_search.json';
    const URL_FUNC_BUKKEN_COUNT = '/kensaku_er_bukken/count.json';
    const URL_FUNC_BUKKEN_SUGGEST = '/kensaku_er_bukken/suggest.json';

    public function __construct()
    {
        $this->logger = \Log::channel('debug');
    }

    public function getClient() {
        return Client::getInstance();
    }

    // GET用関数
    protected function http_get (
        $url_func, AbstractParams $params, $procName = '')
    {
        $data_url = $params->buildQuery($params);
        
        // ATHOME_HP_DEV-4902: 直近に同一APIの結果があるかを確認
        // 履歴保存対象のAPIを限定する
        switch($url_func) {
            case self::URL_FUNC_ENSEN_LIST:
            case self::URL_FUNC_EKI_LIST:
            case self::URL_FUNC_SHIKUGUN_LIST:
            case self::URL_FUNC_CHOSON_LIST:

                $re_request = ServiceUtils::selectRequest($url_func, $data_url);
                    if(is_null($re_request)) {
                        $res = $this->getClient()->get($url_func, $data_url, $procName);

                        // レスポンスコード:200の時は取得結果を一時テーブルに格納する
                        if($res->getStatusCode() == 200) {
                            $ins_res = ServiceUtils::insertRequest($url_func, $data_url, $res);
                        }
                    } else {
                        // 一時テーブルから取得したことを debug.logに記載する
                        $user_id = isset($_GET["com_id"]) ? $_GET["com_id"] : 'cms'; 
                        $path = $url_func . $data_url;
                        $req = \log::channel('debug');
                        $action_id = $req ? strtoupper(getControllerName()).'-'.strtoupper(getActionName()) . '-'. strtoupper($procName) : '';
                        $this->logger->debug("<BAPI> (From Variable). user=${user_id} action=${action_id} URL=${path}");
                        $res = $re_request;
                    }
                    break;

            default:
               $res = $this->getClient()->get($url_func, $data_url, $procName);
               break;
        }

        // 失敗していたらエラーを投げる
        $res->ifFailedThenThrowException();

        $result = json_decode($res['content'], true);

        return $result;
    }

    // // @TODO POST用関数
    // protected function http_post ($url_func, $data, $procName = '')
    // {
    //     return $this->getClient()->post($url_func, $data);
    // }
}
