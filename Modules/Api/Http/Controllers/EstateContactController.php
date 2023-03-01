<?php
namespace Modules\Api\Http\Controllers;

use App\Traits\JsonResponse;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\ContactLog\ContactLogRepositoryInterface;
use App\Repositories\SpamBlock\SpamBlockRepositoryInterface;
use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use Library\Custom;

class EstateContactController extends ContactAbstractController
{

    use JsonResponse;
	// 問い合わせ設定
	private $contact_ini;
	
    public function __construct()
    {
       parent::__construct(); 
       //設定系の情報取得
       $this->contact_ini = getConfigs('api.contact');
    }


    /* バリデーション
     *
     */
    public function validateForm() {
        $apiData = $this->_params['data'];

        $this->data['errors'] = array();
        if ( !$this->_contactForm->isValid($apiData) ){ 
            foreach ($this->_contactForm->getMessages() as $key => $msgs) {
                foreach ($msgs as $msg) {
                    $this->data['errors'][$key][] = $msg;
                }
            }
        }
        return $this->success($this->data);
    }


    /* お問い合わせメールを送信する
     *
     *  (1) 加盟店へお問い合わせメールを送信する
     *  (2) 顧客管理システムにメールを送信する
     *  (3) お問いわせログを登録する
     *
     */
    public function sendInquiry(){
        try {

            $this->errors = array();
            $params = $this->_params;
            $apiData = $params['data'];

            $company    = \App::make(CompanyRepositoryInterface::class)->getDataForId($this->_company->companyId);
            $associatedCompanyHp = \App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCompanyId($this->_company->companyId);

            if(!empty($apiData['special_id'])){
                $spFilename=$apiData['special_id'];
                $apiData['special_id']=$this->getSpecialId($company,$spFilename);
            }

            // お問い合わせ日時
            $this->_inquiryDatetime = date('Y年m月d日 H時m分');

            // 問い合わせをする物件毎にメールを送信する
            $estateContact = new Custom\Estate\Contact();
            $estateIds = $apiData['estateInfo']['estateIds'];
            foreach ( $estateIds as $estateId ) {

                $mailTos = [];
                // 物件データ
                $estateData = $estateContact->getEstateData($company, $apiData, $estateId);

                // 物件を保持する会員宛てにお問い合わせメールを作成する
                $requestNo = date('YmdHms').substr(explode(".", microtime(true))[1], 0, 3);
                $bukkenNo = $estateData['bukken']['display_model']['bukken_no'];
                $this->_mailId = 'NHP:0500'.$requestNo.'0'.$bukkenNo;
                $mail = $this->makeEstateCompanyMail($apiData, $estateData);

                $receivedMail = '';
                $receiveTel = '';
                foreach($apiData['contactItems'] as $contactItem){
                    switch($contactItem['label']){
                        case 'メール':
                            $receivedMail = $contactItem['value'];
                            break;
                        case '電話番号':
                            $receiveTel = $contactItem['value'];
                            break;
                    }
                }
                $mailId = 'NHP:0000'.$requestNo.'0'.$bukkenNo;
                //迷惑メールはメール送信及びお問い合わせカウントしない
                
                $contactLog = \App::make(ContactLogRepositoryInterface::class);
                if (\App::make(SpamBlockRepositoryInterface::class)->isSpamContact($this->_company->companyId,$receivedMail,$receiveTel)){
                    // お問い合わせログを保持する（メール宛先はnull）
                    if ($apiData['site_type'] == 'public'){
                        $contactLog->saveLog($apiData['page_type_code'], null, $this->_subject, $this->_body, $associatedCompanyHp->current_hp_id, $company['id']);
                    }
                    return $this->success([]);
                } else {
                    // メール送信
                    $mail->send();
                    // お問い合わせログを保持する
                    if ($apiData['site_type'] == 'public'){
                        $hankyoReceiveMail = $estateData['kaiinInfo']->contact['hankyoReceiveMail'];
                        $mailTos = array($hankyoReceiveMail);

                        $contactLog->saveLog($apiData['page_type_code'], $mailTos, $this->_subject, $this->_body, $associatedCompanyHp->current_hp_id, $company['id'], $mailId, $apiData['user_id'], $apiData['hankyo_plus_use_flg']);
                    }
                }
                // 顧客管理システム宛てにお問い合わせメールを送信する（契約者のみ対象）
                $this->sendToCrmCompany($apiData, $estateData);

                // 問い合わせ件数を更新する
                if ($apiData['site_type']=='public') {
                    $contactCount = \App::make(EstateContactCountRepositoryInterface::class);
                    $contactCount->saveCount($company,$apiData, $estateData);
                }
            }

            // 自動返信メールを送信する
            $formatFrom = $this->contact_ini->contact->mail->format->from;
            // $this->sendReplyMail($apiData, $formatFrom);

        }
        catch (\Exception $e) {
            $mail = $mailTos;
            $this->makeErrorMail($apiData, $company, $mail);
            error_log(print_r($e->getMessage(),true));
            error_log(print_r('Request parameters: ' . http_build_query($apiData),true));
            exit;
        }
        return $this->success([]);
    }

    // 物件を保持する会員宛てにお問い合わせメールを送信する
    private function makeEstateCompanyMail($apiData,$estateData)
    {
            $hankyoReceiveMail = $estateData['kaiinInfo']->contact['hankyoReceiveMail'];
            $mailTos = explode(",", $hankyoReceiveMail);

            // アドバンス送信専用アドレス
            $formatFrom = $this->contact_ini->contact->mail->format->from;

            // 加盟店宛てにお問い合わせメールを送信する
            try {

                $mail= $this->factoryMailToCompany($apiData);

                $mail->setTos($mailTos);
                
                //replyToは、問い合わせフォームのエンドユーザメールアドレス
                $replyMailTo = $this->getReplyMaiTo($apiData['contactItems']);
                if (!empty($replyMailTo)) $mail->setReplyTo($replyMailTo);

                //subject
                $mail->setSubject($apiData['contact_mail']['subject']);
                
                //fromアドレスはアドバンス送信専用アドレスとする
                $mail->setFrom($formatFrom); 
                
                // 会員名
                $mail->setMemberName($estateData['kaiinInfo']->seikiShogo['shogoName']);

                // メール受付日時
                $mail->setInquiryDateTime($this->_inquiryDatetime); 

                // mail_ID
                $mail->setMailId($this->_mailId);

                // お問い合わせ項目    
                $inquiryParams = array();
                $inquiryParams['estateData']   = $estateData;
                $inquiryParams['contactItems'] = $apiData['contactItems'];
                $inquiryParams['peripheral_flg'] = $apiData['peripheral_flg']; // #4294
                $mail->setInquiryParams($inquiryParams);

                // メール送信
                $mail->make();
                // ログ用に送信内容を取得する
                $this->_subject = $mail->getSubject();
                $this->_body    = $mail->getBody();

                return $mail;
            }
            catch (Exception $e) {
                error_log(print_r($e->getMessage(),1));
                return $this->error(['error' => 'メール送信に失敗しました。']);
            }
    }

    /**
     * 物件を保持する会員宛の顧客管理システム宛てにお問い合わせメールを送信する
     *
     * @param array $apiData
     * @param array $estateData
     */
    private function sendToCrmCompany($apiData, $estateData)
    {
        $kaiNo = $estateData['kaiinInfo']->kaiinNo;
        $hankyoReceiveMail = $estateData['kaiinInfo']->contact['hankyoReceiveMail'];
        $mailTos = explode(",", $hankyoReceiveMail);

        // 顧客管理システム宛てにお問い合わせメールを送信する
        // ・本番サイトからのみ
        // ・顧客管理システムを使っている加盟店のみ
        if ( $apiData['site_type']=='public' ) { 

            $url = $this->contact_ini->contact->api->KokyakuKanriKeiyaku->url;


            //テスト系は自身のスタブを見に行く
            if ( \App::environment() == "development" || \App::environment() == "local" ) $url = "http://". $_SERVER['HTTP_HOST'] . $url;

            //顧客管理情報を取得する
            $uri = $url ."/". $kaiNo;
            $kokyaku_json = @file_get_contents($uri);

            if($kokyaku_json == false) {
                $message = "error [member_no:". $kaiNo ." / message:CustomerManagementSystem API]";
                //$log->info($message, Zend_Log::NOTICE);
                return $this->error(['error' => 'メール送信に失敗しました。']);
            }else{
                $kokyaku_arr = @json_decode($kokyaku_json, true);
                //APIからのerrorsを設定
                if(isset($kokyaku_arr['errors']) && count($kokyaku_arr['errors']) > 0) {
                    $message = "error[member_no:". $kaiNo ." / message:". json_encode($kokyaku_arr['errors']) ."]";
                    //$log->info($message, Zend_Log::NOTICE);
                    return $this->error(['error' => 'メール送信に失敗しました。']);
                }
                //APIからのwarningsを設定
                if(isset($kokyaku_arr['warnings']) && count($kokyaku_arr['warnings']) > 0) {
                    $message = "error[member_no:". $kaiNo ." / message:". json_encode($kokyaku_arr['warnings']) ."]";
                    //$log->info($message, Zend_Log::NOTICE);
                    return $this->error(['error' => 'メール送信に失敗しました。']);
                }

                //送信
                if ($this->isValidCrmAccount($kokyaku_arr)) {
                    try {
                        $mail= $this->factoryMailToCrm($apiData);

                        //契約管理APIから取得した物件を所有する会員のメールアドレス
                        $mail->setTo($kokyaku_arr['model']['mailBoxAddress']);

                        //subject
                        $mail->setSubject($apiData['contact_mail']['subject']);

                        //物件を所有する会員のhankyoReceiveMailとする
                        $mail->setFrom($mailTos[0]);   

                        // 会員名
                        $mail->setMemberName($estateData['kaiinInfo']->seikiShogo['shogoName']);

                        // メール受付日時
                        $mail->setInquiryDateTime($this->_inquiryDatetime);       

                        // お問い合わせ項目    
                        $inquiryParams = array();
                        $inquiryParams['estateData']   = $estateData;
                        $inquiryParams['contactItems'] = $apiData['contactItems'];
                        $inquiryParams['peripheral_flg'] = $apiData['peripheral_flg']; // #4294
                        $mail->setInquiryParamsCrm($inquiryParams);
                        $mail->setHeader('mailid', $this->_mailId);

                        // メール送信
                        $mail->send(true);

                    } catch (Exception $e) {
                        return $this->error(['error' => 'メール送信に失敗しました。']);
                    }
                }
            }
        }
    }

    private function getSpecialId($company,$specialFileName){
        $settingTable = \App::make(HpEstateSettingRepositoryInterface::class);
        $specialTable = \App::make(SpecialEstateRepositoryInterface::class);
        $hp = $company->getCurrentHp();
        $pubSetting = $settingTable->getSettingForPublic($hp->id);
        $special = $specialTable->fetchSpecialByFilename($hp->id, $pubSetting->id, $specialFileName);
        if ($special == null){
            return "";
        }
        return $special->id;
    }

    private function makeErrorMail($apiData, $company, $mail = [])
    {
        $formatFrom = env('ERROR_MAIL_FROM');
        $tile = $apiData['contact_mail']['subject'];
        $subject="反響メールエラー";
        $mailTos = env('ERROR_MAIL_TO');
        $sender = '';
        foreach($apiData['contactItems'] as $contactItem){
            switch($contactItem['label']){
                case 'メール':
                    $sender = $contactItem['value'];
                    break;
            }
        }
        $sendTo = implode(",", $mail);
        $bodyText = '';
        if (isset($this->body)) {
            $bodyText = $this->body;
        }
        $companyId = $company['member_no'];
        $companyName = $company['company_name'];
        $body =<<<BODY
            会員番号、商号：${companyId}、${companyName}
            送信元：${sender}
            宛先：${sendTo}
            件名：${tile}
            メール内容：${bodyText}
            BODY;

        if (is_null($formatFrom) ||  $formatFrom == '' || is_null($mailTos) || $mailTos == '') {
            return;
        }
        try {

            $mail = new Custom\Mail();
            $mail->setBody($body);
            $mail->setFrom($formatFrom,'');
            $mail->addTo($mailTos);
            $mail->setSubject($subject);
            $mail->send();

            return $this->success([]);
        }
        catch (Exception $e) {
            error_log(print_r($e->getMessage(),1));
            return $this->error(['error' => 'メール送信に失敗しました。']);
        }
    }
}
