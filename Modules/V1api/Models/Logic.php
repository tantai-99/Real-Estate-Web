<?php
namespace Modules\V1api\Models;

use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\BApi;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Estate\Setting\SearchFilter\Front;
use Library\Custom\Estate\Setting\Special;


class Logic
{
	public $rentTypes = [
			Estate\TypeList::TYPE_CHINTAI,
			Estate\TypeList::TYPE_KASI_TENPO,
			Estate\TypeList::TYPE_KASI_OFFICE,
			Estate\TypeList::TYPE_PARKING,
			Estate\TypeList::TYPE_KASI_TOCHI,
			Estate\TypeList::TYPE_KASI_OTHER,
	];
	public $purchaseTypes = [
			Estate\TypeList::TYPE_MANSION,
			Estate\TypeList::TYPE_KODATE,
			Estate\TypeList::TYPE_URI_TOCHI,
			Estate\TypeList::TYPE_URI_TENPO,
			Estate\TypeList::TYPE_URI_OFFICE,
			Estate\TypeList::TYPE_URI_OTHER,
	];

	private $emptyBukkenList = ['bukkens' => array()];

	private $searcher;
	private $liner;
	private $citier;
    private $suggester;

	public function __construct()
	{
		$this->searcher = new Logic\BukkenList();
		$this->liner = new Logic\LineEki();
		$this->citier = new Logic\City();
        $this->suggester = new Logic\SuggestCount();
	}

	public function shumoku(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

        $datas->setParamNames(new ParamNames($params));
        // 種目選択画面だけ特殊
		if ($params->isPcMedia()) {
	        $datas->setSeoSpecialsRent($settings->special->pickSpecialRowsByEstateType($this->rentTypes));
    	    $datas->setSeoSpecialsPurchase($settings->special->pickSpecialRowsByEstateType($this->purchaseTypes));
		}

        return $datas;
	}

	public function rent(
		Params $params,
		Settings $settings)
	{
		$datas = new Datas();

        $datas->setParamNames(new ParamNames($params));

		if ($params->isPcMedia()) {
        	$datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($this->rentTypes));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings, $this->rentTypes);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function purchase(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

        $datas->setParamNames(new ParamNames($params));

		if ($params->isPcMedia()) {
        	$datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($this->purchaseTypes));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings, $this->purchaseTypes);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function pref($params, $settings)
	{
		$datas = new Datas();

        $datas->setParamNames(new ParamNames($params));

        $datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

		if ($params->isPcMedia()) {
            $type_ct = $params->getTypeCt();
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function prefSpl($params, $settings)
	{
		$datas = new Datas();

		$datas->setParamNames(new ParamNames($params));

        // 特集取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();

        $datas->setPrefSetting($specialSetting->area_search_filter->area_1);

		if ($params->isPcMedia()) {
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));            
			// 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);

                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function city(
			Params $params,
			Settings $settings,
			$spatial_flag = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setCityList($this->citier->getCityList($params, $settings, $pNames, $searchFilter, $spatial_flag));
        }
		$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

		if ($params->isPcMedia()) {
            $type_ct = $params->getTypeCt();
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function citySpl(
			Params $params,
			Settings $settings,
			$spatial_flag = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
		// 検索用用絞り込み条件
        $searchFilter = $specialSetting->search_filter;
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
		// 表示用絞り込み条件
		$frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		// 検索パラメータ初期化
		$searchFilter->setValues($specialSetting->enabled_estate_type, []);
		$frontSearchFilter->setValues($specialSetting->enabled_estate_type, []);
		// 絞り込み条件未設定項目ロード
		$frontSearchFilter->loadEnables($specialSetting->enabled_estate_type);
		$datas->setSearchFilter($searchFilter);
		$datas->setFrontSearchFilter($frontSearchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setCityList($this->citier->getCityListSpl($params, $settings, $pNames, $searchFilter, $spatial_flag));
        }

		if ($params->isPcMedia()) {
            $areaSearchFilter = $specialSetting->area_search_filter;
            $datas->setPrefSetting($areaSearchFilter->area_1);

            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

    public function choson(
        Params $params,
        Settings $settings)
    {
        $datas = new Datas();

        $pNames = new ParamNames($params);
        $datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setChosonList($this->citier->getChosonList($params, $settings, $pNames, $searchFilter));
        }
        $datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

        if ($params->isPcMedia()) {
            $type_ct = $params->getTypeCt();
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
        }

        return $datas;
    }

    public function chosonSpl(
        Params $params,
        Settings $settings)
    {

        $datas = new Datas();

        $pNames = new ParamNames($params);
        $datas->setParamNames($pNames);

        // 特集取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();

        //$searchFilter = $specialSetting->search_filter;
        $frontSearchFilter = $specialSetting->search_filter->toFrontSearchFilter($specialSetting->enabled_estate_type);
        $frontSearchFilter->setValues($specialSetting->enabled_estate_type, []);
        $frontSearchFilter->loadEnables($specialSetting->enabled_estate_type);
        //$datas->setSearchFilter($searchFilter);
        $datas->setFrontSearchFilter($frontSearchFilter);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setChosonList($this->citier->getChosonListSpl($params, $settings, $pNames, $searchFilter));
        }

        if ($params->isPcMedia()) {
            $methodSetting = Estate\SpecialMethodSetting::getInstance();
            if (!$methodSetting->hasDefaultMethod($specialSetting->method_setting)) {
                $type_ct = (array) $params->getTypeCt();
                $searchSetting = $settings->search->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
                $areaSearchFilter = $searchSetting->area_search_filter;
                if (count($specialSetting->area_search_filter->area_1) > 0) {
                    $areaSearchFilter->area_1 = $specialSetting->area_search_filter->area_1;
                }
            } else {
                $areaSearchFilter = $specialSetting->area_search_filter;
            }
			$datas->setPrefSetting($areaSearchFilter->area_1);

            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
        }

        return $datas;
    }

	public function line(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setLineList($this->liner->getLineList($params, $settings, $pNames, $searchFilter));
            $datas->setLineCountList($this->liner->getLineCountList($params, $settings, $pNames, $searchFilter));
        }
		$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

		if ($params->isPcMedia()) {
            $type_ct = $params->getTypeCt();
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));

            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function lineSpl(
			Params $params,
			Settings $settings)
	{
	    $datas = new Datas();

        $pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setLineList($this->liner->getLineListSpl($params, $settings, $pNames));
            $datas->setLineCountList($this->liner->getLineCountListSpl($params, $settings, $pNames, $searchFilter));
        }

		if ($params->isPcMedia()) {
			// 特集取得
			$specialRow = $settings->special->getCurrentPagesSpecialRow();
			// 特集検索設定取得
			$specialSetting = $specialRow->toSettingObject();
			$datas->setPrefSetting($specialSetting->area_search_filter->area_1);

            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function eki(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setEkiList($this->liner->getEkiList($params, $settings, $pNames, $searchFilter));
            $datas->setEkiSettingOfKen($this->liner->getEkiSettingByKenEnsen($params, $settings, $pNames, $searchFilter));
        }
		$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

		if ($params->isPcMedia()) {
            $type_ct = $params->getTypeCt();
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function ekiSpl(
			Params $params,
			Settings $settings)
	{

		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
		// 検索用用絞り込み条件
        $type_ct = $params->getTypeCt();

		//$searchFilter = $specialSetting->search_filter;
		$frontSearchFilter = $specialSetting->search_filter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		$frontSearchFilter->setValues($specialSetting->enabled_estate_type, []);
		$frontSearchFilter->loadEnables($specialSetting->enabled_estate_type);
        //$datas->setSearchFilter($searchFilter);
		$datas->setFrontSearchFilter($frontSearchFilter);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setEkiList($this->liner->getEkiListSpl($params, $settings, $pNames, $searchFilter));
            $datas->setEkiSettingOfKen($this->liner->getEkiSettingByKenEnsenSpl($params, $settings, $pNames));
        }


		if ($params->isPcMedia()) {
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function result(
			Params $params,
			Settings $settings, $hiddenOnly = false, $isModal = false)
	{

		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        // こだわり条件
        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
		$searchFilter = $searchFilter->setSearchFilterDefault($params);
        $datas->setSearchFilter($searchFilter);
        // 物件一覧
        if (! $isModal) {
        	// モーダルの場合は、物件一覧およびファセット情報は必要ない
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $datas->setBukkenList($this->searcher->getBukkenList($params, $settings, $pNames, $searchFilter));
            }
        }
		$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));

		// if ($params->isPcMedia()) {
			// SEO さらに絞り込む リンクのデータ
			if (! $hiddenOnly) {
				$datas->setSeoAnotherChoiceList($this->getSeoAnotherChoiceList($params, $settings, $pNames));
                $type_ct = $params->getTypeCt();
                $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
                $datas->setSeoSpecials($settings->special->pickSpecialRowsByEstateType($type_id));
			}
        /*
         * hidden情報
         */
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
			switch ($params->getSearchType())
			{
				case $params::SEARCH_TYPE_LINE:
				case $params::SEARCH_TYPE_EKI:
				case $params::SEARCH_TYPE_LINEEKI_POST:
					$datas->setLineList($this->liner->getModalLineList($params, $settings, $pNames));
					$datas->setLineCountList($this->liner->getLineCountList($params, $settings, $pNames, $searchFilter));
					if($isModal) {
						$datas->setEkiList($this->liner->getModalEkiList($params, $settings, $pNames, $searchFilter));
						$datas->setEkiSettingOfKen($this->liner->getEkiSettingByKenEnsen($params, $settings, $pNames, $searchFilter));
					} else {
						// 駅一覧を物件数無で取得設定する
						$apiParam = new BApi\EkiListParams();

						$ken_cd = $pNames->getKenCd();

						$ensen_cd_api = array();
						$ensen_eki_cd = null;
						$ensenCtList = $params->getEnsenCt();
						if (!is_null($ensenCtList)) {
							// 沿線ローマ字より沿線コードを取得する
							$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
							foreach ($ensenObjList as $ensenObj) {
								array_push($ensen_cd_api, $ensenObj['code']);
							}
						} else {
							$eki_ct = $params->getEkiCt();
							$ensenCtList = array();
							foreach ((array) $eki_ct as $eki) {
								$ekiObj = EnsenEki::getObjBySingle($eki);
								array_push($ensenCtList, $ekiObj->getEnsenCt());
							}
							$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
							foreach ($ensenObjList as $ensenObj) {
								array_push($ensen_cd_api, $ensenObj['code']);
							}
						}
						$apiParam->setEnsenCd($ensen_cd_api);

						$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
						$apiParam->setEnsenEkiCd($ensen_eki_cd);

						$apiEkiList = new BApi\EkiList();
						$ekiList = $apiEkiList->getEki($apiParam, 'EKILIST');

						// 件数を 0にする
						foreach($ekiList['ensens'] as &$ensen) {
							foreach ($ensen['ekis'] as &$eki) {
								$eki['count'] = 0;
							}
						}
						$datas->setEkiList($ekiList);

						$datas->setEkiSettingOfKen([]);
					}
					break;

				case $params::SEARCH_TYPE_CITY:
				case $params::SEARCH_TYPE_PREF:
				case $params::SEARCH_TYPE_SEIREI:
				case $params::SEARCH_TYPE_CITY_POST:
                case $params::SEARCH_TYPE_CHOSON:
                case $params::SEARCH_TYPE_CHOSON_POST:
					if ($params->isOnlyChosonModal()) {
						// 町村モーダルのみ更新の場合
					 $datas->setChosonList($this->citier->getModalChosonList($params, $settings, $pNames, $searchFilter));
					} else {
						if($isModal) {
							$datas->setCityList($this->citier->getModalCityList($params, $settings, $pNames, $searchFilter));
						} else {
							$apiParam = new BApi\ShikugunListParams();
							$ken_cd = $pNames->getKenCd();
							$apiParam->setKenCd($ken_cd);
							$apiParam->setGrouping($apiParam::GROUPING_TYPE_LOCATE_CD);

							$type_ct = $params->getTypeCt();
							$settingObject = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
							$shikugunCodes = $settingObject->area_search_filter->getShikugunCodes($ken_cd);
							$apiParam->setShozaichiCd($shikugunCodes);

							$apiCityList = new BApi\ShikugunList();

							$cityList = $apiCityList->getShikugun($apiParam, 'LIST');
							foreach($cityList['shikuguns'] as &$pref) {
								foreach($pref['locate_groups'] as &$locateGroup) {
									foreach($locateGroup['shikuguns'] as &$shikugun) {
										$shikugun['count'] = 0;
									}
								}
							}
							
							$datas->setCityList($cityList);
						}
					}
					break;
			}
        }
		// }

		return $datas;
	}


	public function resultSpl(
			Params $params,
			Settings $settings, $hiddenOnly = false, $isModal = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $searchFilter = $specialSetting->search_filter;
        // フロント用こだわり条件を作成
        $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		// こだわり条件
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
        $areaSearchFilter = $specialSetting->area_search_filter;
        // 検索パラメータ設定
        $searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        // 絞り込み条件検索後
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting) && !empty($params->getSearchFilter())) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidialAfterSearch($searchFilter, $params->getSearchFilter());
        }
        $frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        // 未設定の希望条件をロード
        $frontSearchFilter->loadDesiredEnables($specialSetting->enabled_estate_type);
        // 人気のこだわり条件をロード
        $frontSearchFilter->loadPopularItems($specialSetting->enabled_estate_type);
		// ATHOME_HP_DEV-6563 - apply with special
		if ($specialSetting->isSpecialShumokuSort()) {
			$frontSearchFilter = $frontSearchFilter->setSearchFilterDefault($params);
			$searchFilter = $searchFilter->setSearchFilterDefault($params);
		}
        $datas->setSearchFilter($searchFilter);
        $datas->setFrontSearchFilter($frontSearchFilter);

		// 物件一覧
        if (! $isModal) {
        	// モーダルの場合は、物件一覧およびファセット情報は必要ない
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $datas->setBukkenList($this->searcher->getBukkenListSpl($params, $settings, $pNames, $searchFilter, $frontSearchFilter));				
			}
        }

		if ($params->isPcMedia()) {
			// SEO さらに絞り込む リンクのデータ
			if (! $hiddenOnly) {
				$datas->setSeoAnotherChoiceList($this->getSeoAnotherChoiceList($params, $settings, $pNames));
	            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
			}

			/*
			 * hidden情報
			 */
			$datas->setPrefSetting($areaSearchFilter->area_1);
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                switch ($params->getSearchType())
                {
                    case $params::SEARCH_TYPE_LINE:
                    case $params::SEARCH_TYPE_EKI:
                    case $params::SEARCH_TYPE_LINEEKI_POST:
                        $datas->setLineList($this->liner->getModalLineListSpl($params, $settings, $pNames));
						$datas->setLineCountList($this->liner->getLineCountListSpl($params, $settings, $pNames, $searchFilter));
                        if($isModal) {
                            $datas->setEkiList($this->liner->getModalEkiListSpl($params, $settings, $pNames, $searchFilter));
                            $datas->setEkiSettingOfKen($this->liner->getEkiSettingByKenEnsenSpl($params, $settings, $pNames));
                        } else {
							// 駅一覧を物件数無で取得設定する
							$apiParam = new BApi\EkiListParams();
							
							$ken_cd = $pNames->getKenCd();

							$ensen_cd_api = array();
							$ensen_eki_cd = null;
							$ensenCtList = $params->getEnsenCt();
							if (!is_null($ensenCtList)) {
								// 沿線ローマ字より沿線コードを取得する
								$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
								foreach ($ensenObjList as $ensenObj) {
									array_push($ensen_cd_api, $ensenObj['code']);
								}
							} else {
								$eki_ct = $params->getEkiCt();
								$ensenCtList = array();
								foreach ((array) $eki_ct as $eki) {
									$ekiObj = EnsenEki::getObjBySingle($eki);
									array_push($ensenCtList, $ekiObj->getEnsenCt());
								}
								$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
								foreach ($ensenObjList as $ensenObj) {
									array_push($ensen_cd_api, $ensenObj['code']);
								}
							}
							$apiParam->setEnsenCd($ensen_cd_api);
							$type_ct = $params->getTypeCt();
							if (is_array($type_ct)) {
								$type_ct = $type_ct[0];
							}
							$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
							$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
							$apiParam->setEnsenEkiCd($ensen_eki_cd);

							$apiEkiList = new BApi\EkiList();
							$ekiList = $apiEkiList->getEki($apiParam, 'EKILIST');

							// 件数を 0にする
							foreach($ekiList['ensens'] as &$ensen) {
								foreach ($ensen['ekis'] as &$eki) {
									$eki['count'] = 0;
								}
							}
							$datas->setEkiList($ekiList);

							$datas->setEkiSettingOfKen([]);
						}
                        break;
                    case $params::SEARCH_TYPE_CITY:
                    case $params::SEARCH_TYPE_PREF:
                    case $params::SEARCH_TYPE_SEIREI:
                    case $params::SEARCH_TYPE_CITY_POST:
                    case $params::SEARCH_TYPE_CHOSON:
                    case $params::SEARCH_TYPE_CHOSON_POST:
                        if ($params->isOnlyChosonModal()) {
                            // 町村モーダルのみ更新の場合
                            $datas->setChosonList($this->citier->getModalChosonListSpl($params, $settings, $pNames, $searchFilter));
                        } else {
                            if($isModal) {
                                $datas->setCityList($this->citier->getModalCityListSpl($params, $settings, $pNames, $searchFilter));
                            } else {
								$apiParam = new BApi\ShikugunListParams();
								$ken_cd  = $pNames->getKenCd();
								$apiParam->setKenCd($ken_cd);
							    $apiParam->setGrouping($apiParam::GROUPING_TYPE_LOCATE_CD);

                                $specialRow = $settings->special->getCurrentPagesSpecialRow();
                                $settingObject = $specialRow->toSettingObject();
						     	$shikugunCodes = $settingObject->area_search_filter->getShikugunCodes($ken_cd);
							    $apiParam->setShozaichiCd($shikugunCodes);

							    $apiCityList = new BApi\ShikugunList();

							    $cityList = $apiCityList->getShikugun($apiParam, 'LIST');
								
							    foreach($cityList['shikuguns'] as &$pref) {
								    foreach($pref['locate_groups'] as &$locateGroup) {
									    foreach($locateGroup['shikuguns'] as &$shikugun) {
										    $shikugun['count'] = 0;
							    		}
								    }
						    	}
								$datas->setCityList($cityList);
                            }
                        }
                        break;
                }
            }
		}

		return $datas;
	}

	public function resultDirectSpl(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();

        // ATHOME_HP_DEV-5001
        // 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
        if($specialSetting->area_search_filter->choson_search_enabled == 0) {
            // 特集指定の物件種別の『物件検索設定』地域設定を取得する
            $estateClassArea = $settings->search->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;
            // 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
            if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
                if(!empty($estateClassArea->area_5->getAll())) {
                    $specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;
                    $specialSetting->area_search_filter->choson_search_enabled = 1;
                    if(!empty($estateClassArea->area_6->getAll())) {
                        $specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
                    }
                }
            }
        }

		// こだわり条件
        $areaSearchFilter = $specialSetting->area_search_filter;
        $searchFilter = $specialSetting->search_filter;
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
        // フロント用こだわり条件を作成
        $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
        // 検索パラメータ設定
        $searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
				// 絞り込み条件検索後
				if ($methodSetting->hasInvidialMethod($specialSetting->method_setting) && !empty($params->getSearchFilter())) {
						$searchFilter->categories = $searchFilter->setSearchFilterInvidialAfterSearch($searchFilter, $params->getSearchFilter());
				}
        $frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        // 未設定の希望条件をロード
        $frontSearchFilter->loadDesiredEnables($specialSetting->enabled_estate_type);
        // 人気のこだわり条件をロード
        $frontSearchFilter->loadPopularItems($specialSetting->enabled_estate_type);
		// ATHOME_HP_DEV-6563 - apply with special
		if ($specialSetting->isSpecialShumokuSort()) {
			$frontSearchFilter = $frontSearchFilter->setSearchFilterDefault($params);
			$searchFilter = $searchFilter->setSearchFilterDefault($params);
		}
		$datas->setSearchFilter($searchFilter);
		$datas->setFrontSearchFilter($frontSearchFilter);

		// 物件一覧
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setBukkenList($this->searcher->getBukkenListSplDirect($params, $settings, $pNames, $searchFilter, $frontSearchFilter));
        }

		$datas->setPrefSetting($areaSearchFilter->area_1);

		if ($params->isPcMedia()) {
            $datas->setSeoSpecials($settings->special->pickSpecialRowsByTypeCt($params->getTypeCt()));
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $recommendList = $this->searcher->getRecommendList($params, $settings);
                $datas->setRecommendList($recommendList);
            }
		}

		return $datas;
	}

	public function modal(
			Params $params,
			Settings $settings)
	{
		if (is_null($params->getSpecialPath())) {
			// SEO以外の情報は同じ
			return $this->result($params, $settings, true, true);
		} else {
			// SEO以外の情報は同じ
			return $this->resultSpl($params, $settings, true, true);
		}
	}

	public function detail(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		// 物件詳細
        // 4697 Check Kaiin Stop
        $getBukkenSt = time();
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setBukken($this->getBukken($params, $settings, $pNames, $datas));
        }
        $getBukkenDur = time() - $getBukkenSt;

		if ($params->isPcMedia()) {
			// 最近見た物件コマ
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
				if($getBukkenDur < 13 && !is_null($params->getHistory())) {	// NHP-5120 8s->13s
                    $datas->setHistoryKoma($this->searcher->getHistoryKoma($params, $settings, $pNames));
                } else {
                    // NHP-5107 : 詳細取得時間が8秒以上で最近見た物件数0
                    $bukkenZeroRes = [
                        'bukkens' => [],
                        'current_page' => 1,
                        'total_pages' => 0,
                        'per_page' => 5,
                        'facets' => [],
                        'pivot_facets' => [],
                        'errors' => [],
                        'total_count' => 0
                    ];
                    $datas->setHistoryKoma($bukkenZeroRes);
                }
            }
		}
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setCodeList(BApi\Code::getCode(array('group_nm' => 'sub_category')));
        }
		$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));
		return $datas;
	}

	public function getKenEnsenEkiCds(Settings $settings, $type) {
		$ekiList = $this->liner->getKenEnsenEkiList($settings, $type);

		$kenEnsenEkiCds = [];

		if(!empty($ekiList) && isset($ekiList['ensens'])) {
			foreach($ekiList['ensens'] as $ensen) {
				foreach($ensen['ekis'] as $eki) {
					$kenEnsenEkiCds[ $eki['code'] ] = $eki['ken_ensen_eki_cd'];
				}
			}
		}
		return $kenEnsenEkiCds;
	}

	public function condition(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

		// こだわり条件
		$searchFilter = $this->getSearchFilter($params, $settings, $pNames);
		$searchFilter = $searchFilter->setSearchFilterDefault($params);	
		$datas->setSearchFilter($searchFilter);
        if (!$params->getFType()) {
		    $datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));
        }

		return $datas;
	}

	public function conditionSpl(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
		// 検索用用絞り込み条件
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $searchFilter = $specialSetting->search_filter;
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
		// 表示用絞り込み条件
		$frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		// 検索パラメータ初期化
		$searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
		// 絞り込み条件検索後
		if ($methodSetting->hasInvidialMethod($specialSetting->method_setting) && !empty($params->getSearchFilter())) {
				$searchFilter->categories = $searchFilter->setSearchFilterInvidialAfterSearch($searchFilter, $params->getSearchFilter());
		}
		$frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
		// 絞り込み条件未設定項目ロード
		$frontSearchFilter->loadEnables($specialSetting->enabled_estate_type);
		// ATHOME_HP_DEV-6563 - apply with special
		if ($specialSetting->isSpecialShumokuSort()) {
			$frontSearchFilter = $frontSearchFilter->setSearchFilterDefault($params);
			$searchFilter = $searchFilter->setSearchFilterDefault($params);
		}
		$datas->setSearchFilter($searchFilter);
		$datas->setFrontSearchFilter($frontSearchFilter);

		return $datas;
	}

	public function favorite(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		// お気に入り物件一覧
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            if (is_null($params->getBukkenId())) {
                $datas->setBukkenList($this->emptyBukkenList);
            } else {
                $datas->setBukkenList($this->searcher->getFavoriteBukkenList($params, $settings, $pNames));
            }
        }

		return $datas;
	}

	public function history(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		// 最近見た物件一覧
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            if (is_null($params->getBukkenId())) {
                $datas->setBukkenList($this->emptyBukkenList);
            } else {
                $datas->setBukkenList($this->searcher->getHistoryBukkenList($params, $settings, $pNames));
            }
        }

		return $datas;
	}

	public function howtoinfo(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

		return $datas;
	}

	public function koma(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		// コマ物件一覧
		$datas->setBukkenList($this->searcher->getKomaBukkenList($params, $settings, $pNames));

		return $datas;
	}

	public function inqedit(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		// 問い合わせ物件一覧
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setBukkenList($this->searcher->getInqBukkenList($params, $settings, $pNames));
        }

		return $datas;
	}

	public function inqconfirm(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();
		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		return $datas;
	}

	public function inqcomplete(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();
		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		return $datas;
	}

	public function inqerror(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();
		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);
		return $datas;
	}

	public function count(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);


		$specialPath    = $params->getSpecialPath();
		// 特集
		if ($specialPath) {
			$specialRow     = $settings->special->findByUrl($specialPath);
			$specialSetting = $specialRow->toSettingObject();

			// 種目情報の取得
			$type_id = $specialSetting->enabled_estate_type;

			// こだわり条件
            $searchFilter = $specialSetting->search_filter;
            $methodSetting = Estate\SpecialMethodSetting::getInstance();

            if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
                $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
            }
			$frontSearchFilter = $searchFilter->toFrontSearchFilter($type_id);
			$searchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
			$frontSearchFilter->loadPopularItems($type_id);

			// 直接一覧検索
			if ($specialSetting->area_search_filter->has_search_page) {
				$bukkenList = $this->searcher->getCountSpecialDirectBukkenList($params, $settings->page, $settings->search, $specialSetting, $searchFilter, $frontSearchFilter, $pNames);
			}
			// 特集検索
			else {
				$bukkenList = $this->searcher->getCountSpecialBukkenList($params, $settings->page, $settings->search, $specialSetting, $searchFilter, $frontSearchFilter, $pNames);
			}
		}
		// 物件検索


		else {
			// 種目情報の取得
			$type_ct = $params->getTypeCt();
			$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);

			// こだわり条件
			$frontSearchFilter = new Front();
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
			$frontSearchFilter->loadPopularItems($type_id);

			// 検索
			$bukkenList = $this->searcher->getCountBukkenList($params, $settings->page, $settings->search, $frontSearchFilter, $pNames);
		}

		$total_count = $bukkenList['total_count'];

		$facet = new Services\BApi\SearchFilterFacetTranslator();
		$facet->setFacets($bukkenList['facets']);

		$frontSearchFilter->loadEnables($type_id);
		$searchFilterElement = new Services\Pc\Element\SearchFilter( $frontSearchFilter );

		$facetJson = $searchFilterElement->createFacetJson($facet);

		// 物件一覧
		$datas->setBukkenList($bukkenList);
		$datas->setFacetJson($facetJson);

		return $datas;
	}

	public function mapcenter(
			Params $params,
			Settings $settings)
	{
		$pNames = new ParamNames($params);
		$logic_map = new BApi\Map();

        // こだわり条件
        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        return $logic_map->getCenter($params, $settings, $pNames, $searchFilter);
	}
	public function mapcenterSpl(
			Params $params,
			Settings $settings)
	{
		$datas = new Datas();
		$pNames = new ParamNames($params);
		$logic_map = new BApi\Map();
		$datas->setParamNames($pNames);

		$specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
		$searchFilter = $specialSetting->search_filter;
        // フロント用こだわり条件を作成
        $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		// こだわり条件
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
		// 検索パラメータ設定
		$searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
		$frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
		// 未設定の希望条件をロード
		$frontSearchFilter->loadDesiredEnables($specialSetting->enabled_estate_type);
		// 人気のこだわり条件をロード
		$frontSearchFilter->loadPopularItems($specialSetting->enabled_estate_type);
		$datas->setSearchFilter($searchFilter);
		$datas->setFrontSearchFilter($frontSearchFilter);

		return $logic_map->getCenterSpl($params, $settings, $pNames, $searchFilter, $frontSearchFilter);

	}

	public function spatialEstate(
			Params $params,
			Settings $settings,
			$isModal = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$logic_map = new BApi\Map();
		$datas->setParamNames($pNames);

        // こだわり条件
        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
		$searchFilter = $searchFilter->setSearchFilterDefault($params);
		$datas->setSearchFilter($searchFilter);
		$bukkenlist = $logic_map->getBukkenList($params, $settings, $pNames, $searchFilter, $isModal);
		$datas->setBukkenList($bukkenlist);
		$datas->setSpatialEstate($bukkenlist);
		// 県コードがある場合のみ
		if ($pNames->getKenCd()) {
			$datas->setPrefSetting($this->getPrefSetting($settings->search, $params->getTypeCt()));
			$datas->setCityList($this->citier->getModalCityList($params, $settings, $pNames, $searchFilter, true));
		}
		return $datas;
	}

	public function spatialEstateSpl(
			Params $params,
			Settings $settings,
			$isModal = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$logic_map = new BApi\Map();
		$datas->setParamNames($pNames);

        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();

        // ATHOME_HP_DEV-5001
        // 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
        if($specialSetting->area_search_filter->choson_search_enabled == 0) {
            // 特集指定の物件種別の『物件検索設定』地域設定を取得する
            $estateClassArea = $settings->search->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;

            // 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
            if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
                if(!empty($estateClassArea->area_5->getAll())) {
                    $specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;
                    $specialSetting->area_search_filter->choson_search_enabled = 1;

                    if(!empty($estateClassArea->area_6->getAll())) {
                        $specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
                    }
                }
            }
        }

        // こだわり条件
        $searchFilter = $specialSetting->search_filter;
        // フロント用こだわり条件を作成
        $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
		// こだわり条件
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
        // 検索パラメータ設定
        $searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        $frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        // 未設定の希望条件をロード
        $frontSearchFilter->loadDesiredEnables($specialSetting->enabled_estate_type);
        // 人気のこだわり条件をロード
        $frontSearchFilter->loadPopularItems($specialSetting->enabled_estate_type);
		// ATHOME_HP_DEV-6563 - apply with special
		if ($specialSetting->isSpecialShumokuSort()) {
			$frontSearchFilter = $frontSearchFilter->setSearchFilterDefault($params);
			$searchFilter = $searchFilter->setSearchFilterDefault($params);
		}
        $datas->setSearchFilter($searchFilter);
        $datas->setFrontSearchFilter($frontSearchFilter);
        $bukkenlist = $logic_map->getBukkenListSpl($params, $settings, $pNames, $searchFilter, $frontSearchFilter, $isModal);
        $datas->setBukkenList($bukkenlist);
        $datas->setSpatialEstate($bukkenlist);

        // 県コードがある場合のみ
        if ($pNames->getKenCd()) {
            $datas->setPrefSetting($specialSetting->area_search_filter->area_1);
            $datas->setCityList($this->citier->getModalCityListSpl($params, $settings, $pNames, $searchFilter, true));
        }
        return $datas;
    }

	public function spatialMapEstatelist(
			Params $params,
			Settings $settings,
			$isModal = false)
	{
		$datas = new Datas();

		$pNames = new ParamNames($params);
		$logic_map = new BApi\Map();
		$datas->setParamNames($pNames);

        // こだわり条件
        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
		$datas->setSearchFilter($searchFilter);
		$bukkenlist = $logic_map->getBukkenListForIds($params, $settings, $pNames, $searchFilter, $isModal);
		$datas->setBukkenList($bukkenlist);
		return $datas;
	}

	private function getBukken(
			Params $params,
			Settings $settings,
            ParamNames $pNames,
            Datas $datas)
	{
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		$shumoku    = $pNames->getShumokuCd();
        // BApi用パラメータ作成
        $apiParam = new BApi\BukkenIdParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumokuCd($shumoku);
        $apiParam->setId($params->getBukkenId());
        $apiObj = new BApi\BukkenId();
        $bukken = $apiObj->search($apiParam, 'DETAIL');
        
        $result = null ;
    	if ( strpos( @$bukken[ 'errors' ][ 0 ][ 'message' ], '対象物件が存在しません' ) === false )
    	{
    		$result = $this->isKokaiBukken( $bukken, $settings ) ? $bukken : null ;
    	}

        return $result ;
	}

	/**
	 * 物件詳細で取得した物件が、公開してよい物件かどうかを判定する。
	 */
	private function isKokaiBukken($bukken, $settings) {
// 		「物件の公開判定ロジック」で、物件が「非公開」と判断されるのは以下の２パターン。
// 		　　a) 2次広告自動公開フラグ（niji_kokoku_jido_kokai_fl）が false かつ ER自グループフラグ（er_jisha_group_fl）が false
// 		　　b) ER自グループフラグが true かつ B100とE200のいずれも公開していない
// 二次広告F　　自社F　　C/ER公開
// false　　　　false　　確認不要　→　二次広告自動公開対象だった物件が何らかの理由により対象から外れたケースなので「非公開」
// false　　　　true　　 false　　 →　自社(自グループ)の物件なので、C公開かERのみ公開かをチェックし、どちらにも公開していなければ「非公開」
		if (isset($bukken['display_model'])
				&& isset($bukken['display_model']['kokaichu_kokais'])) {
			// ２次広告自動公開フラグ
			$isNijiKoukokuJidou = $bukken['display_model']['niji_kokoku_jido_kokai_fl'];
			// ER自グループフラグ
			$isErJishaGroup = $bukken['display_model']['er_jisha_group_fl'];

			if (! $isNijiKoukokuJidou) {
				// a) 2次広告自動公開フラグ（niji_kokoku_jido_kokai_fl）が false かつ ER自グループフラグ（er_jisha_group_fl）が false
				if (! $isErJishaGroup) {
					return false;
				} else {
				// b) ER自グループフラグが true かつ B100とE200のいずれも公開していない		　　　　
					foreach ($bukken['display_model']['kokaichu_kokais'] as $kokai) {
						$kokaisaki_cd = $kokai['kokaisaki_cd'];
						if ($kokaisaki_cd == 'B100' || $kokaisaki_cd == 'E200') {
							return true;
						}
					}
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * SEO さらに絞り込む リンクのデータ
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 * @throws Exception
	 */
	private function getSeoAnotherChoiceList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		$result = null;
        $searchCond = $settings->search;

        $comId = $params->getComId();
        // 検索タイプ
        $s_type = $params->getSearchType();

        // 駅・市区群取得用の種目情報の取得
        $type_ct = (array) $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct[0]);

        $shumoku    = $pNames->getShumokuCd();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        $eki_cd = $pNames->getEkiCd();
        // 検索タイプ：駅の場合は、駅ひとつ指定なので、駅ローマ字から沿線情報を取得
        if ($s_type == $params::SEARCH_TYPE_EKI) {
            $ekiObj = EnsenEki::getObjBySingle($eki_ct);
            $ensen_ct = $ekiObj->getEnsenCt();
            $ensenObj = Services\ServiceUtils::getEnsenObjByConst($ken_cd, $ensen_ct);
            $ensen_cd = $ensenObj->code;
        }

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();

		// さらに絞り込む のリンクデータ
		switch ($s_type)
		{
			case $params::SEARCH_TYPE_LINE:
				// 沿線の駅一覧
				// BApi用パラメータ作成
				$apiParam = new BApi\EkiListParams();
				$apiParam->setKenCd($ken_cd);
				$apiParam->setEnsenCd($ensen_cd);
				// 沿線コードをキーに、DB駅設定を取得
                if ($settings->special->getCurrentPagesSpecialRow()) {
                    $ensen_eki_cd = $settings->special->getEkiByEnsen((array)$ensen_cd);
                } else {
                    $ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, (array)$ensen_cd);
                }
				$apiParam->setEnsenEkiCd($ensen_eki_cd);
				// 結果JSONを元に要素を作成。
				$apiObj = new BApi\EkiList();
				$ekiList = $apiObj->getEki($apiParam, 'SEO_EKI_LIST');
				$result = array();
				if (isset($ekiList['ensens'][0]['ekis'])) {
				    $result = $ekiList['ensens'][0]['ekis'];
				    foreach ($result as $key => $val) {
				        $result[$key]['ensen_roman'] = $ekiList['ensens'][0]['ensen_roman'];
                    }
                }
				break;
			case $params::SEARCH_TYPE_SEIREI:
				// 政令指定都市の市区郡一覧
				// 検索設定の所在地コードを取得
				$settingShozaichi_cd = $searchCond->getShikugun($type_id, $ken_cd);
				// BApi用パラメータ作成
				$apiParam = new BApi\ShikugunListParams();
				$apiParam->setKenCd($ken_cd);
				$apiParam->setLocateRoman($locate_ct);
				$apiParam->setGrouping(
					BApi\ShikugunListParams::GROUPING_TYPE_LOCATE_CD);

				// 結果JSONを元に要素を作成。
				$apiObj = new BApi\ShikugunList();
				$shikugunList = $apiObj->getShikugun($apiParam, 'SEO_MCITY_LIST');

				$result = isset($shikugunList['shikuguns'][0]['locate_groups'][0]['shikuguns']) ?
						$shikugunList['shikuguns'][0]['locate_groups'][0]['shikuguns'] : array();
				break;
			case $params::SEARCH_TYPE_PREF:
				// 県のCMS市区郡一覧
				// 検索設定の所在地コードを取得
                if ($settings->special->getCurrentPagesSpecialRow()) {
                    $shozaichi_cd = $settings->special->getShikugun($ken_cd);
                } else {
                    $shozaichi_cd = $searchCond->getShikugun($type_id, $ken_cd);
                }
				// BApi用パラメータ作成
				$apiParam = new BApi\ShikugunListParams();
				$apiParam->setKenCd($ken_cd);
				$apiParam->setShozaichiCd($shozaichi_cd);
				// 結果JSONを元に要素を作成。
				$apiObj = new BApi\ShikugunList();
				$shikugunList = $apiObj->getShikugun($apiParam, 'SEO_CITY_LIST');
				$result = isset($shikugunList['shikuguns'][0]['shikuguns']) ?
						$shikugunList['shikuguns'][0]['shikuguns'] : array();
				break;
			case $params::SEARCH_TYPE_CITY:
			    $result = null;
			    if ($settings->special->getCurrentPagesSpecialRow()) {
			        $settingObject = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
                } else {
                    $settingObject = $settings->search->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
                }
                // 町名検索可能な場合は町名一覧を設定
                if ($settingObject->area_search_filter->canChosonSearch()) {
			        $apiParam = new BApi\ChosonListParams();
			        $apiParam->setShikugunCd($shikugun_cd);
                    $apiObj = new BApi\Choson();
                    $chosonList = $apiObj->getChosonList($apiParam, 'SEO_CHOSON_LIST');
                    $chosons = isset($chosonList['shikuguns'][0]['chosons']) ?
                        $chosonList['shikuguns'][0]['chosons'] : array();
                    $result = [];
                    // CMSの設定にある町名でフィルター
                    // 設定にない場合はすべての町名
                    if (
                        isset($settingObject->area_search_filter->area_5[$ken_cd][$shikugun_cd]) &&
                        is_array($settingObject->area_search_filter->area_5[$ken_cd][$shikugun_cd]) &&
                        $settingObject->area_search_filter->area_5[$ken_cd][$shikugun_cd]
                    ) {
                        $cmsChosonCodes = $settingObject->area_search_filter->area_5[$ken_cd][$shikugun_cd];
                        foreach ($chosons as $choson) {
                            if (in_array($choson['code'], $cmsChosonCodes)) {
                                $result[] = $choson;
                            }
                        }
                    } else {
                        $result = $chosons;
                    }
                }
			    break;
			case $params::SEARCH_TYPE_EKI:
			case $params::SEARCH_TYPE_CITY_POST:
			case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
				$result = null;
				break;
			default:
				throw new \Exception('Illegal Argument');
		}

		return $result;
	}

	// 物件一覧用
	private function getSearchFilter(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
        $type_ct = (array)$params->getTypeCt();
        $type_id = [];
        foreach ($type_ct as $ct) {
            $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
        }

		// こだわり条件
        $searchFilter = new Front();
        $searchFilter->setValues($type_id, $params->getSearchFilter());
        $searchFilter->loadDesiredEnables($type_id);
        $searchFilter->loadPopularItems($type_id);
        return $searchFilter;
	}

	private function getPrefSetting($searchCond, $type_ct)
	{
		$prefs = $searchCond->getPref(Estate\TypeList::getInstance()->getTypeByUrl($type_ct));
		return $prefs;
	}
    public function resultFreeword (
            Params $params,
            Settings $settings) 
    {
        $datas = new Datas();

		$pNames = new ParamNames($params);
		$datas->setParamNames($pNames);

        $searchFilter = $this->getSearchFilter($params, $settings, $pNames);
        $datas->setSearchFilter($searchFilter);
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setBukkenList($this->searcher->getBukkenListFreeword($params, $settings, $pNames, $searchFilter));
        }
        return $datas;
    }
    public function suggest(
            Params $params,
            Settings $settings)
    {
        $datas = new Datas();
        $pNames = new ParamNames($params);

		$datas->setParamNames($pNames);


		$specialPath    = $params->getSpecialPath();
		// 特集
		if ($specialPath) {
			$specialRow     = $settings->special->findByUrl($specialPath);
			$specialSetting = $specialRow->toSettingObject();

			// 種目情報の取得
			$type_id = $specialSetting->enabled_estate_type;

            // こだわり条件
            $searchFilter = $specialSetting->search_filter;
            $methodSetting = Estate\SpecialMethodSetting::getInstance();
            if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
                $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
            }
			$frontSearchFilter = $searchFilter->toFrontSearchFilter($type_id);
			$searchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
			$frontSearchFilter->loadPopularItems($type_id);

            $suggestList = $this->suggester->getSuggestSpecial($params, $settings->page, $settings->search, $specialSetting, $searchFilter, $frontSearchFilter, $pNames);
		}
		// 物件検索


		else {
            // 種目情報の取得
			$type_ct = (array)$params->getTypeCt();
            foreach ($type_ct as $ct) {
                $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
            }
           
			// こだわり条件
			$frontSearchFilter = new Front();
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
			$frontSearchFilter->loadPopularItems($type_id);

			// 検索
			$suggestList = $this->suggester->getSuggestList($params, $settings,$frontSearchFilter, $pNames);
		}
		// 物件一覧
		$datas->setSuggestList($suggestList);

		return $datas;
    }

    public function countBukken(
            Params $params,
			Settings $settings)
    {
        $datas = new Datas();

        $pNames = new ParamNames($params);

		$datas->setParamNames($pNames);


		$specialPath    = $params->getSpecialPath();
		// 特集
		if ($specialPath) {
			$specialRow     = $settings->special->findByUrl($specialPath);
			$specialSetting = $specialRow->toSettingObject();

			// 種目情報の取得
			$type_id = $specialSetting->enabled_estate_type;

            // こだわり条件
            $searchFilter = $specialSetting->search_filter;
            $methodSetting = Estate\SpecialMethodSetting::getInstance();
            if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
                $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
            }
			$frontSearchFilter = $searchFilter->toFrontSearchFilter($type_id);
			$searchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
            $frontSearchFilter->loadPopularItems($type_id);
            $count = $this->suggester->getCountSpecial($params, $settings->page, $settings->search, $specialSetting, $searchFilter, $frontSearchFilter, $pNames);
		}
		// 物件検索


		else {
            // 種目情報の取得
            $type_ct = (array)$params->getTypeCt();
            foreach ($type_ct as $ct) {
                $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
            }
           
			// こだわり条件
			$frontSearchFilter = new Front();
			$frontSearchFilter->setValues($type_id, $params->getSearchFilter());
			$frontSearchFilter->loadDesiredEnables($type_id);
			$frontSearchFilter->loadPopularItems($type_id);

			// 検索
			$count = $this->suggester->getCount($params, $settings, $frontSearchFilter, $pNames);
		}
		// 物件一覧
		$datas->setCount($count);

		return $datas;
    }

    public function searchHouse (
        Params $params,
        Settings $settings) 
    {
        $datas = new Datas();

        $pNames = new ParamNames($params);
        $datas->setParamNames($pNames);
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $specialSetting = new Special( $params->getSetting());
            $searchFilter = $specialSetting->search_filter;
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
            $datas->setCodeList(BApi\Code::getCode(array('group_nm' => 'sub_category')));
            if ($params->getIsCondition()) {
                $datas->setBukkenList($this->searcher->getBukkenListSearchCondition($params, $settings, $pNames));
            } else {
                if ($params->getBukkenNo() || $params->getBukkenId()) {
                    $datas->setBukkenList($this->searcher->getBukkenListSearchHouse($params, $settings, $pNames, $searchFilter));
                } else {
                    $datas->setBukkenList($this->searcher->getBukkenListHouseAll($params, $settings, $pNames, $searchFilter));
                }
            }
            
        }
        return $datas;
    }

    public function countCondition(
        Params $params,
        Settings $settings)
    {
        $datas = new Datas();

        $pNames = new ParamNames($params);
        $datas->setParamNames($pNames);
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            $datas->setCodeList(BApi\Code::getCode(array('group_nm' => 'sub_category')));
            if ($params->getIsCondition()) {
                $datas->setBukkenList($this->searcher->getCountBukkenListSearchCondition($params, $settings, $pNames));
            } 
        }
        return $datas;
    }
}