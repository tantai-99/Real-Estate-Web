<?php
namespace Modules\V1api\Services\BApi;

use Modules\V1api\Exceptions;

class Response extends \ArrayObject {
	protected $logger;
	
    public function __construct($array) {
        parent::__construct($array, \ArrayObject::ARRAY_AS_PROPS);
        
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
    	return $this->status;
    }
    
    /**
     * ステータスコードとレスポンスのjson_decode結果を検証する
     * 物件APIの仕様上、この関数がtrueを返す場合もエラーメッセージが付与されている可能性があります
     * @return boolean
     */
    public function isSuccess() {
        if (! empty($this->getErrors())) {
        	$this->logger->warning("<BAPI WARN> 警告レスポンス　content=" . mb_substr(print_r($this->getErrorMessages(), true), 0, 1000));
        }

        $isValidData = true;

        if (empty($this->data)) {
            $isValidData = false;
        } else {
            if (isset($this->data['bukkens']) ||
                    isset($this->data['data_model']) ||
                    isset($this->data['display_model']) ||
                    isset($this->data['facets']) ||
                    isset($this->data['ensens']) ||
                    isset($this->data['shikuguns']) ||
                    isset($this->data['suggestions'])) {
                $isValidData = true;
            } else {
                $this->logger->error("<BAPI ERR> 想定外のレスポンス　content=" . mb_substr(print_r($this->content, true), 0, 1000));
                $isValidData = false;
            }
        }
        if ($this->getStatusCode() != 200) {
        	$this->logger->error("<BAPI ERR> レスポンスコードエラー。 status:".$this->status."  message:".$this->message. "　content=" . mb_substr(print_r($this->content, true), 0, 1000));
        	$isValidData = false;
        }
        return $isValidData;
    }
    
    /**
     * エラーの場合例外を投げる
     * @throws Exception
     */
    public function ifFailedThenThrowException() {
    	if ( strpos( @$this->data[ 'errors' ][ 0 ][ 'message' ], '対象物件が存在しません' ) === 0 )
    	{	// ATHOME_HP_DEV-3576 システムエラー表示ではなくページがありません画面を出す対応
    		return ;
    	}
        if (!$this->isSuccess()) {
        	$msg = '物件APIとの通信に失敗しました。 status:'.$this->getStatusCode().' message:'.implode(', ', $this->getErrorMessages());
        	throw new Exceptions\BApi(Exceptions\BApi::DEFAULT_ERR, $msg);
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
}