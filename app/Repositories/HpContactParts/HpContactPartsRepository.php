<?php

namespace App\Repositories\HpContactParts;

use App\Repositories\BaseRepository;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists;

class HpContactPartsRepository extends BaseRepository implements HpContactPartsRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpContactParts::class;
    }

    protected $_name = 'hp_contact_parts';
    
    const REQUIREDTYPE_REQUIRED = 1;
    const REQUIREDTYPE_OPTION   = 2;
    const REQUIREDTYPE_HIDDEN   = 3;

    // 問い合わせ入力項目
    const SUBJECT                         = 1;    // お問い合せ内容
    const REQUEST                         = 2;    // リクエスト内容
    const REQUEST_DETAIL                  = 3;    // リクエスト詳細
    const PERSON_NAME                     = 10;    // お名前
    const PERSON_MAIL                     = 11;    // メール
    const PERSON_TEL                      = 12;    // 電話番号
    const PERSON_OTHER_CONNECTION         = 13;    // その他の連絡方法
    const PERSON_TIME_OF_CONNECTION       = 14;    // 希望連絡時間帯
    const PERSON_ADDRESS                  = 15;    // 住所
    const PERSON_GENDER                   = 16;    // 性別
    const PERSON_AGE                      = 17;    // 年齢
    const PERSON_NUMBER_OF_FAMILY         = 18;    // 世帯人数
    const PERSON_ANNUAL_INCOM             = 19;    // 年収
    const PERSON_JOB                      = 20;    // 職業
    const PERSON_OFFICE_NAME              = 21;    // 勤務先名
    const PERSON_OWN_FUND                 = 22;    // 自己資金
    const PERSON_CURRENT_HOME_CLASS       = 23;    // 現住居区分
    const PERSON_CURRENT_HOME_FORM        = 24;    // 現住居形態
    const PROPERTY_TYPE                   = 40;    // 物件の種別
    const PROPERTY_ADDRESS                = 41;    // 物件の住所
    const PROPERTY_EXCLUSIVE_AREA         = 42;    // 専有面積
    const PROPERTY_BUILDING_AREA          = 43;    // 建物面積
    const PROPERTY_LAND_AREA              = 44;    // 土地面積
    const PROPERTY_NUMBER_OF_HOUSE        = 45;    // 総戸数
    const PROPERTY_LAYOUT                 = 46;    // 間取り
    const PROPERTY_AGE                    = 47;    // 築年数
    const PROPERTY_STATE                  = 48;    // 物件の現況
    const PROPERTY_CELL_REASON            = 49;    // 売却理由
    const PROPERTY_HOPE_LAYOUT            = 50;    // ご希望の間取り
    const PROPERTY_MOVEIN_PLAN            = 51;    // 入居予定時期
    const PROPERTY_BUDGET                 = 52;    // 予算（万円）
    const COMPANY_NAME                    = 60;    // 貴社名
    const COMPANY_BUSINESS                = 61;    // 事業内容
    const COMPANY_PERSON                  = 62;    // ご担当者様名
    const COMPANY_PERSON_POST             = 63;    // ご担当者様役職

    //物件リクエスト -
    const PROPERTY_ITEM_OF_BUSINESS       = 70;    // 種目
    const PROPERTY_AREA                   = 71;    // エリア（沿線・駅）
    const PROPERTY_SCHOOL_DISREICT        = 72;    // ご希望の学区
    const PROPERTY_RENT_PRICE             = 73;    // 賃料
    const PROPERTY_PRICE                  = 74;    // 価格(□万円～□万円)
    const PROPERTY_REQUEST_LAYOUT         = 75;    // 間取り
    const PROPERTY_SQUARE_MEASURE         = 76;    // 面積
    const PROPERTY_REQUEST_BUILDING_AREA  = 77;    // 建物面積
    const PROPERTY_REQUEST_LAND_AREA      = 78;    // 土地面積
    const PROPERTY_REQUEST_AGE            = 79;    // 築年数
    const PROPERTY_OTHER_REQUEST          = 80;    // その他ご希望

    const NOTE                            = 90;    // 備考
    const FREE_1                          = 100;    // 自由項目1
    const FREE_2                          = 101;    // 自由項目2
    const FREE_3                          = 102;    // 自由項目3
    const FREE_4                          = 103;    // 自由項目4
    const FREE_5                          = 104;    // 自由項目5
    //物件リクエスト
    const FREE_6                          = 105;    // 自由項目6
    const FREE_7                          = 106;    // 自由項目7
    const FREE_8                          = 107;    // 自由項目8
    const FREE_9                          = 108;    // 自由項目9
    const FREE_10                         = 109;    // 自由項目10
    //物件リクエスト
    // 4402 Add field contact FDP
    const PERIPHERAL_INFO                 = 110; // 周辺エリア情報

    // 問い合わせ入力項目のラベル
    private $itemCodeLabel = array(
        self::SUBJECT                         => 'お問い合せ内容',
        self::REQUEST                         => 'リクエスト内容',
        // self::REQUEST_DETAIL                  => 'リクエスト備考',
        self::PERSON_NAME                     => 'お名前',
        self::PERSON_MAIL                     => 'メール',
        self::PERSON_TEL                      => '電話番号',
        self::PERSON_OTHER_CONNECTION         => 'その他の連絡方法',
        self::PERSON_TIME_OF_CONNECTION       => '希望連絡時間帯',
        self::PERSON_ADDRESS                  => '住所',
        self::PERSON_GENDER                   => '性別',
        self::PERSON_AGE                      => '年齢',
        self::PERSON_JOB                      => '職業',
        self::PERSON_OFFICE_NAME              => '勤務先名',
        self::PERSON_NUMBER_OF_FAMILY         => '世帯人数',
        self::PERSON_ANNUAL_INCOM             => '年収',
        self::PERSON_OWN_FUND                 => '自己資金',
        self::PERSON_CURRENT_HOME_CLASS       => '現住居区分',
        self::PERSON_CURRENT_HOME_FORM        => '現住居形態',
        self::PROPERTY_TYPE                   => '物件の種別',
        self::PROPERTY_ADDRESS                => '物件の住所',
        self::PROPERTY_EXCLUSIVE_AREA         => '専有面積',
        self::PROPERTY_BUILDING_AREA          => '建物面積',
        self::PROPERTY_LAND_AREA              => '土地面積',
        self::PROPERTY_NUMBER_OF_HOUSE        => '総戸数',
        self::PROPERTY_LAYOUT                 => '間取り',
        self::PROPERTY_AGE                    => '築年数',
        self::PROPERTY_STATE                  => '物件の現況',
        self::PROPERTY_CELL_REASON            => '売却理由',
        self::PROPERTY_HOPE_LAYOUT            => 'ご希望の間取り',
        self::PROPERTY_MOVEIN_PLAN            => '入居予定時期',
        self::PROPERTY_BUDGET                 => '予算（万円）',
        self::COMPANY_NAME                    => '貴社名',
        self::COMPANY_BUSINESS                => '事業内容',
        self::COMPANY_PERSON                  => 'ご担当者様名',
        self::COMPANY_PERSON_POST             => 'ご担当者様役職',
        self::NOTE                            => '備考',

        //物件リクエスト
        self::PROPERTY_ITEM_OF_BUSINESS       => '種目',
        self::PROPERTY_AREA                   => 'エリア（沿線・駅）',
        self::PROPERTY_SCHOOL_DISREICT        => 'ご希望の学区',
        self::PROPERTY_RENT_PRICE             => '賃料',
        self::PROPERTY_PRICE                  => '価格',
        self::PROPERTY_REQUEST_LAYOUT         => '間取り',
        self::PROPERTY_SQUARE_MEASURE         => '面積',
        self::PROPERTY_REQUEST_BUILDING_AREA  => '建物面積',
        self::PROPERTY_REQUEST_LAND_AREA      => '土地面積',
        self::PROPERTY_REQUEST_AGE            => '築年数',
        self::PROPERTY_OTHER_REQUEST          => 'その他ご希望',

        self::FREE_1                          => '自由項目１',
        self::FREE_2                          => '自由項目２',
        self::FREE_3                          => '自由項目３',
        self::FREE_4                          => '自由項目４',
        self::FREE_5                          => '自由項目５',

        //物件リクエスト
        self::FREE_6                          => '自由項目６',
        self::FREE_7                          => '自由項目７',
        self::FREE_8                          => '自由項目８',
        self::FREE_9                          => '自由項目９',
        self::FREE_10                         => '自由項目１０',
        self::PERIPHERAL_INFO                 => '周辺エリア情報',
    );


    // 会社問い合わせの入力項目
    private $contactItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getContactItemCodes() {
        return $this->contactItemCode;
    }
    
    // 資料請求パーツの入力項目
    private $documentItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getDocumentItemCodes() {
        return $this->documentItemCode;
    }

    // 査定依頼の入力項目
    private $assessmentItemCode = array(
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::PROPERTY_TYPE,
        self::PROPERTY_ADDRESS,
        self::PROPERTY_EXCLUSIVE_AREA,
        self::PROPERTY_BUILDING_AREA,
        self::PROPERTY_LAND_AREA,
        self::PROPERTY_NUMBER_OF_HOUSE,
        self::PROPERTY_LAYOUT,
        self::PROPERTY_AGE,
        self::PROPERTY_STATE,
        self::PROPERTY_CELL_REASON,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getAssessmentItemCodes() {
        return $this->assessmentItemCode;
    }

    // 居住用賃貸物件フォームのの入力項目
    private $livingleaseItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getLivingLeaseItemCodes() {
        return $this->livingleaseItemCode;
    }

    // 事務所用賃貸物件フォームのの入力項目
    private $officeleaseItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getOfficeLeaseItemCodes() {
        return $this->officeleaseItemCode;
    }

    // 居住用売買物件フォームのの入力項目
    private $livingbuyItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,   
    );
    
    public function getLivingBuyItemCodes() {
        return $this->livingbuyItemCode;
    }

    // 事務所用売買物件フォームのの入力項目
    private $officebuyItemCode = array(
        self::SUBJECT,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );
    
    public function getOfficeBuyItemCodes() {
        return $this->officebuyItemCode;
    }


    // 居住用賃貸物件リクエストフォームのの入力項目
    private $requestLivingleaseItemCode = array(
        self::REQUEST,
        // self::REQUEST_DETAIL,
        self::PERSON_NAME,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_ADDRESS,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_MOVEIN_PLAN,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::PROPERTY_ITEM_OF_BUSINESS,
        self::PROPERTY_AREA,
        self::PROPERTY_SCHOOL_DISREICT,
        self::PROPERTY_RENT_PRICE,
        self::PROPERTY_PRICE,
        self::PROPERTY_REQUEST_LAYOUT,
        self::PROPERTY_SQUARE_MEASURE,
        self::PROPERTY_REQUEST_BUILDING_AREA,
        self::PROPERTY_REQUEST_LAND_AREA,
        self::PROPERTY_REQUEST_AGE,
        self::PROPERTY_OTHER_REQUEST,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
        self::FREE_6,
        self::FREE_7,
        self::FREE_8,
        self::FREE_9,
        self::FREE_10,
    );
    
    public function getRequestLivingLeaseItemCodes() {
        return $this->requestLivingleaseItemCode;
    }

    // 事務所用賃貸物件リクエストフォームのの入力項目
    private $requestOfficeleaseItemCode = array(
        self::REQUEST,
        // self::REQUEST_DETAIL,
        self::PERSON_NAME,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_ADDRESS,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_MOVEIN_PLAN,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::PROPERTY_ITEM_OF_BUSINESS,
        self::PROPERTY_AREA,
        self::PROPERTY_SCHOOL_DISREICT,
        self::PROPERTY_RENT_PRICE,
        self::PROPERTY_PRICE,
        self::PROPERTY_REQUEST_LAYOUT,
        self::PROPERTY_SQUARE_MEASURE,
        self::PROPERTY_REQUEST_BUILDING_AREA,
        self::PROPERTY_REQUEST_LAND_AREA,
        self::PROPERTY_REQUEST_AGE,
        self::PROPERTY_OTHER_REQUEST,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
        self::FREE_6,
        self::FREE_7,
        self::FREE_8,
        self::FREE_9,
        self::FREE_10,
    );
    
    public function getRequestOfficeLeaseItemCodes() {
        return $this->requestOfficeleaseItemCode;
    }

    // 居住用売買物件リクエストフォームのの入力項目
    private $requestLivingbuyItemCode = array(
        self::REQUEST,
        // self::REQUEST_DETAIL,
        self::PERSON_NAME,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_ADDRESS,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_MOVEIN_PLAN,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::PROPERTY_ITEM_OF_BUSINESS,
        self::PROPERTY_AREA,
        self::PROPERTY_SCHOOL_DISREICT,
        self::PROPERTY_RENT_PRICE,
        self::PROPERTY_PRICE,
        self::PROPERTY_REQUEST_LAYOUT,
        self::PROPERTY_SQUARE_MEASURE,
        self::PROPERTY_REQUEST_BUILDING_AREA,
        self::PROPERTY_REQUEST_LAND_AREA,
        self::PROPERTY_REQUEST_AGE,
        self::PROPERTY_OTHER_REQUEST,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
        self::FREE_6,
        self::FREE_7,
        self::FREE_8,
        self::FREE_9,
        self::FREE_10,
    );
    
    public function getRequestLivingBuyItemCodes() {
        return $this->requestLivingbuyItemCode;
    }

    // 事務所用売買物件リクエストフォームのの入力項目
    private $requestOfficebuyItemCode = array(
        self::REQUEST,
        // self::REQUEST_DETAIL,
        self::PERSON_NAME,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_ADDRESS,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_MOVEIN_PLAN,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::PROPERTY_ITEM_OF_BUSINESS,
        self::PROPERTY_AREA,
        self::PROPERTY_SCHOOL_DISREICT,
        self::PROPERTY_RENT_PRICE,
        self::PROPERTY_PRICE,
        self::PROPERTY_REQUEST_LAYOUT,
        self::PROPERTY_SQUARE_MEASURE,
        self::PROPERTY_REQUEST_BUILDING_AREA,
        self::PROPERTY_REQUEST_LAND_AREA,
        self::PROPERTY_REQUEST_AGE,
        self::PROPERTY_OTHER_REQUEST,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
        self::FREE_6,
        self::FREE_7,
        self::FREE_8,
        self::FREE_9,
        self::FREE_10,
    );
    
    public function getRequestOfficeBuyItemCodes() {
        return $this->requestOfficebuyItemCode;
    }

    /**
     *　お問い合わせ設定情報を取得する
     *
     */
    public function fetchAllByHpId($hpId) {
        $select = $this->model->select('item_code', 'item_title', 'required_type', 'choices_type_code', 'sort', 'detail_flg');
        $select->where('hp_id', $hpId);

        return $select->get();
    }


    public function getLabels() {
        return $this->itemCodeLabel;
    }
    
    public function getLabel($type) {
        return isset($this->itemCodeLabel[$type]) ? $this->itemCodeLabel[$type] : null;
    }

    // FDP item code
    private $fdpItemCode = array(
        self::SUBJECT,
        self::PERIPHERAL_INFO,
        self::PERSON_NAME,
        self::COMPANY_NAME,
        self::COMPANY_PERSON,
        self::COMPANY_PERSON_POST,
        self::COMPANY_BUSINESS,
        self::PERSON_MAIL,
        self::PERSON_TEL,
        self::PERSON_OTHER_CONNECTION,
        self::PERSON_TIME_OF_CONNECTION,
        self::PERSON_ADDRESS,
        self::PERSON_GENDER,
        self::PERSON_AGE,
        self::PERSON_JOB,
        self::PERSON_OFFICE_NAME,
        self::PERSON_NUMBER_OF_FAMILY,
        self::PERSON_ANNUAL_INCOM,
        self::PERSON_OWN_FUND,
        self::PROPERTY_HOPE_LAYOUT,
        self::PROPERTY_MOVEIN_PLAN,
        self::PROPERTY_BUDGET,
        self::PERSON_CURRENT_HOME_CLASS,
        self::PERSON_CURRENT_HOME_FORM,
        self::NOTE,
        self::FREE_1,
        self::FREE_2,
        self::FREE_3,
        self::FREE_4,
        self::FREE_5,
    );

    public function getFDPItemCodes() {
        return $this->fdpItemCode;
    }

    public function insertContactPartsWithDefault($pageTypeCode, $pageId, $hpId) {
        switch($pageTypeCode){
            case HpPageRepository::TYPE_FORM_CONTACT:
                $options = Lists\ContactContactSubject::getInstance()->getAll();
                break;
            case HpPageRepository::TYPE_FORM_LIVINGLEASE:
                $options = Lists\ContactLivingLeaseSubject::getInstance()->getAll();
                break;
            case HpPageRepository::TYPE_FORM_OFFICELEASE:
                $options = Lists\ContactOfficeLeaseSubject::getInstance()->getAll();
                break;
            case HpPageRepository::TYPE_FORM_LIVINGBUY:
                $options = Lists\ContactLivingBuySubject::getInstance()->getAll();
                break;
            case HpPageRepository::TYPE_FORM_OFFICEBUY:
                $options = Lists\ContactOfficeBuySubject::getInstance()->getAll();
                break;
            default:
                return;
        }
        $data = array(
            'page_id'       => $pageId,
            'hp_id'         => $hpId,
            'item_code'     => self::SUBJECT,
            'required_type' => self::REQUIREDTYPE_REQUIRED,
            'sort' => 0,
        );
        foreach ($options as $key => $option) {
            $data['choice_' . $key] = $option;
        }
        $this->create($data);
    }
}
