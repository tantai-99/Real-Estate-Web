<?php
namespace App\Http\Form\EstateSetting;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Http\Form\EstateSetting\ClassSearch;
use Library\Custom\Model\Estate\SecondEstateEnabledList;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SecondSearchTypeList;

class SecondClassSearch extends ClassSearch {
	/**
	 * 
	 * @var App\Models\SecondEstate
	 */
	protected $_secondEstate;
	
	public function setSecondEstate($secondEstate) {
		$this->_secondEstate = $secondEstate;
	}
	public function init() {
		// 2次広告自動公開設定
		$options = SecondEstateEnabledList::getInstance()->getAll();
		$this->_addRequiredRadio('enabled', '2次広告自動公開', $options);
		// 都道府県
		// 二次広告設定内の都道府県を取得
		$settingObject = $this->_secondEstate->toSettingObject();
		$options = PrefCodeList::getInstance()->pick($settingObject->area_search_filter->area_1);
		$this->_addRequiredMultiCheckbox('pref', '都道府県', $options);
		
		// 物件種目
		$options = [
		];
		$this->_addRequiredMultiCheckbox('enabled_estate_type', '物件種目', $options);
		$this->setEstateClass($this->_estateClass);
		
		// 市区郡/沿線・駅選択
		$options = SecondSearchTypeList::getInstance()->getAll();
		$this->_addRequiredRadio('search_type', '市区郡/沿線・駅選択', $options);
	}
	
	private function _addRequiredRadio($name, $label, $options) {
		$element = new Element\Radio($name);
		$element->setLabel($label);
		$element->setRequired(true);
		$element->setValueOptions($options);
		$element->setSeparator('');
		$this->add($element);
	}
}