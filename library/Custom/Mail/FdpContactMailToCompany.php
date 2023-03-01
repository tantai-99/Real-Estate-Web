<?php
namespace Library\Custom\Mail;

use App\Repositories\HpContactParts\HpContactPartsRepository;

class FdpContactMailToCompany extends MailAbstract
{

    const TEMPLATE_FILE_NAME = 'fdp_contact_mail_to_company';
    const SUBJECT            = '貴社ホームページからのお問合せ（周辺情報お問い合わせ）　アットホーム　ホームページ作成ツール';
    const FROM               = 'dummy@dummy.com';

	protected $contentCode = array(
		HpContactPartsRepository::SUBJECT
	);

	protected $profileCode = array(
		HpContactPartsRepository::PERSON_NAME                     ,    // お名前
		HpContactPartsRepository::COMPANY_NAME                    ,    // 貴社名
		HpContactPartsRepository::COMPANY_PERSON                  ,    // ご担当者様名
		HpContactPartsRepository::COMPANY_PERSON_POST             ,    // ご担当者様役職
		HpContactPartsRepository::COMPANY_BUSINESS                ,    // 事業内容
		HpContactPartsRepository::PERSON_MAIL                     ,    // メール
		HpContactPartsRepository::PERSON_TEL                      ,    // 電話番号
		HpContactPartsRepository::PERSON_OTHER_CONNECTION         ,    // その他の連絡方法
		HpContactPartsRepository::PERSON_TIME_OF_CONNECTION       ,    // 希望連絡時間帯
		HpContactPartsRepository::PERSON_ADDRESS                  ,    // 住所
		HpContactPartsRepository::PERSON_GENDER                   ,    // 性別
		HpContactPartsRepository::PERSON_AGE                      ,    // 年齢
		HpContactPartsRepository::PERSON_JOB                      ,    // 職業
		HpContactPartsRepository::PERSON_OFFICE_NAME              ,    // 勤務先名
		HpContactPartsRepository::PERSON_NUMBER_OF_FAMILY         ,    // 世帯人数
		HpContactPartsRepository::PERSON_ANNUAL_INCOM             ,    // 年収
		HpContactPartsRepository::PERSON_OWN_FUND                 ,    // 自己資金
		HpContactPartsRepository::PROPERTY_BUDGET                 ,    // 予算
		HpContactPartsRepository::PROPERTY_HOPE_LAYOUT            ,    // ご希望の間取り
		HpContactPartsRepository::PROPERTY_MOVEIN_PLAN            ,    // 入居予定時期
		HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS       ,    // 現住居区分
		HpContactPartsRepository::PERSON_CURRENT_HOME_FORM        ,    // 現住居形態
		HpContactPartsRepository::FREE_1                          ,    // 自由項目1
		HpContactPartsRepository::FREE_2                          ,    // 自由項目2
		HpContactPartsRepository::FREE_3                          ,    // 自由項目3
		HpContactPartsRepository::FREE_4                          ,    // 自由項目4
		HpContactPartsRepository::FREE_5                          ,    // 自由項目5
		HpContactPartsRepository::NOTE                            ,    // 備考

	);

	protected $orderCode = array();

    public function __construct(){
        
        parent::__construct();
        $this->_templete = self::TEMPLATE_FILE_NAME;
        $this->_subject  = self::SUBJECT;
        $this->_from     = self::FROM;
        
    }

    public function setInquiryParams($contactItems){

		$params = $this->createMailParams($contactItems);

        $contactLabels = $this->geLabels();
        
        $labels = array();
        foreach($params['profile'] as $key=>$val){
			if (array_key_exists($key,$contactLabels)){
				$freeLabel = $this->getFreeLabel($contactItems,$key);
				$labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
	        }
        }
        $params['label'] = $labels;
        parent::setInquiryParams($params);
		return;
    }
}