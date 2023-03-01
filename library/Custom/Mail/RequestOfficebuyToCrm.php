<?php
namespace Library\Custom\Mail;

use App\Repositories\HpContactParts\HpContactPartsRepository;

class RequestOfficebuyToCrm extends MailAbstract
{

    const TEMPLATE_FILE_NAME = 'request_officebuy_mail_to_crm';
    const SUBJECT            = '【お問合せ】アットホーム（貴店ホームページ）より（会社問合せ）';
    const FROM               = 'dummy-to-crm@dummy.com';

	// お問い合わせ内容
	protected $contentCode = array(
		HpContactPartsRepository::REQUEST
	);
	// メールに記載するお問い合わせ項目の順番
	protected $profileCode = array(
		HpContactPartsRepository::PERSON_NAME                     ,    // お名前
		HpContactPartsRepository::PERSON_MAIL                     ,    // メール
		HpContactPartsRepository::PERSON_TEL                      ,    // 電話番号
		HpContactPartsRepository::PERSON_OTHER_CONNECTION         ,    // その他の連絡方法
		HpContactPartsRepository::PERSON_TIME_OF_CONNECTION       ,    // 希望連絡時間帯
		HpContactPartsRepository::PERSON_GENDER                   ,    // 性別
		HpContactPartsRepository::PERSON_AGE                      ,    // 年齢
		HpContactPartsRepository::COMPANY_NAME                    ,    // 貴社名
		HpContactPartsRepository::COMPANY_PERSON                  ,    // ご担当者様名
		HpContactPartsRepository::COMPANY_PERSON_POST             ,    // ご担当者様役職
		HpContactPartsRepository::COMPANY_BUSINESS                ,    // 事業内容
		HpContactPartsRepository::PERSON_ADDRESS                  ,    // 住所
		HpContactPartsRepository::PERSON_JOB                      ,    // 職業
		HpContactPartsRepository::PERSON_OFFICE_NAME              ,    // 勤務先名
		HpContactPartsRepository::PERSON_NUMBER_OF_FAMILY         ,    // 世帯人数
		HpContactPartsRepository::PERSON_ANNUAL_INCOM             ,    // 年収
		HpContactPartsRepository::PERSON_OWN_FUND                 ,    // 自己資金
        HpContactPartsRepository::PROPERTY_MOVEIN_PLAN            ,    // 入居予定時期
		HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS       ,    // 現住居区分
		HpContactPartsRepository::PERSON_CURRENT_HOME_FORM        ,    // 現住居形態
		HpContactPartsRepository::NOTE                            , // 備考
		HpContactPartsRepository::PROPERTY_ITEM_OF_BUSINESS       ,    // 種目
		HpContactPartsRepository::PROPERTY_AREA                   ,    // エリア（沿線・駅）
		HpContactPartsRepository::PROPERTY_SCHOOL_DISREICT        ,    // ご希望の学区
		HpContactPartsRepository::PROPERTY_RENT_PRICE             ,    // 賃料
		HpContactPartsRepository::PROPERTY_PRICE                  ,    // 価格(□万円～□万円)
        HpContactPartsRepository::PROPERTY_REQUEST_LAYOUT         , // 間取り
		HpContactPartsRepository::PROPERTY_SQUARE_MEASURE         , // 面積
        HpContactPartsRepository::PROPERTY_REQUEST_BUILDING_AREA  , // 建物面積
        HpContactPartsRepository::PROPERTY_REQUEST_LAND_AREA      , // 土地面積
        HpContactPartsRepository::PROPERTY_REQUEST_AGE            , // 築年数
		HpContactPartsRepository::PROPERTY_OTHER_REQUEST          , // その他ご希望
		HpContactPartsRepository::FREE_1                          , // 自由項目1
		HpContactPartsRepository::FREE_2                          , // 自由項目2
		HpContactPartsRepository::FREE_3                          , // 自由項目3
		HpContactPartsRepository::FREE_4                          , // 自由項目4
		HpContactPartsRepository::FREE_5                          , // 自由項目5
		HpContactPartsRepository::FREE_6                          , // 自由項目6
		HpContactPartsRepository::FREE_7                          , // 自由項目7
		HpContactPartsRepository::FREE_8                          , // 自由項目8
		HpContactPartsRepository::FREE_9                          , // 自由項目9
		HpContactPartsRepository::FREE_10                         , // 自由項目10
	);

	//＜依頼内容＞
	protected $orderCode = array();

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
     *　顧客システム宛てのメールの項目は順番固定で全て表示する
     *　空欄は空欄のままにする
     */
    public function setInquiryParams(array $contactItems){

		$params = $this->createMailParams($contactItems);

		// プロフィールを作る
		$dstProfile = array();
		foreach( $this->profileCode as $val ){
			$dstProfile[$val] = "";
			if( array_key_exists( $val, $params['profile'] ) ){
				$dstProfile[$val] = $params['profile'][$val];
			}
		}
		$params['profile'] = $dstProfile;


		// お問い合わせ項目のラベルを取得する
		$contactLabels = $this->geLabels();
		
		// パラメータにラベルを追加する
		$labels = array();
		foreach($params['profile'] as $key=>$val){
			$freeLabel = $this->getFreeLabel($contactItems,$key);
			$labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
            $labels[$key] =  $this->convertLabel($labels[$key]);
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
