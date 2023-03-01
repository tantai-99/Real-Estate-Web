<?php
namespace Library\Custom\Hp\Page\SectionParts\Form;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;

class Officelease extends AbstractForm {

	public function getItemCodes() {
		return \App::make(HpContactPartsRepositoryInterface::class)->getOfficeLeaseItemCodes();
	}

	protected $_form_title = '事業用賃貸物件';

	protected $_form_subject_choice_count = 9;

	protected $_defaultRequiredType = array(
			HpContactPartsRepository::SUBJECT					=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
            HpContactPartsRepository::PERIPHERAL_INFO	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
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
			HpContactPartsRepository::PERSON_OWN_FUND			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PERSON_CURRENT_HOME_FORM	=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_TYPE				=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_ADDRESS			=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_EXCLUSIVE_AREA	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_BUILDING_AREA	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_LAND_AREA		=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_NUMBER_OF_HOUSE	=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_LAYOUT			=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_AGE				=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_STATE			=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_CELL_REASON		=> HpContactPartsRepository::REQUIREDTYPE_REQUIRED,
			HpContactPartsRepository::PROPERTY_HOPE_LAYOUT		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::PROPERTY_MOVEIN_PLAN		=> HpContactPartsRepository::REQUIREDTYPE_OPTION,
			HpContactPartsRepository::PROPERTY_BUDGET			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_NAME				=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_BUSINESS			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_PERSON			=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::COMPANY_PERSON_POST		=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::NOTE						=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_1					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_2					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_3					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_4					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
			HpContactPartsRepository::FREE_5					=> HpContactPartsRepository::REQUIREDTYPE_HIDDEN,
	);

	protected $_onlyRequiredRequiredType = array(
			HpContactPartsRepository::SUBJECT,
			HpContactPartsRepository::PERSON_NAME,
	);
}