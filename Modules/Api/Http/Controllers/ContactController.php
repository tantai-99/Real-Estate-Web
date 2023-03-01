<?php
namespace Modules\Api\Http\Controllers;

use App\Traits\JsonResponse;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\ContactLog\ContactLogRepositoryInterface;
use App\Repositories\SpamBlock\SpamBlockRepositoryInterface;
use App\Repositories\ContactCount\ContactCountRepositoryInterface;

class ContactController extends ContactAbstractController
{
    use JsonResponse; 

    /* バリデーション
     *
     */
    public function validateForm() {
        $apiData = $this->_params['data'];

        $this->data['errors'] = array();
        if (!$this->_contactForm->isValid($apiData)) {
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

        $this->data['errors'] = array();
        $params = $this->_params;
        $apiData = $params['data'];

        $company    = \App::make(CompanyRepositoryInterface::class)->getDataForId($this->_company->companyId);
        $associatedCompanyHp = \App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCompanyId($this->_company->companyId);

        // お問い合わせ日時
        $inquiryDatetime = date('Y年m月d日 H時m分');

        //設定系の情報取得
        $contact_ini = getConfigs('api.contact');
        // アドバンス送信専用アドレス
        $formatFrom = $contact_ini->contact->mail->format->from;
        $subject="";
        $body="";
        $mailTos = $this->getMailTos($apiData);

        // 加盟店宛てにお問い合わせメールを送信する
        try {
            $mail= $this->factoryMailToCompany($apiData);
            $mail->setTos($mailTos);
            $mail->setSubject($apiData['contact_mail']['subject']);
            $mail->setFrom($formatFrom);//fromアドレスはアドバンス送信専用アドレスとする
            $replyMailTo = $this->getReplyMaiTo($apiData['contactItems']);
            if (!empty($replyMailTo)) $mail->setReplyTo($replyMailTo);
            $mail->setMemberName($company['member_name']);      // 加盟店名
            $mail->setInquiryDateTime($inquiryDatetime);        // メール受付日時
            $mail->setInquiryParams($apiData['contactItems']);  // お問い合わせ項目
            $mail->make();

            // ログ用に送信内容を取得する
            $subject = $mail->getSubject();
            $body    = $mail->getBody();

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
            //迷惑メールはメール送信及びお問い合わせカウントしない
            $contactLog = \App::make(ContactLogRepositoryInterface::class);
            if (\App::make(SpamBlockRepositoryInterface::class)->isSpamContact($this->_company->companyId,$receivedMail,$receiveTel)){
                // お問い合わせログを保持する（メール宛先はnull）
                if ($apiData['site_type'] == 'public') {
                    $contactLog->saveLog($apiData['page_type_code'], null, $subject, $body, $associatedCompanyHp->current_hp_id, $company['id']);
                }
                return;
            } else {
                // お問い合わせログを保持する
                if ($apiData['site_type'] == 'public'){
                    $contactLog->saveLog($apiData['page_type_code'], $mailTos, $subject, $body, $associatedCompanyHp->current_hp_id, $company['id']);
                }
            }

            $mail->send();
            if ($apiData['site_type'] == 'public'){
                // お問い合わせカウントを更新する
                $contactCount = \App::make(ContactCountRepositoryInterface::class);
                $contactCount->saveCount($apiData['page_type_code'], $company['id'], $apiData );
            }
        } catch (\Exception $e) {
            return $this->error(['error' => 'メール送信に失敗しました。']);
        }


        // 顧客管理システム宛てにお問い合わせメールを送信する
        // ・本番サイトからのみ
        // ・顧客管理システムを使っている加盟店のみ
        if ( $apiData['site_type']=='public' ) { 
        	
        	$url = $contact_ini->contact->api->KokyakuKanriKeiyaku->url;
        	 
            //テスト系は自身のスタブを見に行く
            if ( \App::environment() == "development" || \App::environment() == "local" ) $url = "http://". $_SERVER['HTTP_HOST'] . $url;

            //顧客管理情報を取得する
            $uri = $url ."/". $company['member_no'];
            $kokyaku_json = @file_get_contents($uri);
            if ($kokyaku_json == false) {
                $message = "error [member_no:". $company['member_no'] ." / message:CustomerManagementSystem API]";
                return $this->error(['error' => 'メール送信に失敗しました。']);
            } else {
                $kokyaku_arr = @json_decode($kokyaku_json, true);
                //APIからのerrorsを設定
                if(isset($kokyaku_arr['errors']) && count($kokyaku_arr['errors']) > 0) {
                    $message = "error[member_no:". $company['member_no'] ." / message:". json_encode($kokyaku_arr['errors']) ."]";
                    return $this->error(['error' => 'メール送信に失敗しました。']);
                }
                //APIからのwarningsを設定
                if(isset($kokyaku_arr['warnings']) && count($kokyaku_arr['warnings']) > 0) {
                    $message = "error[member_no:". $company['member_no'] ." / message:". json_encode($kokyaku_arr['warnings']) ."]";
                    return $this->error(['error' => 'メール送信に失敗しました。']);
                }

                //送信
                if ($this->isValidCrmAccount($kokyaku_arr)) {
                    try {
                        $mail= $this->factoryMailToCrm($apiData);
                        $mail->setTo($kokyaku_arr['model']['mailBoxAddress']);    //契約管理APIから取得したメールアドレス
                        $mail->setSubject($apiData['contact_mail']['subject']);
                        $mail->setFrom($mailTos[0]);                       //fromアドレスはCMSで登録した加盟店のToアドレスの先頭とする仕様
                        $mail->setMemberName($company['member_name']);     // 加盟店名
                        $mail->setInquiryDateTime($inquiryDatetime);       // メール受付日時
                        $mail->setInquiryParams($apiData['contactItems']); // お問い合わせ項目
                        $mail->send(true);
                    } catch (\Exception $e) {
                        return $this->error(['error' => 'メール送信に失敗しました。']);
                    }
                }
            }
        }

        // 自動返信メールを送信する
        $this->sendReplyMail($apiData, $formatFrom);
        return $this->success($this->data);
    }
}