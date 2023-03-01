<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;
use Library\Custom\Hp\Page\SectionParts\Form\Element;

class Factory {
	/**
	 *
	 * @param int $type
	 * @param Library\Custom\Hp\Page\SectionParts\Form\AbstractForm $contactForm
	 * @return Element\Text
	 */
	public function create($type, $contactForm) {
		$options = array();
		if (in_array((int)$type, $contactForm->getOnlyRequiredRequiredType(), true)) {
			$options['isRequired'] = true;
		}

		$options['defaultRequiredType'] = HpContactPartsRepository::REQUIREDTYPE_REQUIRED;
		$defaultRequiredType = $contactForm->getDefaultRequiredType();
		if (isset($defaultRequiredType[$type])) {
			$options['defaultRequiredType'] = $defaultRequiredType[$type];
		}
		if (in_array($type, [HpContactPartsRepository::SUBJECT, HpContactPartsRepository::FREE_1, HpContactPartsRepository::FREE_2, HpContactPartsRepository::FREE_3, HpContactPartsRepository::FREE_4, HpContactPartsRepository::FREE_5, HpContactPartsRepository::FREE_6, HpContactPartsRepository::FREE_7, HpContactPartsRepository::FREE_8, HpContactPartsRepository::FREE_9, HpContactPartsRepository::FREE_10, HpContactPartsRepository::REQUEST])) {
			$options['type'] = $type;
		}
		switch ($type) {
			case HpContactPartsRepository::SUBJECT:
				$options['freeChoiceCount'] = $contactForm->getFormSubjectChoiceCount();
				$options['presetChoices']   = $contactForm->getFormSubjectPresets();
				$form = new Element\Subject($options);
				break;
			//物件リクエスト
			case HpContactPartsRepository::REQUEST:
				$options['freeChoiceCount'] = $contactForm->getFormSubjectChoiceCount();
				$options['presetPlaceholders'] = $contactForm->getFormSubjectPlaceholder();
				$form = new Element\Request($options);
				break;
			case HpContactPartsRepository::FREE_1:
			case HpContactPartsRepository::FREE_2:
			case HpContactPartsRepository::FREE_3:
			case HpContactPartsRepository::FREE_4:
			case HpContactPartsRepository::FREE_5:
			//物件リクエスト
			case HpContactPartsRepository::FREE_6:
			case HpContactPartsRepository::FREE_7:
			case HpContactPartsRepository::FREE_8:
			case HpContactPartsRepository::FREE_9:
			case HpContactPartsRepository::FREE_10:
				$form = new Element\Free($options);
				if (isset($options['isRequired'])) {
					$form->setIsRequired($options['isRequired']);
				}
				$form->getElement('required_type')->setValue($options['defaultRequiredType']);
				break;

			//物件リクエスト
    		// 種目
			case HpContactPartsRepository::PROPERTY_ITEM_OF_BUSINESS:
				$options['defaultValue'] = 'checkbox';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_item_of_business');
				$form = new Element\FreeItem($options);
				break;
			// エリア（沿線・駅）
			case HpContactPartsRepository::PROPERTY_AREA:
				$options['defaultValue'] = 'checkbox';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_area');
				$form = new Element\FreeItem($options);
				break;
			// ご希望の学区
			case HpContactPartsRepository::PROPERTY_SCHOOL_DISREICT:
				$options['defaultValue'] = 'checkbox';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_school_disreict');
				$form = new Element\FreeItem($options);
				break;
			// 賃料
			case HpContactPartsRepository::PROPERTY_RENT_PRICE:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_rent_price');
				$form = new Element\FreeItem($options);
				break;
			// 価格(□万円～□万円)
			case HpContactPartsRepository::PROPERTY_PRICE:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_price');
				$form = new Element\FreeItem($options);
				break;
			// 間取り
			case HpContactPartsRepository::PROPERTY_REQUEST_LAYOUT:
				$options['defaultValue'] = 'checkbox';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_request_layout');
				$form = new Element\FreeItem($options);
				break;
			// 面積
			case HpContactPartsRepository::PROPERTY_SQUARE_MEASURE:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_square_measure');
				$form = new Element\FreeItem($options);
				break;
			// 建物面積
			case HpContactPartsRepository::PROPERTY_REQUEST_BUILDING_AREA:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_request_building_area');
				$form = new Element\FreeItem($options);
				break;
			// 土地面積
			case HpContactPartsRepository::PROPERTY_REQUEST_LAND_AREA:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_request_land_area');
				$form = new Element\FreeItem($options);
				break;
			// 築年数
			case HpContactPartsRepository::PROPERTY_REQUEST_AGE:
				$options['defaultValue'] = 'select';
				$options['presetPlaceholders'] = $contactForm->getFormPlaceholder('property_request_age');
				$form = new Element\FreeItem($options);
				break;
			// その他ご希望
			case HpContactPartsRepository::PROPERTY_OTHER_REQUEST:
				$options['defaultValue'] = 'textarea';
				$form = new Element\FreeItem($options);
				break;
			default:
				$form = new Element\Text($options);
				break;
		}

		$form->setTitle(\App::make(HpContactPartsRepositoryInterface::class)->getLabel($type));

		return $form;
	}

}