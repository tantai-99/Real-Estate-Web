<?php
namespace Library\Custom\Kaiin\Service;

use ArrayObject;
use Modules\V1api\Exceptions;

class Response extends ArrayObject {
    protected $logger;

    public function __construct($array) {
        $array['original_headers'] = $array['headers'];
        $array['headers'] = $this->_parseHeaders($array['headers']);
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);

        $this->logger = \Log::channel('debug');
        if ($this->content) {
            $this->data = @json_decode($this->content, true);
        }
        else {
            $this->data = null;
        }
    }
    
    /**
     * レスポンスのステータスコードを取得する
     * @return int
     */
    public function getStatusCode() {
        return isset($this->headers['response_code']) ? $this->headers['response_code']: null;
    }
    
    /**
     * ステータスコードとレスポンスのjson_decode結果を検証する
     * 物件APIの仕様上、この関数がtrueを返す場合もエラーメッセージが付与されている可能性があります
     * @return boolean
     */
    public function isSuccess() {
        $isValidData = true;
        if ($this->isBreak) {
            if (empty($this->data)) {
                $isValidData = false;
            } else {
                if (isset($this->data['model'])) {
                    $isValidData = true;
                } else {
                    $this->logger->error("<KAPI ERR> 想定外のレスポンス　content=" . mb_substr(print_r($this->data, true), 0, 1000));
                    $isValidData = false;
                }
            }
        }
        return $isValidData && $this->getStatusCode() == 200;
    }
    
    /**
     * エラーの場合例外を投げる
     * @throws Exception
     */
    public function ifFailedThenThrowException() {
        if (!$this->isSuccess()) {
            $msg = '会員APIとの通信に失敗しました。 status:'.$this->getStatusCode().' message:'.implode(', ', $this->getErrorMessages());
            throw new Exceptions\KApi(Exceptions\KApi::DEFAULT_ERR, $msg);
        }
    }
    
    /**
     * エラーメッセージ配列を取得する
     * @return array
     */
    public function getErrors() {
        return isset($this->data['errors'])?$this->data['errors']:null;
    }
    
    public function getErrorMessages() {
        $result = [];
        $errors = $this->getErrors();
        if ($errors) {
            foreach ($errors as $error) {
                $result[] = $error['message'];
            }
        }
        return $result;
    }
    
    protected function _parseHeaders( $headers ) {
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ strtolower(trim($t[0])) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['response_code'] = intval($out[1]);
            }
        }
        return $head;
    }
}