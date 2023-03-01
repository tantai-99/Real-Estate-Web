<?php

namespace App\Http\Form\EstateSetting;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SearchTypeList;
use Library\Custom\Model\Estate\FdpType;

class ClassSearch extends Form
{

	protected $_estateClass;
	protected $_plan;
	protected $_fdp;

	public function setEstateClass($class)
	{
		$this->_estateClass = $class;
		if ($this->getElement('enabled_estate_type')) {
			$estateTypeMaster = TypeList::getInstance();
			$this->getElement('enabled_estate_type')->setValueOptions($estateTypeMaster->getByClass($class));
		}
	}

	public function setPlan($plan)
	{
		$this->_plan = $plan;
	}

	public function setFdp($isFDP)
	{
		$this->_fdp = $isFDP;
	}

	public function init()
	{
		$options = SearchTypeList::getInstance()->getAll();
		$this->_addRequiredMultiCheckbox('search_type', '探し方', $options);

		// 物件種目
		$options = [];
		$this->_addRequiredMultiCheckbox('enabled_estate_type', '物件種目', $options);
		$this->setEstateClass($this->_estateClass);

		// 都道府県
		$options = PrefCodeList::getInstance()->getAll();
		$this->_addRequiredMultiCheckbox('pref', '都道府県', $options);

		//物件リクエスト
		$element = new Element\Checkbox("estate_request_flg");
		$element->setAttributes(['label_attributes' => "利用する"]);
		$element->setLabel('物件リクエスト');
		$element->setRequired(false);
		if ($this->_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
			$this->add($element);
		}

		// add checkbox display freeword no2 ticket 2792
		$element1 = new Element\Checkbox("display_freeword");
		$element1->setAttributes(['label_attributes' => "利用する"]);
		$element1->setLabel('フリーワード検索');
		$element1->setRequired(false);
		$this->add($element1);
		//end
		if ($this->_fdp) {
			$options = FdpType::getInstance()->getFdp();
			$element = new Element\Checkbox('display_fdp');
			$element->setLabel(FdpType::getInstance()->getFdpTitle());
			$element->setRequired(false);
			$element->setValueOptions($options);
			$element->setSeparator('');
			$this->add($element);
		} else {
			// 4489: Change UI setting FDP
			$options = FdpType::getInstance()->getFdp();
			$element = new Element\Checkbox('display_fdp');
			$element->setLabel(FdpType::getInstance()->getFdpTitle());
			$element->setRequired(false);
			$element->setValueOptions($options);
			$element->setSeparator('');
			$element->setAttributes(['disabled' => 'disabled']);
			$this->add($element);

			$links = FdpType::getInstance()->getFdpNotUse();
			$element = new Element\Hidden('fdp_not_use');
			$element->setLabel(FdpType::getInstance()->getFdpTitle());
			$element->setAttributes([
				'data-register-link' => $links[0],
				'data-link-lable' => $links[1],
				'data-link' => $links[2]
			]);
			$this->add($element);
			// end 4489
		}
	}

	protected function _addRequiredMultiCheckbox($name, $label, $options)
	{
		$element = new Element\MultiCheckbox($name);
		$element->setLabel($label);
		$element->setRequired(true);
		$element->setValueOptions($options);
		$element->setSeparator('');
		$this->add($element);
	}
}
