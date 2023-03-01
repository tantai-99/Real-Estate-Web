<?php

namespace Modules\V1api\Models\BApi;

use Modules\V1api\Services\BApi\Client;

class Code
{
    const URL_FUNC_CODE = '/code/search.json';

    /**
     * @return JSON
     */
    public static function getCode($codes)
    {   
        $obj = Client::getInstance();
        $res = $obj->get(self::URL_FUNC_CODE, $codes);
        // 失敗していたらエラーを投げる
        self::ifFailedThenThrowException($res);
        $res = json_decode($res['content'], true);
        $res = self::editData($res);
        return $res;
    }
    private static function editData($res)
    {
        $return = array();
        foreach($res['code_groups'] as $value1){
            $key1 = $value1['group_nm'];
            foreach($value1['codes'] as $value2){
                $key2 = $value2['cd'];
                $return[$key1][$key2] = $value2['nm'] != '未設定'? $value2['nm']: '';
            }
        }
        return $return;
    }
    private static function ifFailedThenThrowException($res)
    {
        if ($res->getStatusCode() != 200) {
            $logger = Zend_Registry::get('logger');
            $logger->log("<BAPI ERR> レスポンスコードエラー。 status:".$this->status."  message:".$this->message. "　content=" . mb_substr(print_r($this->content, true), 0, 1000), Zend_Log::ERR);
        }
    }
}