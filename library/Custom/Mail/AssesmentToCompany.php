<?php
namespace Library\Custom\Mail;

use App\Repositories\HpContactParts\HpContactPartsRepository;
/**
 * パスワード変更フォーム
 * 
 */

class AssesmentToCompany  extends MailAbstract
{

    const TEMPLATE_FILE_NAME = 'assesment_mail_to_company';
    const SUBJECT            = '貴店ホームページからのお問合せ（査定依頼）　アットホーム　ホームページ作成ツール';
    const FROM               = 'dummy@dummy.com';

	// お問い合わせ内容
	protected $contentCode = array(
		HpContactPartsRepository::SUBJECT
	);

	// メールに記載するお問い合わせ項目の順番
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
		HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS       ,    // 現住居区分
		HpContactPartsRepository::PERSON_CURRENT_HOME_FORM        ,    // 現住居形態
	);

	//＜依頼内容＞
	protected $orderCode = array(
		HpContactPartsRepository::PROPERTY_TYPE                   , // 物件の種別
		HpContactPartsRepository::PROPERTY_ADDRESS                , // 物件の住所
		HpContactPartsRepository::PROPERTY_EXCLUSIVE_AREA         , // 専有面積
		HpContactPartsRepository::PROPERTY_BUILDING_AREA          , // 建物面積
		HpContactPartsRepository::PROPERTY_LAND_AREA              , // 土地面積
		HpContactPartsRepository::PROPERTY_NUMBER_OF_HOUSE        , // 総戸数
		HpContactPartsRepository::PROPERTY_LAYOUT                 , // 間取り
		HpContactPartsRepository::PROPERTY_AGE                    , // 築年数
		HpContactPartsRepository::PROPERTY_STATE                  , // 物件の現況
		HpContactPartsRepository::PROPERTY_CELL_REASON            , // 売却理由
		HpContactPartsRepository::FREE_1                          , // 自由項目1
		HpContactPartsRepository::FREE_2                          , // 自由項目2
		HpContactPartsRepository::FREE_3                          , // 自由項目3
		HpContactPartsRepository::FREE_4                          , // 自由項目4
		HpContactPartsRepository::FREE_5                          , // 自由項目5
		HpContactPartsRepository::NOTE                            , // 備考
	);



    /** コンストラクタ
     *
     */
    public function __construct(){
        
        parent::__construct();
        $this->_templete = self::TEMPLATE_FILE_NAME;
        $this->_subject  = self::SUBJECT;
        $this->_from     = self::FROM;
    }


  	/** 
     *お問い合わせパラメータを設定する
     *  加盟店宛てのメールは項目の順番はCMSで作成したままとする
     *  空欄は空欄のままにする
     */
    public function setInquiryParams($contactItems){

		$params = $this->createMailParams($contactItems);

        // お問い合わせ項目のラベルを取得する
        $contactLabels = $this->geLabels();
        
        // パラメータにラベルを追加する
        $labels = array();
        foreach($params['profile'] as $key=>$val){
			if (array_key_exists($key,$contactLabels)){
				$freeLabel = $this->getFreeLabel($contactItems,$key);
				$labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
	        }
        }
        foreach($params['order'] as $key=>$val){
			if (array_key_exists($key,$contactLabels)){
				$freeLabel = $this->getFreeLabel($contactItems,$key);
				$labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
	        }
        }

        $keyParams = array_keys($params);
        foreach ($labels as $key => $value) {
            foreach ($keyParams as $valKeyParams) {
                if ($valKeyParams == 'url' || $valKeyParams == 'content' || $valKeyParams == 'peripheral_flg') {
                    continue;
                }
                if (isset($params[$valKeyParams][$key]))
                {
                    if ($params[$valKeyParams][$key]) {
                        $params[$valKeyParams][$key] = $this->convertContentExceptLabel($value, $params[$valKeyParams][$key]);
                    }
                }
            }
        }

        foreach($params['content'] as $key=>&$val){
            $val = $this->convertContent($val, $key);
        }

        $params['label'] = $labels;
        parent::setInquiryParams($params);

		return;
    }
}
