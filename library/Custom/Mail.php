<?php
namespace Library\Custom;

use Laminas\Mime\Mime;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Part as MimePart;

class Mail extends Message
{
    private $_templateParams;
    protected $body;
    protected $subject;
    static protected $_settings;

    const TO_ENCODING = 'ISO-2022-JP';


    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        
        $this->_view = new View();
        $this->_view->setViewPath([storage_path() . '/data/mail']);
        $this->_view->addHelperPath(array('library/Custom/View/Helper/'), '\Library\Custom\View\Helper\\');
        $this->body = null;
        $this->subject = null;
    }

    /**
     * 設定情報を取得する
     *
     */
    static public function getSettings() {
        if (!self::$_settings) {
          // self::$_settings = new Zend_Config_Ini(APPLICATION_PATH . '/configs/mail.ini', APPLICATION_ENV);
          self::$_settings = getConfigs('mail');
        }
        return self::$_settings;
    }

    /** 
     * Toアドレスを設定する
     *
     */
    public function setTos(array $tos) {
        parent::addTo($tos);
    }

    /** 
     * Fromアドレスを設定する
     *
     */
    public function setFrom($email, $name=null) {
		if (!is_null($name)){ 
//			$name = $this->encodeHeader($name);
		}
        parent::setFrom($email, $name);
    }

    /** 
     * 件名を設定する
     *
     */
    public function setSubject($subject) {
		$this->subject = $subject;
    }

    /** 
     * テンプレートのパラメーターを設定する
     *
     */
    public function setBodyFromTemplate($template, array $params) { 
        $this->body = $this->_renderBody( $template, $params );
        
    }

    /** 
     * テンプレートのパラメーターを設定する
     *
     */
    public function setBody($body) { 
        $this->body = $body;
    }

    /** 
     * メールを送信する
     *
     */
    public function send() { 

        $orginalEncoding = mb_internal_encoding();
        $orginalLanguage = mb_language();

        mb_internal_encoding('UTF-8');
        mb_language('uni');

		parent::setSubject($this->subject);
        parent::setBody($this->body);

        $transport = new Sendmail();
        $transport->send($this);

        mb_internal_encoding($orginalEncoding);
        mb_language($orginalLanguage);

    }


    /** 
     * 件名を設定する
     *
     */
    public function getMailSubject() {
		return $this->subject;
    }

    /** 
     * 本文を設定する
     *
     */
    public function getMailBody() {
		return $this->body;
    }


    /** 
     * メールの本文を取得する
     *
     */
    private function _renderBody($template, $params){ 

        if(!is_null($params)){

            // テンプレートのパラメータをセット
            foreach($params as $key=>$val){ 
                $this->_view->$key = $val;
            }
        }
        // レンダリング
        return $this->_view->render($template);
    }


    /**
     * メールヘッダーエンコード
     * @param string $str
     * @return string
     */
    private function encodeHeader($str) {


        // 内部エンコーディングを保持しておく
        $orginalEncoding = mb_internal_encoding();

        // 変換したい文字列のエンコーディングをセット
        mb_internal_encoding(self::TO_ENCODING);

        // エンコーディング実行
        $encodedString =  mb_encode_mimeheader(
            $this->encodeToJis($str), 'iso-2022-jp'
            );

        // エンコーディングに戻す
        mb_internal_encoding($orginalEncoding);

        return $encodedString;
    }
    
    
    /**
     * メールヘッダーエンコード
     * @param string $str
     * @return string
     */
    private function encodeToJis($str) { 
        return mb_convert_encoding($str, 'JIS', 'auto');
    }

    public function setBodyText($html, $charset = null, $encoding = Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($charset === null) {
            $charset = $this->_charset;
        }

        $mp = new MimePart($html);
        $mp->encoding = $encoding;
        $mp->type = Mime::TYPE_HTML;
        $mp->disposition = Mime::DISPOSITION_INLINE;
        $mp->charset = $charset;

        $this->body = $mp;

        return $this;
    }
}
