<?php
namespace Library\Custom\Mail;

use App\Repositories\HpContactParts\HpContactPartsRepository;

class LivingleaseToCompany  extends Estate
{

    const TEMPLATE_FILE_NAME = 'livinglease_mail_to_company';
    const SUBJECT            = '貴店ホームページからの居住用賃貸物件のお問合せ　アットホーム　ホームページ作成ツール
';
    const FROM               = 'dummy@dummy.com';
    const ESTATE_CATEGORY    = 'livinglease';

	// お問い合わせ内容
	protected $contentCode = array(
		HpContactPartsRepository::SUBJECT
	);
	//物件お問い合わせ項目
    protected $bukkenCode = array(
        Estate::ESTATE_CLASS              ,     //物件種目 
        Estate::TATEMONO_NAME             ,     //建物名
        Estate::KOUTSU_ROSEN              ,     //交通
        Estate::STATION_NAME              ,     //駅名
        Estate::STATION_TOHO_FUN          ,     //徒歩

        Estate::BUSTEI_NAME               ,     //バス停名
        Estate::BUS_JIKAN                 ,     //バス乗車分
        Estate::BUSTEI_JIKAN              ,     //バス停歩分

        Estate::PROPERTY_LOCATION         ,     //所在地
        Estate::PROPERTY_RENT             ,     //賃料
        Estate::PROPERTY_LAYOUT           ,     //間取り
        Estate::PROPERTY_EXCLUSIVE_AREA   ,     //専有面積
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

        HpContactPartsRepository::PROPERTY_BUDGET                 ,     // 予算
        HpContactPartsRepository::PROPERTY_HOPE_LAYOUT            ,     // ご希望の間取り
        HpContactPartsRepository::PROPERTY_MOVEIN_PLAN            ,     // 入居予定時期

        HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS       ,    // 現住居区分
		HpContactPartsRepository::PERSON_CURRENT_HOME_FORM        ,    // 現住居形態
		HpContactPartsRepository::FREE_1                          , // 自由項目1
		HpContactPartsRepository::FREE_2                          , // 自由項目2
		HpContactPartsRepository::FREE_3                          , // 自由項目3
		HpContactPartsRepository::FREE_4                          , // 自由項目4
		HpContactPartsRepository::FREE_5                          , // 自由項目5
		HpContactPartsRepository::NOTE                            , // 備考

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
	);

    /** コンストラクタ
     *
     */
    public function __construct(){
        
        parent::__construct();
        $this->_templete = self::TEMPLATE_FILE_NAME;
        $this->_subject  = self::SUBJECT;
        $this->_from     = self::FROM;
        $this->_category = self::ESTATE_CATEGORY;        
    }

}
