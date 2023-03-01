<?php
namespace Library\Custom\Estate\Setting\SearchFilterForBapi;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Model\Estate\Search\Second\Factory;

class Second{


	public $estate_types;
	private $_categoryFactory;


	private $_typeNamelist = [
		TypeList::TYPE_CHINTAI		=>'Chintai',
		TypeList::TYPE_KASI_TENPO	=>'KasiTenpo',
		TypeList::TYPE_KASI_OFFICE	=>'KasiOffice',
		TypeList::TYPE_PARKING		=>'Parking',
		TypeList::TYPE_KASI_TOCHI	=>'KasiTochi',
		TypeList::TYPE_KASI_OTHER	=>'KasiOther',
		TypeList::TYPE_MANSION		=>'Mansion',
		TypeList::TYPE_KODATE		=>'Kodate',
		TypeList::TYPE_URI_TOCHI	=>'UriTochi',
		TypeList::TYPE_URI_TENPO	=>'UriTenpo',
		TypeList::TYPE_URI_OFFICE	=>'UriOffice',
		TypeList::TYPE_URI_OTHER	=>'UriOther',
	];


	public function __construct() {
		$categoryLabelConfig = SearchFilter\Config\Second\CategoryLabel::getInstance();
		$categoryDescriptionConfig = SearchFilter\Config\Second\CategoryDescription::getInstance();
		$enabledItemConfig   = SearchFilter\Config\Second\EnabledItem::getInstance();
		$itemTypeConfig      = SearchFilter\Config\Second\ItemType::getInstance();
		$itemLabelConfig     = SearchFilter\Config\Second\ItemLabel::getInstance();
		$itemOptionsFactory  = new Factory();

		$itemFactory = new SearchFilter\Item\Factory(
			$enabledItemConfig, $itemTypeConfig, $itemLabelConfig, $itemOptionsFactory);

		$categoryFactory = new SearchFilter\Category\Factory(
			$categoryLabelConfig, $categoryDescriptionConfig, $itemFactory);

		$this->_categoryFactory   = $categoryFactory;
		$this->init();
	}

	public function init() {
		$this->estate_types = [];
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($estateType, $enabledEstateTypes, $searchFilter) {

		if (!is_array($searchFilter)) {
			$searchFilter = @json_decode($searchFilter, true);
		}
		if (!is_array($enabledEstateTypes)) {
			$enabledEstateTypes = explode(',', $enabledEstateTypes);
		}

		if (empty($enabledEstateTypes)){
			return;
		}

		$searchFilterMap=[];
		if(isset($searchFilter['estate_types'])){
			foreach ($searchFilter['estate_types'] as $estateTypeSetting) {
				$searchFilterMap[$estateTypeSetting['estate_type']] = $estateTypeSetting;
			}
		}

		foreach($enabledEstateTypes as $estateType ){
			$bApiParamResult = "";
			// 設定がない場合はパラメータを空にする
			if ( !array_key_exists($estateType,$searchFilterMap)){
				$estateTypeFiletr = [];
				$estateTypeFiletr['estate_type'] = $estateType;
				$estateTypeFiletr['param'] = $bApiParamResult;
				$this->estate_types[] =$estateTypeFiletr;
				continue;
			}

			$estateTypeSetting = $searchFilterMap[$estateType];
			foreach ($estateTypeSetting['categories'] as $category) {
				if (!is_array($category)) {
					continue;
				}
				if (!isset($category['category_id'])) {
					continue;
				}
				if (empty($category['items'])) {
					continue;
				}

				$bApiParam = "";
				switch($category['category_id'])
				{
					case 'kakaku';
						$bApiParam = $this->getBapiParamKakaku($estateType,$category);
						break;
					case 'madori';
						$bApiParam = $this->getBapiParamMadori($estateType,$category);
						break;
					case 'chikunensu';
						$bApiParam = $this->getBapiParamChikunensu($estateType,$category);
						break;
					case 'image';
						$bApiParam = $this->getBapiParamImage($estateType,$category);
						break;
					case 'koukokuhi';
						$bApiParam = $this->getBapiParamKoukokuhi($estateType,$category);
						break;
					case 'tesuryo';
						$bApiParam = $this->getBapiParamTtesuryo($estateType,$category);
						break;
					case 'tatemono_ms';
						$bApiParam = $this->getBapiParamTatemonoms($estateType,$category);
						break;
					case 'tochi_ms';
						$bApiParam = $this->getBapiParamTochims($estateType,$category);
						break;
					case 'saiteki_yoto_cd';
						$bApiParam = $this->getBapiParamSaitekiyotocd($estateType,$category);
						break;
				}

				if (!empty($bApiParam)){
					if (!empty($bApiParamResult)){
						$bApiParamResult.="&";
					}
					$bApiParamResult.=$bApiParam;
				}
			}
			$estateTypeFiletr = [];
			$estateTypeFiletr['estate_type'] = $estateType;
			$estateTypeFiletr['param'] = $bApiParamResult;
			$this->estate_types[] =$estateTypeFiletr;
		}
	}

	private function getBapiParamKakaku($estateType,$category){
		if ($category['category_id'] != 'kakaku') {
			return "";
		}

		$classBase="Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Kakaku\\";
		$className=$classBase.$this->_typeNamelist[$estateType];
		$list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "kakaku_from=".$itemValue;
		}
		if(isset($item[0]) && $item[0]['item_id']==2) {
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$union = !(empty($bApiParam)) ? "&" : "";
			$bApiParam .= $union. "kakaku_to=".$itemValue;
		}
		if(isset($item[1]) && $item[1]['item_id']==2) {
			$itemValue = $item[1]['item_value'];
			$itemValue = $list->get($itemValue);
			$union = !(empty($bApiParam)) ? "&" : "";
			$bApiParam .= $union. "kakaku_to=".$itemValue;
		}
		return $bApiParam;
	}


	private function getBapiParamMadori($estateType,$category){
		if ($category['category_id'] != 'madori') {
			return "";
		}

        $classBase="Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Madori1\\";
        $className=$classBase.$this->_typeNamelist[$estateType];
        $list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
            $itemValue = "";
            foreach($item[0]['item_value'] as $val){
                $val= $list->get($val);
                $itemValue .=  (empty($itemValue))? $val : ",".$val ;
            }

			$bApiParam .= "madori_cd=".$itemValue;
		}
		return $bApiParam;

	}


	private function getBapiParamChikunensu($estateType,$category){
		if ($category['category_id'] != 'chikunensu') {
			return "";
		}

		$classBase="Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\Chikunensu1\\";
		$className=$classBase.$this->_typeNamelist[$estateType];
		$list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= $itemValue;
		}
		return $bApiParam;
	}


	private function getBapiParamImage($estateType,$category)
	{
		if ($category['category_id'] != 'image') {
			return "";
		}

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			if ($item[0]['item_value'] == 1) {
				$bApiParam = "image_ari_fl=1";
			}
		}
		return $bApiParam;
	}
	private function getBapiParamKoukokuhi($estateType,$category){
		if ($category['category_id'] != 'koukokuhi') {
			return "";
		}

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			if ($item[0]['item_value'] == 1) {
				$bApiParam ="kokokuhi_sodan_ari_fl=1";
			}
		}
		return $bApiParam;
	}

	private function getBapiParamTtesuryo($estateType,$category){
		if ($category['category_id'] != 'tesuryo') {
			return "";
		}

		$typeList = TypeList::getInstance();

		$item = $category['items'];
		$bApiParam = "";

		//賃貸
        if ($typeList->isRent($typeList->getClassByType($estateType))) {
			// 手数料ありのみ のみ
			if(isset($item[0]) && $item[0]['item_id']==1){
				// ありのみ(1)
				if($category['items'][0]['item_value'] == 1){
					$bApiParam = 'tesuryo_kyakuzuke_hyaku_fl=1';
				}
			}
		}
		//売買
		else{
			// 手数料ありのみ or 分かれも含むで分岐する
			if(isset($item[0]) && $item[0]['item_id']==1){
				// ありのみ(1)
				if($category['items'][0]['item_value'] == 1){
					$bApiParam = 'tesuryo_cd_not_in=01,02';
				}
			} else if(isset($item[0]) && $item[0]['item_id']==2){
				// 分かれも含む(2)
				if($category['items'][0]['item_value'] == 1){
					$bApiParam = 'tesuryo_cd_not_in=02';
				}
			}
        }
		return $bApiParam;
	}


	private function getBapiParamTatemonoms($estateType,$category){
		if ($category['category_id'] != 'tatemono_ms') {
			return "";
		}

		$classBase="Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\TatemonoMs\\";
		$className=$classBase.$this->_typeNamelist[$estateType];
		$list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "tatemono_ms_from=".$itemValue;
		}
		if(isset($item[0]) && $item[0]['item_id']==2){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "tatemono_ms_to=".$itemValue;
		}
		if(isset($item[1]) && $item[1]['item_id']==2) {
			$itemValue = $item[1]['item_value'];
			$itemValue = $list->get($itemValue);
			$union = !(empty($bApiParam)) ? "&" : "";
			$bApiParam .= $union. "tatemono_ms_to=".$itemValue;
		}
		return $bApiParam;

	}

	private function getBapiParamTochims($estateType,$category){
		if ($category['category_id'] != 'tochi_ms') {
			return "";
		}

		$classBase="Library\Custom\Estate\Setting\SearchFilterForBapi\BapiList\TochiMs\\";
		$className=$classBase.$this->_typeNamelist[$estateType];
		$list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "tochi_ms_from=".$itemValue;
		}
		if(isset($item[0]) && $item[0]['item_id']==2){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "tochi_ms_to=".$itemValue;
		}
		if(isset($item[1]) && $item[1]['item_id']==2) {
			$itemValue = $item[1]['item_value'];
			$itemValue = $list->get($itemValue);
			$union = !(empty($bApiParam)) ? "&" : "";
			$bApiParam .= $union. "tochi_ms_to=".$itemValue;
		}
		return $bApiParam;

	}

	private function getBapiParamSaitekiyotocd($estateType,$category){
		if ($category['category_id'] != 'saiteki_yoto_cd') {
			return "";
		}

		$classBase="Custom_Estate_Setting_SearchFilterForBapi_BapiList_SaitekiYotoCd";
		$className=$classBase.'_'.$this->_typeNamelist[$estateType];
		$list = $className::getInstance();

		$item = $category['items'];
		$bApiParam = "";
		if(isset($item[0]) && $item[0]['item_id']==1){
			$itemValue = $item[0]['item_value'];
			$itemValue = $list->get($itemValue);
			$bApiParam .= "saiteki_yoto_cd=".$itemValue;
		}
		return $bApiParam;
	}
}