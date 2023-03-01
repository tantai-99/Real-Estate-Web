<?php
namespace Library\Custom\Hp\Page\SectionParts\Form;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;

class RequestOfficebuy extends AbstractForm {

	public function getItemCodes() {
		return \App::make(HpContactPartsRepositoryInterface::class)->getRequestOfficeBuyItemCodes();
	}

	protected $_form_title = '事業用売買物件';

	// protected $_form_subject_preset = array(
	protected $_form_subject_placeholder = array(
			'〇〇マンションの物件を紹介して欲しい',
			'利回りが〇〇％以上の投資用物件を紹介して欲しい',
			'〇〇用のおすすめ物件を紹介して欲しい',
	);

	protected $_form_subject_choice_count = 10;

	protected $_form_placeholder = array(
		'property_item_of_business' => array(
				'賃貸アパート',
				'賃貸マンション',
				'貸店舗',
				'新築マンション',
				'売事務所',
			),
		'property_area' => array(
				'青山周辺エリア',
				'六本木周辺エリア',
				'麻布周辺エリア',
				'白金周辺エリア',
			),
		'property_school_disreict' => array(
				'◯◯小学校',
				'△△小学校',
				'○○中学校',
				'△△中学校',
			),
		'property_rent_price' => array(
				'指定しない',
				'5万円以下',
				'5万円～7万円',
				'7万円～10万円',
				'10万円～15万円',
				'15万円以上',
			),
		'property_price' => array(
				'指定しない',
				'～1000万円',
				'1000万円～2000万円',
				'2000万円～3000万円',
				'3000万円～4000万円',
				'4000万円～5000万円',
				'5000万円以上',
			),
		'property_request_layout' => array(
				'1R・1K' ,
				'1DK・1LDK',
				'2K・2DK・2LDK',
				'3K・3DK・3LDK',
				'4K・4DK・4LDK',
			),
		'property_square_measure' => array(
				'20㎡～',
				'50㎡～',
				'100㎡～',
				'150㎡～',
				'200㎡～',
			),
		'property_request_building_area' => array(
				'20㎡～',
				'50㎡～',
				'100㎡～',
				'150㎡～',
				'200㎡～',
			),
		'property_request_land_area' => array(
				'20㎡～',
				'50㎡～',
				'100㎡～',
				'150㎡～',
				'200㎡～',
			),
		'property_request_age' => array(
				'新築',
				'築後未入居',
				'3年以内',
				'5年以内',
				'10年以内',
			)
	);

	protected $_defaultRequiredType = array(
			HpContactPartsRepository::REQUEST					=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			// HpContactPartsRepository::REQUEST_DETAIL			=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PERSON_MAIL				=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PERSON_TEL				=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PERSON_OTHER_CONNECTION	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PERSON_TIME_OF_CONNECTION	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PERSON_ADDRESS			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_GENDER				=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PERSON_AGE				=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PERSON_NUMBER_OF_FAMILY	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_ANNUAL_INCOM		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_JOB				=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_OFFICE_NAME		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			// HpContactPartsRepository::PERSON_OWN_FUND			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_CURRENT_HOME_FORM	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_TYPE				=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_ADDRESS			=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_EXCLUSIVE_AREA	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			// HpContactPartsRepository::PROPERTY_BUILDING_AREA	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			// HpContactPartsRepository::PROPERTY_LAND_AREA		=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_NUMBER_OF_HOUSE	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			// HpContactPartsRepository::PROPERTY_LAYOUT			=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			// HpContactPartsRepository::PROPERTY_AGE				=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_STATE			=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_CELL_REASON		=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_HOPE_LAYOUT		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			// HpContactPartsRepository::PROPERTY_MOVEIN_PLAN		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_BUDGET			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_NAME				=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_BUSINESS			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_PERSON			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_PERSON_POST		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::NOTE						=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,

			HpContactPartsRepository::PROPERTY_ITEM_OF_BUSINESS	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_AREA				=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_SCHOOL_DISREICT	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_RENT_PRICE		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_PRICE			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_REQUEST_LAYOUT	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_SQUARE_MEASURE	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_REQUEST_BUILDING_AREA	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_REQUEST_LAND_AREA	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_REQUEST_AGE		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_OTHER_REQUEST	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,

			HpContactPartsRepository::FREE_1					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_2					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_3					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_4					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_5					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,

			HpContactPartsRepository::FREE_6					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_7					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_8					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_9					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_10					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
	);

	protected $_onlyRequiredRequiredType = array(
			// HpContactPartsRepository::REQUEST,
			HpContactPartsRepository::PERSON_NAME,
	);
}