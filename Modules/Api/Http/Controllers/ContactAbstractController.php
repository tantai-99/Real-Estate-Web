<?php
namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Api\Http\Form;
use Library\Custom;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;

class ContactAbstractController extends Controller {

    public function __construct()
    {
       parent::__construct(); 
    }

    public function init($request, $next)
    {

        $this->errors = new \stdClass;
        $this->errors->param_invalid = false;
        $this->errors->auth_invalid = false;
        $this->data = array();

        $this->_params =  $request->all();

        $company_id = $this->_params['company_id'];
        $auth_id    = $this->_params['api_key'];

        // 必須パラメータのチェック
        if ( is_null($company_id) || is_null($auth_id) ){ 
            $this->errors->param_invalid  = true;
            return $next($request);
        }

        // API認証
        if ( !$this->authApi($company_id, $auth_id)){ 
            $this->errors->auth_invalid  = true;
            return $next($request);
        }
        $this->_company = new \stdClass();
        $this->_company->companyId = $company_id;
        $this->_contactForm = new Form\Contact\Contact();
        
        $this->_crypContactMail = new Custom\Crypt\ContactMail();
        return $next($request);

    }

    /* バリデーション
     *
     */
    public function validateForm() {
        
        $params = app('request')->all();
        $apiData = $params['data'];

        $this->data->errors = array();
        if ( !$this->_contactForm->isValid($apiData) ){ 
            foreach($this->_contactForm->getMessages() as $msg){
                $this->data->errors[] = $msg['Invalid'];
            }
        }
    }

    /*
     * 顧客管理システムのアカウントが有効かどうか
     *
     */
    protected function isValidCrmAccount($kokyaku_arr){

        // 顧客管理システムにアカウントがない場合は無効アカウント
        if( !isset($kokyaku_arr['model']) || count($kokyaku_arr['model']) <= 0 ){
            return false;
        }
        //利用区分が1（利用中） or 3（休止） のみ、メール送信対象
        if ( !array_key_exists('riyoKbn', $kokyaku_arr['model'])){
            return false;
        }
        if( $kokyaku_arr['model']['riyoKbn'] != 1 && $kokyaku_arr['model']['riyoKbn'] != 3  ){
            return false;
        }
        return true;
    }


    protected function factoryMailToCompany($apiData){
        $mail = null;
        switch ($apiData['page_type_code']) {
            case HpPageRepository::TYPE_FORM_LIVINGLEASE://居住用賃貸物件お問い合わせ
                $mail = new Custom\Mail\LivingleaseToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_OFFICELEASE://事務所用賃貸物件お問い合わせ
                $mail = new Custom\Mail\OfficeleaseToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_LIVINGBUY://居住用売買物件お問い合わせ
                $mail = new Custom\Mail\LivingbuyToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_OFFICEBUY://事務所用売買物件お問い合わせ
                $mail = new Custom\Mail\OfficebuyToCompany();
                break;
            case HpPageRepository::TYPE_FORM_DOCUMENT://資料請求
                $mail = new Custom\Mail\DocumentToCompany();
                break;
            case HpPageRepository::TYPE_FORM_ASSESSMENT://査定依頼
                $mail = new Custom\Mail\AssesmentToCompany();
                break;
            //物件リクエスト
            case HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE://居住用賃貸物件リクエスト
                $mail = new Custom\Mail\RequestLivingleaseToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE://事務所用賃貸物件リクエスト
                $mail = new Custom\Mail\RequestOfficeleaseToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY://居住用売買物件リクエスト
                $mail = new Custom\Mail\RequestLivingbuyToCompany(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY://事務所用売買物件リクエスト
                $mail = new Custom\Mail\RequestOfficebuyToCompany();
                break;
            // -- 物件リクエスト
            //#4274 Change spec form FDP contact
            // case HpPageRepository::TYPE_FORM_FDP_CONTACT:
            //     $mail = new Custom\Mail\FdpContactMailToCompany();
            //     break;
            default:                                        //会社問い合わせ
                $mail = new Custom\Mail\GeneralToCompany(); 
                break;
        }
        return $mail;
    }
    
    
    protected function factoryMailToCrm($apiData){
        $mail = null;
        switch ($apiData['page_type_code']) {
            case HpPageRepository::TYPE_FORM_LIVINGLEASE://居住用賃貸物件お問い合わせ
                $mail = new Custom\Mail\LivingleaseToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_OFFICELEASE://事務所用賃貸物件お問い合わせ
                $mail = new Custom\Mail\OfficeleaseToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_LIVINGBUY://居住用売買物件お問い合わせ
                $mail = new Custom\Mail\LivingbuyToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_OFFICEBUY://事務所用売買物件お問い合わせ
                $mail = new Custom\Mail\OfficebuyToCrm();
                break;
            case HpPageRepository::TYPE_FORM_DOCUMENT://資料請求
                $mail = new Custom\Mail\DocumentToCrm();
                break;
            case HpPageRepository::TYPE_FORM_ASSESSMENT://査定依頼
                $mail = new Custom\Mail\AssesmentToCrm();
                break;
            //物件リクエスト
            case HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE://居住用賃貸物件リクエスト
                $mail = new Custom\Mail\RequestLivingleaseToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE://事務所用賃貸物件リクエスト
                $mail = new Custom\Mail\RequestOfficeleaseToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY://居住用売買物件リクエスト
                $mail = new Custom\Mail\RequestLivingbuyToCrm(); 
                break;
            case HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY://事務所用売買物件リクエスト
                $mail = new Custom\Mail\RequestOfficebuyToCrm();
                break;
            // -- 物件リクエスト
            //#4274 Change spec form FDP contact
            // case HpPageRepository::TYPE_FORM_FDP_CONTACT:
            //     $mail = new Custom\Mail\FdpContactMailToCrm();
            //     break;
            default:                                        //会社問い合わせ
                $mail = new Custom\Mail\GeneralToCrm(); 
                break;
        }
        return $mail;
    }


    protected function getMailTos($apiData){
        $mailTos = array();
        foreach( $apiData['contact_mail'] as $key=>$mailTo ){
            if ( strncmp( $key , 'mail_to', 7) != 0 ){
                continue;
            }
            $mail = $this->_crypContactMail->decrypt($mailTo);
            if (!empty($mail)) {
                $mailTos[] = $mail;
            }
        }
        return $mailTos;
    }

    /** 
     * オートリプライメールのメールアドレスをコンタクト情報から取得する
     * 
     *
     */
    protected function getReplyMaiTo($contactItems){
        $replyMailTo = null;
        foreach($contactItems as $item){ 
            if( $item['item_key'] == 'person_mail' && !empty($item['value']) ){
                $replyMailTo = $item['value'];
            }
        }
        return $replyMailTo;
    }

    /** 
     * API認証
     * 
     *
     */
    protected function authApi($company_id, $api_key){ 

        $model = \App::make(CompanyAccountRepositoryInterface::class);
        $rows  = $model->getDataForCompanyId($company_id);
        $row = $rows[0];

        if ($row->api_key == $api_key){
            return true;
        }
        return false;
    }

    /**
     * 自動返信メールを送信する
     *
     * @param array $apiData
     * @param string $formatFrom
     */
    protected function sendReplyMail($apiData, $formatFrom) {

        // 自動返信メールの送り先メールドレスを取得する
        $replyMail     = $apiData['reply_mail'];
        $replyMailTo   = $this->getReplyMaiTo($apiData['contactItems']);
        $replyMailFrom = $this->_crypContactMail->decrypt($replyMail['mail_from']);

        if($replyMail['autoreply_flg'] && !is_null($replyMailTo)){
            $mail = new \Library\Custom\Mail();
            try {
                $mail->addTo($replyMailTo);
                $mail->setFrom($formatFrom);//fromアドレスはアドバンス送信専用アドレスとする
                $mail->setReplyTo($replyMailFrom, $replyMail['name_from']);
                $mail->setSubject($replyMail['subject']);
                $mail->setbody($this->convertContent($replyMail['body']));
                $mail->send();
            }
            catch (\Exception $e) {
                $this->data['error'] = 'メール送信に失敗しました。';
            }
        }
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
}