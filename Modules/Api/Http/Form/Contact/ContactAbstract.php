<?php
namespace Modules\Api\Http\Form\Contact;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists;
use App\Repositories\HpContactParts\HpContactPartsRepository;
use App\Rules;

class ContactAbstract extends Form {

	protected $_itemCodeMap = array(
		'subject'					=> HpContactPartsRepository::SUBJECT,					// お問い合せ内容

		//物件リクエスト
		'request'					=> HpContactPartsRepository::REQUEST,					// リクエスト内容
		'request_detail'			=> HpContactPartsRepository::REQUEST_DETAIL,			// リクエスト詳細

		'person_name'				=> HpContactPartsRepository::PERSON_NAME,				// お名前
		'person_mail'				=> HpContactPartsRepository::PERSON_MAIL,				// メール
		'person_tel'				=> HpContactPartsRepository::PERSON_TEL,				// 電話番号
		'person_other_connection'	=> HpContactPartsRepository::PERSON_OTHER_CONNECTION,	// その他の連絡方法
		'person_time_of_connection'	=> HpContactPartsRepository::PERSON_TIME_OF_CONNECTION,	// 希望連絡時間帯
		'person_address'			=> HpContactPartsRepository::PERSON_ADDRESS,			// 住所
		'person_gender'				=> HpContactPartsRepository::PERSON_GENDER,				// 性別
		'person_age'				=> HpContactPartsRepository::PERSON_AGE ,				// 年齢
		'person_number_of_family'	=> HpContactPartsRepository::PERSON_NUMBER_OF_FAMILY,	// 世帯人数
		'person_annual_incom'		=> HpContactPartsRepository::PERSON_ANNUAL_INCOM,		// 年収
		'person_job'				=> HpContactPartsRepository::PERSON_JOB,				// 職業
		'person_office_name'		=> HpContactPartsRepository::PERSON_OFFICE_NAME,		// 勤務先名
		'person_own_fund'			=> HpContactPartsRepository::PERSON_OWN_FUND,			// 自己資金
		'person_current_home_class'	=> HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS,	// 現住居区分
		'person_current_home_form'	=> HpContactPartsRepository::PERSON_CURRENT_HOME_FORM,	// 現住居形態
		'property_type'				=> HpContactPartsRepository::PROPERTY_TYPE,				// 物件の種別
		'property_address'			=> HpContactPartsRepository::PROPERTY_ADDRESS,			// 物件の住所
		'property_exclusive_area'	=> HpContactPartsRepository::PROPERTY_EXCLUSIVE_AREA ,	// 専有面積
		'property_building_area'	=> HpContactPartsRepository::PROPERTY_BUILDING_AREA,	// 建物面積
		'property_land_area'		=> HpContactPartsRepository::PROPERTY_LAND_AREA,		// 土地面積
		'property_number_of_house'	=> HpContactPartsRepository::PROPERTY_NUMBER_OF_HOUSE,	// 総戸数

		'property_layout'			=> HpContactPartsRepository::PROPERTY_LAYOUT,			// 間取り
		'property_age'				=> HpContactPartsRepository::PROPERTY_AGE,				// 築年数
		'property_state'			=> HpContactPartsRepository::PROPERTY_STATE,			// 物件の現況
		'property_cell_reason'		=> HpContactPartsRepository::PROPERTY_CELL_REASON,		// 売却理由
		'property_hope_layout'		=> HpContactPartsRepository::PROPERTY_HOPE_LAYOUT,		// ご希望の間取り
		'property_movein_plan'		=> HpContactPartsRepository::PROPERTY_MOVEIN_PLAN, 		// 入居予定時期
		'property_budget'			=> HpContactPartsRepository::PROPERTY_BUDGET, 			// 予算（万円）

		//物件リクエスト
		'property_item_of_business'	=> HpContactPartsRepository::PROPERTY_ITEM_OF_BUSINESS,	// 種目
		'property_area'				=> HpContactPartsRepository::PROPERTY_AREA,				// エリア（沿線・駅）
		'property_school_disreict'	=> HpContactPartsRepository::PROPERTY_SCHOOL_DISREICT,	// ご希望の学区
		'property_rent_price'		=> HpContactPartsRepository::PROPERTY_RENT_PRICE,		// 賃料
		'property_price'			=> HpContactPartsRepository::PROPERTY_PRICE,			// 価格(□万円～□万円)
		'property_request_layout'	=> HpContactPartsRepository::PROPERTY_REQUEST_LAYOUT,			// 間取り
		'property_square_measure'	=> HpContactPartsRepository::PROPERTY_SQUARE_MEASURE,	// 面積
		'property_request_building_area'	=> HpContactPartsRepository::PROPERTY_REQUEST_BUILDING_AREA,	// 建物面積
		'property_request_land_area'		=> HpContactPartsRepository::PROPERTY_REQUEST_LAND_AREA,		// 土地面積
		'property_request_age'		=> HpContactPartsRepository::PROPERTY_REQUEST_AGE,				// 築年数
		'property_other_request'	=> HpContactPartsRepository::PROPERTY_OTHER_REQUEST,	// その他ご希望

		'company_name'				=> HpContactPartsRepository::COMPANY_NAME, 				// 貴社名
		'company_business'			=> HpContactPartsRepository::COMPANY_BUSINESS, 			// 事業内容
		'company_person'			=> HpContactPartsRepository::COMPANY_PERSON, 			// ご担当者様名
		'company_person_post'		=> HpContactPartsRepository::COMPANY_PERSON_POST,		// ご担当者様役職
		'memo'						=> HpContactPartsRepository::NOTE,						// 備考
		'free_1'					=> HpContactPartsRepository::FREE_1,					// 自由項目1
		'free_2'					=> HpContactPartsRepository::FREE_2,					// 自由項目2
		'free_3'					=> HpContactPartsRepository::FREE_3,					// 自由項目3
		'free_4'					=> HpContactPartsRepository::FREE_4,					// 自由項目4
		'free_5'					=> HpContactPartsRepository::FREE_5,					// 自由項目5
		//物件リクエスト
		'free_6'					=> HpContactPartsRepository::FREE_6,					// 自由項目6
		'free_7'					=> HpContactPartsRepository::FREE_7,					// 自由項目7
		'free_8'					=> HpContactPartsRepository::FREE_8,					// 自由項目8
		'free_9'					=> HpContactPartsRepository::FREE_9,					// 自由項目9
		'free_10'					=> HpContactPartsRepository::FREE_10,					// 自由項目10
		'peripheral'                => HpContactPartsRepository::PERIPHERAL_INFO,
	);

	protected $_validateLengthMap = array(
		'subject_memo'						=> array('min' => 0, 'max' => 1000),	// お問い合せ内容の備考
		'request'							=> array('min' => 0, 'max' => 1000),	// リクエスト内容
		'request_memo'						=> array('min' => 0, 'max' => 1000),	// リクエスト内容の備考
		'person_name'						=> array('min' => 0, 'max' => 40),		// お名前
		'person_mail'						=> array('min' => 0, 'max' => 62),		// メール
		'person_tel'						=> array('min' => 12, 'max' => 13),		// 電話番号
		'person_other_connection'			=> array('min' => 0, 'max' => 50),		// その他の連絡方法
		'person_time_of_connection'			=> array('min' => 0, 'max' => 50),		// 希望連絡時間帯
		'person_address'					=> array('min' => 0, 'max' => 250),		// 住所
		'person_age'						=> array('min' => 0, 'max' => 3),		// 年齢
		'person_number_of_family'			=> array('min' => 0, 'max' => 2),		// 世帯人数
		'person_annual_incom'				=> array('min' => 0, 'max' => 9),		// 年収
		'person_job'						=> array('min' => 0, 'max' => 30),		// 職業
		'person_office_name'				=> array('min' => 0, 'max' => 140),		// 勤務先名
		'person_own_fund'					=> array('min' => 0, 'max' => 9),		// 自己資金
		'property_address'					=> array('min' => 0, 'max' => 250),		// 物件の住所
		'property_exclusive_area'			=> array('min' => 0, 'max' => 50),		// 専有面積
		'property_building_area'			=> array('min' => 0, 'max' => 50),		// 建物面積
		'property_land_area'				=> array('min' => 0, 'max' => 50),		// 土地面積
		'property_number_of_house'			=> array('min' => 0, 'max' => 9),		// 総戸数
		'property_age'						=> array('min' => 0, 'max' => 4),		// 築年数
		'property_hope_layout'				=> array('min' => 0, 'max' => 75),		// ご希望の間取り
		'property_budget'					=> array('min' => 0, 'max' => 9), 		// 予算（万円）
		'property_item_of_business'			=> array('min' => 0, 'max' => 2000),	// 種目
		'property_area'						=> array('min' => 0, 'max' => 2000),	// エリア（沿線・駅）
		'property_school_disreict'			=> array('min' => 0, 'max' => 2000),	// ご希望の学区
		'property_rent_price'				=> array('min' => 0, 'max' => 2000),	// 賃料
		'property_price'					=> array('min' => 0, 'max' => 2000),	// 価格(□万円～□万円)
		'property_request_layout'			=> array('min' => 0, 'max' => 2000),	// 間取り
		'property_square_measure'			=> array('min' => 0, 'max' => 2000),	// 面積
		'property_request_building_area'	=> array('min' => 0, 'max' => 2000),	// 建物面積
		'property_request_land_area'		=> array('min' => 0, 'max' => 2000),	// 土地面積
		'property_request_age'				=> array('min' => 0, 'max' => 2000),	// 築年数
		'property_other_request'			=> array('min' => 0, 'max' => 2000),	// その他ご希望
		'company_name'						=> array('min' => 0, 'max' => 140), 	// 貴社名
		'company_business'					=> array('min' => 0, 'max' => 150), 	// 事業内容
		'company_person'					=> array('min' => 0, 'max' => 75), 		// ご担当者様名
		'company_person_post'				=> array('min' => 0, 'max' => 75),		// ご担当者様役職
		'memo'								=> array('min' => 0, 'max' => 2000),	// 備考
		'free_1'							=> array('min' => 0, 'max' => 2000),	// 自由項目1
		'free_2'							=> array('min' => 0, 'max' => 2000),	// 自由項目2
		'free_3'							=> array('min' => 0, 'max' => 2000),	// 自由項目3
		'free_4'							=> array('min' => 0, 'max' => 2000),	// 自由項目4
		'free_5'							=> array('min' => 0, 'max' => 2000),	// 自由項目5
		'free_6'							=> array('min' => 0, 'max' => 2000),	// 自由項目6
		'free_7'							=> array('min' => 0, 'max' => 2000),	// 自由項目7
		'free_8'							=> array('min' => 0, 'max' => 2000),	// 自由項目8
		'free_9'							=> array('min' => 0, 'max' => 2000),	// 自由項目9
		'free_10'							=> array('min' => 0, 'max' => 2000),	// 自由項目10
	);

	protected $_itemKeyMap;


	public function getItemCode($itemKey) {
		if(!array_key_exists($itemKey,$this->_itemCodeMap)){ 
			return null;
		}

		return $this->_itemCodeMap[$itemKey];
	}

	public function getItemKey($itemCode) {
		return $this->_itemKeyMap[$itemCode];
	}


    // 連絡先関連の入力項目（連絡先はまとめる）
    private $ContactInfoCodes = array(
        HpContactPartsRepository::PERSON_MAIL,             // メール
        HpContactPartsRepository::PERSON_TEL,              // 電話
        HpContactPartsRepository::PERSON_OTHER_CONNECTION, // その他
    );
    public function getContactInfoCodes() {
    	return $this->ContactInfoCodes;
    }
    public function getContactInfoKey() {
		$itemKey = array();
		foreach($this->ContactInfoCodes as $itemCode){ 
			$itemKey[] = $this->getItemKey($itemCode);
		}
    	return $itemKey;
    }


	public function init() {

		$itemKeyMap = array();

		foreach($this->_itemCodeMap as $key=>$val ){
			$itemKeyMap[$val]=$key;
		}

		$this->_itemKeyMap = $itemKeyMap;

		//お問い合せ内容
		$element = new Element\MultiCheckbox('subject', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyAreaUnit::getInstance()->getAll());
		$this->add($element);

		//お問い合せ内容の備考
		$element = new Element\Text('subject_memo', array('disableLoadDefaultDecorators'=>true));
		$validator = new Rules\StringLength($this->_validateLengthMap['subject_memo']);
		$validator->setMessage('備考の文字数がオーバーしています。'.$this->_validateLengthMap['subject_memo']['max'].'文字以内で入力してください。');
		$element->addValidator($validator);
		$this->add($element);

		//リクエスト内容
		$element = new Element\Text('request', array('disableLoadDefaultDecorators'=>true));
		$validator = new Rules\StringLength($this->_validateLengthMap['request']);
		$validator->setMessage('リクエスト内容の文字数がオーバーしています。'.$this->_validateLengthMap['request']['max'].'文字以内で入力してください。');
		$element->addValidator($validator);
		$this->add($element);

		//リクエスト内容の備考
		$element = new Element\Text('request_memo', array('disableLoadDefaultDecorators'=>true));
		$validator = new Rules\StringLength($this->_validateLengthMap['request_memo']);
		$validator->setMessage('備考の文字数がオーバーしています。'.$this->_validateLengthMap['request_memo']['max'].'文字以内で入力してください。');
		$element->addValidator($validator);
		$this->add($element);

		//お名前
		$element = new Element\Text('person_name', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_name']));
		$this->add($element);

		//person_mail
		$element = new Element\Text('person_mail', array('disableLoadDefaultDecorators'=>true));
		$validator = new Rules\StringLength($this->_validateLengthMap['person_mail']);
		$validator->setMessage('メールアドレスは半角'.$this->_validateLengthMap['person_mail']['max'].'文字以内で入力してください。');
		$element->addValidator($validator);
		$validator = new Rules\EmailAddress();
		$validator->setMessage('メールアドレスを正確に入力してください。');
		$element->addValidator($validator);
		$this->add($element);

		$element = new Element\Text('person_tel', array('disableLoadDefaultDecorators'=>true));
		$validator = new Rules\StringLength($this->_validateLengthMap['person_tel']);
		$validator->setMessage('電話番号の形式に誤りがあります。');
		$element->addValidator($validator);
		$validator = new Rules\Tel();
		$validator->setMessage('電話番号の形式に誤りがあります。');
		$element->addValidator($validator);
		$this->add($element);

		$element = new Element\Text('person_other_connection', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_other_connection']));
		$this->add($element);

		$element = new Element\Text('person_time_of_connection', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_time_of_connection']));
		$this->add($element);

		$element = new Element\Text('person_address', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_address']));
		$this->add($element);

		// 性別
		$element = new Element\Radio('person_gender', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPersonGender::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Text('person_age', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_age']));
		$this->add($element);

		$element = new Element\Text('person_number_of_family', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_number_of_family']));
		$this->add($element);

		$element = new Element\Text('person_annual_incom', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_annual_incom']));
		$this->add($element);

		$element = new Element\Text('person_job', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_job']));
		$this->add($element);

		$element = new Element\Text('person_office_name', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_office_name']));
		$this->add($element);

		$element = new Element\Text('person_own_fund', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['person_own_fund']));
		$this->add($element);

		$element = new Element\Select('person_current_home_class', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPersonCurrentHomeClass::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Select('person_current_home_form', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPersonCurrentHomeForm::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Select('property_type', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyType::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Text('property_address', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_address']));
		$this->add($element);

		// 専有面積
		$element = new Element\Text('property_exclusive_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_exclusive_area']));
		$this->add($element);

		$element = new Element\Radio('property_exclusive_area_sub', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyAreaUnit::getInstance()->getAll());
		$this->add($element);

		// 建物面積
		$element = new Element\Text('property_building_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_building_area']));
		$this->add($element);

		$element = new Element\Radio('property_building_area_sub', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyAreaUnit::getInstance()->getAll());
		$this->add($element);

		//土地面積
		$element = new Element\Text('property_land_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_land_area']));
		$this->add($element);

		$element = new Element\Radio('property_land_area_sub', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyAreaUnit::getInstance()->getAll());
		$element->setAllowEmpty(true);
		$this->add($element);

		//
		$element = new Element\Text('property_number_of_house', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_number_of_house']));
		$this->add($element);

		//
		$element = new Element\Select('property_layout', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyLayout::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Select('property_layout_sub', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyLayoutUnit::getInstance()->getAll());
		$this->add($element);

		// 築年数
		$element = new Element\Text('property_age', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_age']));
		$this->add($element);

		$element = new Element\Radio('property_age_sub', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyAgeUnit::getInstance()->getAll());
		$this->add($element);

		//
		$element = new Element\Select('property_state', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyState::getInstance()->getAll());
		$this->add($element);

		//
		$element = new Element\MultiCheckbox('property_cell_reason', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyCellReason::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Text('property_hope_layout', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_hope_layout']));
		$this->add($element);

		$element = new Element\Select('property_movein_plan', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(Lists\ContactPropertyMoveinPlan::getInstance()->getAll());
		$this->add($element);

		$element = new Element\Text('property_budget', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_budget']));
		$this->add($element);

		$element = new Element\Text('property_item_of_business', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_item_of_business']));
		$this->add($element);

		$element = new Element\Text('property_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_area']));
		$this->add($element);

		$element = new Element\Text('property_school_disreict', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_school_disreict']));
		$this->add($element);

		$element = new Element\Text('property_rent_price', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_rent_price']));
		$this->add($element);

		$element = new Element\Text('property_price', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_price']));
		$this->add($element);

		$element = new Element\Text('property_request_layout', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_request_layout']));
		$this->add($element);

		$element = new Element\Text('property_square_measure', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_square_measure']));
		$this->add($element);

		$element = new Element\Text('property_request_building_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_request_building_area']));
		$this->add($element);

		$element = new Element\Text('property_request_land_area', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_request_land_area']));
		$this->add($element);

		$element = new Element\Text('property_request_age', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_request_age']));
		$this->add($element);

		$element = new Element\Text('property_other_request', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['property_other_request']));
		$this->add($element);

		$element = new Element\Text('company_name', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['company_name']));
		$this->add($element);

		$element = new Element\Text('company_business', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['company_business']));
		$this->add($element);

		$element = new Element\Text('company_person', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['company_person']));
		$this->add($element);

		$element = new Element\Text('company_person_post', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['company_person_post']));
		$this->add($element);

		$element = new Element\Textarea('memo', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['memo']));
		$this->add($element);

		$element = new Element\Text('free_1', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_1']));
		$this->add($element);

		$element = new Element\Text('free_2', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_2']));
		$this->add($element);

		$element = new Element\Text('free_3', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_3']));
		$this->add($element);
		
		$element = new Element\Text('free_4', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_4']));
		$this->add($element);

		$element = new Element\Text('free_5', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_5']));
		$this->add($element);

		$element = new Element\Text('free_6', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_6']));
		$this->add($element);

		$element = new Element\Text('free_7', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_7']));
		$this->add($element);

		$element = new Element\Text('free_8', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_8']));
		$this->add($element);

		$element = new Element\Text('free_9', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_9']));
		$this->add($element);

		$element = new Element\Text('free_10', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength($this->_validateLengthMap['free_10']));
		$this->add($element);

        $element = new Element\MultiCheckbox('peripheral', array('disableLoadDefaultDecorators'=>true));
        $this->add($element);
	}


	public function isValid($data, $checkError = true) {
		$this->setData($data);
		$_data = $this->_dissolveArrayValue($data, $this->getElementsBelongTo());
		foreach ($_data as $key => $val) {
			if (($element = $this->getElement($key)) != null) {
				// 選択系の項目は公開側でチェック済み
				if (is_string($val) && !($element instanceof Element\Select)) {
					$element->isValid(false);
				}
			}
		}

		if (empty($this->getMessages())){
			return true;
		}
		return false;
	}

	public function getContactInfo ($formContact) {

	    $contactInfo = array();
	    $values = $formContact->form->form->getValues();
		$contactParts = new ContactAbstract();
	    foreach($formContact->form->form->getSortedFormElements() as $val){ 
			$element = $values['form'][$val->getName()];
	        if ($element['required_type'] == HpContactPartsRepository::REQUIREDTYPE_HIDDEN){
	            continue;
	        }
	        $itemKey = $this->getItemKey($val->getName()); 
	        if (in_array($itemKey, $contactParts->getContactInfoKey())){ 
   	            $contactInfo[$itemKey]['name']          = $val->getName();
	            $contactInfo[$itemKey]['key']           = $itemKey;
	            $contactInfo[$itemKey]['label']         = $val->getTitle();
	            $contactInfo[$itemKey]['required_type'] = $element['required_type'];
	        }
	    }
		return $contactInfo;
	}

    const ERROR_MSG_MAIL_AND_TEL = 'メールアドレスと電話番号は必ずご入力ください。';
    const ERROR_MSG_MAIL_OR_TEL = 'メールアドレスか電話番号は必ずご入力ください。';
    const ERROR_MSG_MAIL_AND_OTHER = 'メールアドレスとその他連絡先は必ずご入力ください。';
    const ERROR_MSG_TEL_AND_OTHER = '電話番号とその他連絡先は必ずご入力ください。';
    const ERROR_MSG_TEL = '電話番号は必ずご入力ください。';
    const ERROR_MSG_MAIL = 'メールは必ずご入力ください。';
    const ERROR_MSG_OTHER = 'メールアドレスか電話番号は必ずご入力ください。その他連絡先は必ずご入力ください。';

	public function getContactInfoAnnotation ($contactInfo) { 

		$ci = $contactInfo;
	    $annotation = "";
	    
		$mail = 'person_mail';
		$tel = 'person_tel';
		$other = 'person_other_connection';

	    // (1)全て必須 (2)メール(必須) 電話番号(必須) その他(非表示) (3)電話番号だけ表示 (4)メールだけ表示 
	    if(($this->isReuired($mail, $ci) && $this->isReuired($tel, $ci) && $this->isReuired($other, $ci)) || 
	       (!$this->isHidden($mail, $ci) && $this->isHidden($tel, $ci)  && $this->isHidden($other, $ci))   || 
	       ( $this->isHidden($mail, $ci) && !$this->isHidden($tel, $ci) && $this->isHidden($other, $ci)))
	    {
			$annotation = "";
		}
	    // (1)電話番号とメールのみ必須 (2)メールアドレスと電話番号が任意、その他は任意または非表示
	    else 
	    if  ( $this->isReuired($mail, $ci) && $this->isReuired($tel, $ci) && !$this->isReuired($other, $ci) ||
	          $this->isReuired($mail, $ci) && $this->isReuired($tel, $ci) &&  $this->isHidden($other, $ci)   )
	    {
			$annotation = self::ERROR_MSG_MAIL_AND_TEL;
		}
		else
	    if ($this->isOption($mail, $ci) && $this->isOption($tel, $ci) && !$this->isReuired($other, $ci) )
	    {
            $annotation = self::ERROR_MSG_MAIL_OR_TEL;
		}
	    // メールとその他が必須
	    else 
	    if (($this->isReuired($mail, $ci) && !$this->isReuired($tel, $ci) && $this->isReuired($other, $ci)) ||
	        ($this->isOption($mail, $ci) && $this->isHidden($tel, $ci) && $this->isReuired($other, $ci)))
	    {
            $annotation = self::ERROR_MSG_MAIL_AND_OTHER;
		}
	    // (1)電話番号とその他が必須 (2)その他必須
	    else 
	    if((!$this->isReuired($mail, $ci) && $this->isReuired($tel, $ci) && $this->isReuired($other, $ci)) ||
	       ( $this->isHidden($mail, $ci)  && $this->isOption($tel, $ci) && $this->isReuired($other, $ci)))
	    {
            $annotation = self::ERROR_MSG_TEL_AND_OTHER;
		}
		else
	    // (1)電話番号とその他が表示
	    if(( $this->isHidden($mail, $ci) && !$this->isHidden($tel, $ci) &&  !$this->isHidden($other, $ci) || 
	       (!$this->isReuired($mail, $ci) && $this->isReuired($tel, $ci) && !$this->isReuired($other, $ci))))
	    {
            $annotation = self::ERROR_MSG_TEL;
		}
	    // (1)メールとその他が表示
	    else 
	    if (( $this->isHidden($mail, $ci) && !$this->isHidden($tel, $ci) &&  !$this->isHidden($other, $ci) ) ||
	        ($this->isReuired($mail, $ci) && !$this->isReuired($tel, $ci) && !$this->isReuired($other, $ci)))
	    {
            $annotation = self::ERROR_MSG_MAIL;
		}
	    // その他連絡先のみ必須
	    else 
	    if (!$this->isReuired($mail, $ci) && !$this->isReuired($tel, $ci) && $this->isReuired($other, $ci))
	    {
            $annotation = self::ERROR_MSG_OTHER;
		}
	    else 
		{
			$annotation = "";
		}

	    return $annotation;
	}

    /**
     * @param string $itemKey
     * @return string|null
     */
    public static function getItemUnitWord($itemKey) {
        $units = array(
            'person_age'               => '歳',
            'property_age'             => '年',
            'person_number_of_family'  => '人',
            'person_annual_incom'      => '万円',
            'person_own_fund'          => '万円',
            'property_budget'          => '万円',
            'property_number_of_house' => '戸'
        );
        return array_key_exists($itemKey, $units) ? $units[$itemKey] : null;
    }
	
	private function isReuired($key, $contactInfo){
		$isRequired = false;
		if ( array_key_exists($key, $contactInfo) && $contactInfo[$key]['required_type'] == HpContactPartsRepository::REQUIREDTYPE_REQUIRED ){
			$isRequired = true;
		}
		return $isRequired;
	}
	
	private function isOption($key, $contactInfo){
		$isOption = false;
		if ( array_key_exists($key, $contactInfo) && $contactInfo[$key]['required_type'] == HpContactPartsRepository::REQUIREDTYPE_OPTION ){
			$isOption = true;
		}
		return $isOption;
	}

	private function isHidden($key, $contactInfo){
		$isHidden = false;
		if ( !array_key_exists($key, $contactInfo) || 
		     (array_key_exists($key, $contactInfo) && $contactInfo[$key]['required_type'] == HpContactPartsRepository::REQUIREDTYPE_HIDDEN )){
			$isHidden = true;
		}
		return $isHidden;
	}

}