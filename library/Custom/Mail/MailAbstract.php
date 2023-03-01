<?php
namespace Library\Custom\Mail;

use Modules\Api\Http\Form\Contact;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
/**
 * パスワード変更フォーム
 * 
 */

class MailAbstract
{
    /** コンストラクタ
     *
     */
    public function __construct(){
        
        $this->_mail   = new \Library\Custom\Mail();
        $this->_templete = "";
        $this->_subject  = "";
        $this->_params   = array();
        $this->_tos      = array();
        $this->_params   = array();
        $this->_replyTo = null;

        $this->_contactForm = new Contact\Contact();
    }


    /**
     *  会員名（加盟店名）を設定する
     */
    public function setMemberName($name){

        $this->_params += array('memberName'=>$name);
    }

    /**
     *  会員名（加盟店名）を設定する
     */
    public function setInquiryDateTime($InquiryDatetime){

        $this->_params += array('InquiryDatetime'=>$InquiryDatetime);
    }

    /** 
     * Toアドレスを設定する
     */
    public function setTo($mailTo){
        
        $this->_tos[] = $mailTo;
    }

    /** 
     * Toアドレスを設定する
     */
    public function setTos(array $mailTos){

        $this->_tos = array_merge($this->_tos, $mailTos);
    }

    /**
     * Reply-Toアドレスを設定する
     */
    public function setReplyTo($mailReplyTo){
    
    	$this->_replyTo = $mailReplyTo;
    }
    
    /** 
     * Fromアドレスを設定する
     */
    public function setFrom($mailFrom){
        
        $this->_from = $mailFrom;
    }

    /** 
     * 件名を設定する
     */
    public function setSubject($subject){
        
        $this->_subject = $subject;
    }

    /** 
     *お問い合わせパラメータを設定する
     */
    public function setInquiryParams($params){
        $this->_params += array('inquiryParams'=>$params);
    }


    /** ユーザ情報
     *
     */
    public function setUser($userObj){
          $this->_user = $userObj;
    }

    /** 
     * ヘッダーを設定する
     */
    public function setHeader($name, $value){
        $this->_mail->addHeader($name, $value);
    }

    /** 
     * mail_IDを設定する
     */
    public function setMailId($params){
        $this->_params += array('mailId'=>$params);
    }

    /**
     * 送信
     * @param bool $isMake
     */
    public function send($isMake = false){
        if (empty($this->_from) || $isMake) {
            $this->make();
        }
        $this->_mail->send();
    }

    public function make(){
        if ($this->_replyTo) $this->_mail->setReplyTo($this->_replyTo);
        $this->_mail->setTos($this->_tos);
        
        $this->_mail->setFrom($this->_from);
        $this->_mail->setSubject($this->_subject);
        $this->_mail->setBodyFromTemplate($this->_templete, $this->_params);
    }

    /** メールの件名を取得する
     *
     */
    public function getSubject(){
        return $this->_mail->getMailSubject();
    }

    /** メールの本文を取得する
     *
     */
    public function getBody(){
        return $this->_mail->getMailBody();
    }

    /** ラベルを取得する
     *
     */
    protected function geLabels(){
        // お問い合わせ項目のラベルを取得する
        $contactParts = \App::make(HpContactPartsRepositoryInterface::class);
        $srcLabels = $contactParts->getLabels();
        
        // パディング
        $dstLabels = array();
        foreach( $srcLabels as $key=>$val ){
            $dstLabels[$key] =  $this->labelPadding($srcLabels[$key]);
        }

        return $dstLabels;
    }


    protected function labelPadding($label){
        if(mb_strlen($label) >= 10 ){
            return $label;
        }
        return mb_substr($label . str_repeat("　", 10), 0, 10);
    }

    protected function createMailParams($contactItems){
        // メール用に問い合わせ項目を作成
        $inquiryParams = array();
        $content = array();
        $profile = array();
        $order = array();
        foreach( $contactItems as $key=>$items ){

            // お問い合わせアイテムコードを取得する
            $itemCode = $this->_contactForm->getItemCode($items['item_key']);
            if ($itemCode==null){
                continue;
            }

            // お問い合わせ内容
            if ( in_array($itemCode, $this->contentCode) ){
                if(array_key_exists('value',$items)){
                    if(is_array($items['value'])) {
                        $content = $items['value'];
                    }else{
                        $content = array($items['value']);
                    }
                }
                if(array_key_exists('subject_more_item_value',$items)){
                    $content['remarks'] = $items['subject_more_item_value'];
                }
                else if(array_key_exists('request_more_item_value',$items)){
                    $content['remarks'] = $items['request_more_item_value'];
                }

            // プロフィール
            } else if( in_array($itemCode, $this->profileCode) ) {
                if(array_key_exists('value',$items)){
                    $profile[$itemCode] = $this->getPofileItemText($items);
                }

            // 依頼内容
            } else if( in_array($itemCode, $this->orderCode) ) {
                if(array_key_exists('value',$items)){
                    $order[$itemCode] = $this->getPofileItemText($items);
                }
            }
        }

        $inquiryParams['content'] = $content;
        $inquiryParams['profile'] = $profile;
        $inquiryParams['order']   = $order;

        return $inquiryParams;
    }

    protected function getFreeLabel($contactItems,$srchItemCode){        


        // 自由入力項目のみ
        if ($srchItemCode < 100 || $srchItemCode > 109){
            return null;
        }

        $freeLabel = null;
        foreach( $contactItems as $items ){
            if(!array_key_exists('item_key', $items)){
                continue;
            }

            $itemCode = $this->_contactForm->getItemCode($items['item_key']);
            if ($itemCode==null || $itemCode!=$srchItemCode){
                continue;
            }
            $freeLabel = $this->labelPadding($items['label']);

        }
        return $freeLabel;
    }

    protected function getPofileItemText($pofileItem){

        $item=$pofileItem['value'];

        // 複数項目ある場合はスラッシュでつなげる
        if ( is_array($pofileItem['value'])){ 
            $item = "";
            $idx = 0;
            foreach( $pofileItem['value'] as $val ){
                $pref = ($idx == 0) ? "": "／" ;
                $item .= $pref.$val;
                $idx++;
            }
        // 末尾に万円
        }else if ( !empty($pofileItem['value']) &&
                   ($pofileItem['item_key'] == 'property_budget' ||
                    $pofileItem['item_key'] == 'person_own_fund'   ||
                    $pofileItem['item_key'] == 'person_annual_incom' )) {
            $item = $pofileItem['value']."万円";

        // 末尾に人
        }else if ( !empty($pofileItem['value']) && $pofileItem['item_key'] == 'person_number_of_family'){
            $item = $pofileItem['value']."人";

        //  末尾に歳
        }else if ( !empty($pofileItem['value']) && $pofileItem['item_key'] == 'person_age'){
            $item = $pofileItem['value']."歳";

        //  末尾に戸
        }else if ( !empty($pofileItem['value']) && $pofileItem['item_key'] == 'property_number_of_house'){
            $item = $pofileItem['value']."戸";

        }else if ( !empty($pofileItem['value']) && $pofileItem['item_key'] == 'property_age'){
            $item = $pofileItem['value']."年";

        }else if ( !empty($pofileItem['value']) && $pofileItem['item_key'] == 'person_tel'){
            $item = preg_replace('/([0-9]{3})([0-9]{4})([0-9]{1})/', '$1-$2-$3', $pofileItem['value']);

        }
        return $item;
    }

    // #4294 
    protected function convertLabel($label) {
        $split = preg_split('/(.{1})/us', $label, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        $length = count($split);

        if ($length < 9) {
            $label = $label.str_repeat('　', 10 - $length);
        }
        
        return $label;
    }

    protected function convertContent($content) {
        $contents = explode(PHP_EOL, $content);
        $result = [];
        foreach ($contents as $content) {
            $new = preg_split('/(.{200})/us', $content, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
            $rs = implode(PHP_EOL,$new);
            $result[]= $rs;
        }
        return  implode(PHP_EOL,$result);
    }

    protected function convertContentExceptLabel($label, $content) {
        $contents = explode(PHP_EOL, $label."：".$content);

        $result = [];
        foreach ($contents as $content) {
            $new = preg_split('/(.{200})/us', $content, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
            $rs = implode(PHP_EOL,$new);
            $result[]= $rs;
        }
        return str_replace($label."：", '', implode(PHP_EOL,$result));
    }
    // END #4294
}