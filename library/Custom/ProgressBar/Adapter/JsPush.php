<?php
namespace Library\Custom\ProgressBar\Adapter;

use Laminas\ProgressBar\Adapter\JsPush as BaseJsPush;

class JsPush extends BaseJsPush {
	
	protected $_pad_length;
    protected $_pushTime;


	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->_pad_length = max(1024, ini_get('output_buffering'));
        $this->_pushTime = 0;
	}


    protected function _outputData($data) {
        \Library\Custom\Logger\Publish::getInstance()->infoJspush("   ##### jsPush:data-push ##");

        echo str_pad($data . '<br />', $this->_pad_length, ' ', STR_PAD_RIGHT) . "\n";
        flush();
        ob_flush();
    }

    public function renderingStart() {

	    // レンダリング処理のクライアント通知（表示）は別途対応になる。
  /*
        $param=[];
        $data = "<script type=\"text/javascript\">parent.progressRenderingStart(".Zend_Json::encode($param).");</script>";
        echo str_pad($data, $this->_pad_length, ' ', STR_PAD_RIGHT) . "\n";
        flush();
        ob_flush();
  */
    }

    public function renderingEnd() {

        // レンダリング処理のクライアント通知（表示）は別途対応になる。
/*
        $param=[];
        $data = "<script type=\"text/javascript\">parent.progressRenderingEnd(".Zend_Json::encode($param).");</script>";
        echo str_pad($data, $this->_pad_length, ' ', STR_PAD_RIGHT) . "\n";
        flush();
        ob_flush();
*/
    }

    // サーバ→クライアントに通信する（無通信状態の軽減のため）
    public function polling() {

	    //３秒間隔未満のアクセスはしないようにしておく。
	    $curTime = microtime(true);
	    if(($curTime - $this->_pushTime) < 3){
            return;
        }
        $this->_pushTime=$curTime;

        \Library\Custom\Logger\Publish::getInstance()->infoJspush("   ##### jsPush:empty-push ##");

        // 空データ送信
        echo str_pad(' ', $this->_pad_length, ' ', STR_PAD_RIGHT) . "\n";
        flush();
        ob_flush();

        // レンダリング処理のクライアント通知（表示）は別途対応になる。
        /*
        $param = [];
        $data = "<script type=\"text/javascript\">parent.progressAccess(".Zend_Json::encode($param).");</script>";
        echo str_pad($data, $this->_pad_length, ' ', STR_PAD_RIGHT) . "\n";
        flush();
        ob_flush();
        */
	}

}