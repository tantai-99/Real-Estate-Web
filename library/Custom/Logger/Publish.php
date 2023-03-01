<?php
namespace Library\Custom\Logger;

/**
 * 公開処理時のログ
 *
 */
class Publish extends AbstractLogger
{
	const	PUBLISH_NORMALY		= 0	;				// 公開処理の最後にセットされる値
	const	PUBLISH_ABNORMALY	= 9	;				// $this->statusの初期値
	
    static protected $_instance;

    protected $writer;
	protected $logger;
	protected $status	= self::PUBLISH_ABNORMALY	;		// 異常終了検出用

	private $company;

	private $infoMsgTag;
    private $infoRenderMsgTag;

	/** コンストラクタ
	 *
	 */
	public function __construct(){
		$this->logger = \Log::channel('publish');

        $this->renderLogger = \Log::channel('publish_render');


        $this->infoMsgTag=1;
		$this->infoRenderMsgTag=1;
    }
    
    /**
     * 異常検出用の値を変更する
     */
    public function setStatus( $status )
    {
    	$this->status = $status	;
    }
    
    /**
     * 異常検出用の値を取得する
     */
    public function getStatus()
    {
    	return $this->status ;
    }
    
    public function init($hp,$company){
		$this->hp      =$hp;
		$this->company =$company;

		$hpId = ($this->hp) ? $this->hp->id: "";
		$companyId = ($this->company) ? $this->company->id: "";
		$this->suffix  = ", hp_id=".$hpId.", company_id=".$companyId;
        $pid = getmypid();
        if($pid){
            $this->suffix .= ", pid=".$pid;
        }

        $this->infoMsgTag=1;
        $this->infoRenderMsgTag=1;

	}

    public function isMonitorKaiin() {
        if($this->company->publish_notify) {
            return true;
        }
        return false;
    }

    //infoログを出力する
	public function info($message) {
		$this->_info($message);
	}

    //infoログ（レンダリング）を出力する
    public function infoRender($message) {
        $this->_infoRender($message);
    }

    //infoログ（レンダリング）を出力する
    public function infoJspush($message) {
        $this->_infoJspush($message);
    }

    //infoログ（レンダリング）を出力する
    public function infoRenderGetContent($message) {
        if(false){
            $this->_infoRender($message);
        }
    }



    //errorログを出力する
	public function error($message) {
		$this->_error($message);
	}

	private function _info($message) {

		$this->logger->info(sprintf('%02d',$this->infoMsgTag)."-".$message.$this->suffix);
		$this->infoMsgTag++;
	}

    private function _infoRender($message) {

        // 本番環境は監視対象の会員のみ出力する
        if (\App::environment() == "production") {
            if ( !$this->isMonitorKaiin() ){
                return;
            }
        }

        $mTimeArr = explode('.',microtime(true));
        $mTime    = date('H:i:s', $mTimeArr[0]) . '.' .str_pad($mTimeArr[1],4,0);
        $this->renderLogger->info("[ ".$mTime." ] ".sprintf('%02d',$this->infoRenderMsgTag)."-publish-render-".$message.$this->suffix);
        $this->infoRenderMsgTag++;
    }


    private function _infoJspush($message) {

        // 本番環境は監視対象の会員のみ出力する
        if (\App::environment() == "production") {
            if ( !$this->isMonitorKaiin() ){
                return;
            }
        }

        $mTimeArr = explode('.',microtime(true));
        $mTime    = date('H:i:s', $mTimeArr[0]) . '.' .str_pad($mTimeArr[1],4,0);
        $this->renderLogger->info("[ ".$mTime." ] ".$message.$this->suffix);
        //$this->infoRenderMsgTag++;
    }



    //errorログを出力する
	private function _error($message) {

		$this->logger->error($message.$this->suffix);
	}
}
