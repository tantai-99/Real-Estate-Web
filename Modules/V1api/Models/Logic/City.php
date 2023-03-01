<?php
namespace Modules\V1api\Models\Logic;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Model\Estate;
use Library\Custom\Estate\Setting\SearchFilter\Front;
use Modules\V1api\Models\BApi;
use Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract;

class City
{
	/**
	 * エリア選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getCityList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            Front $searchFilter=null,
            $spatial_flag = false)
	{
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();

		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		$settingObject = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();

		return $this->_getCityList(
            $comId, $kaiinLinkNo, $shumoku, $ken_cd, $spatial_flag,
            $searchFilter,
            $settingObject,
            false
        );
	}

    /**
     * @param Library\Custom\Estate\Setting\Basic|Library\Custom\Estate\Setting\Special $settingObject
     * @param int $prefCode
     * @param $procName
     * @return JSON
     */
	public function _getCityList(
	    $comId, $kaiinLinkNo, $shumoku, $ken_cd, $spatial_flag,
        $searchFilter,
        $settingObject,
        $isSpecial
    ) {
	    $procNamePrefix = $isSpecial ? 'SPL_' : '';
        $shikugunCodes = $settingObject->area_search_filter->getShikugunCodes($ken_cd);
        $areaSearchFilter = $settingObject->area_search_filter;

        $methodSetting = Estate\SpecialMethodSetting::getInstance();

        // 市区群コードまで指定して検索
        $apiParam = new BApi\ShikugunParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setGrouping($apiParam::GROUPING_TYPE_LOCATE_CD);
        $apiParam->setChizuKensakuFukaFl($spatial_flag);
        if($spatial_flag){
            $apiParam->setChizuHyojiKaFl(true);
        }
        $apiParam->setShozaichiCd($shikugunCodes);
        if ($isSpecial) {
            // 「2次広告自動公開物件」
            // カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
            // $apiParam->setNijiKokokuJidoKokaiFl($settingObject->second_estate_enabled);
            // 「２次広告物件（他社物件）のみ抽出」
            // 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
            // $apiParam->setOnlySecond($settingObject->only_second);
            // 「２次広告物件除いて（自社物件）抽出」
            // $apiParam->setExcludeSecond($settingObject->exclude_second);
            if ($methodSetting->hasInvidialMethod($settingObject->method_setting)) {
                $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($settingObject->houses_id));
            } else {
                // 「オーナーチェンジ」
                $apiParam->setOwnerChangeFl($settingObject->owner_change);
                // 「自社物件」「2次広告物件」「2次広告自動公開物件」
                $apiParam->setKokaiType($settingObject->jisha_bukken, $settingObject->niji_kokoku, $settingObject->niji_kokoku_jido_kokai);
                // 検索エンジンレンタルのみ公開の物件だけを表示する
                $apiParam->setOnlyEREnabled($settingObject->only_er_enabled);
                if ($methodSetting->hasRecommenedMethod($settingObject->method_setting)) {
                    $apiParam->setOsusumeKokaiFl('true');
                } else {
                    // 「エンド向け仲介手数料不要の物件」
                    $apiParam->setEndMukeEnabled($settingObject->end_muke_enabled);
                    // 「手数料」
                    $apiParam->setSetTesuryo($settingObject->tesuryo_ari_nomi, $settingObject->tesuryo_wakare_komi);
                    // 「広告費」
                    $apiParam->setKokokuhiJokenAri($settingObject->kokokuhi_joken_ari);
                }
            }
        }
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter, null, $isSpecial);
        }
        $apiObj = new BApi\Shikugun();
        $shikugunWithLocateCd = $apiObj->getShikugunWithLocateCd($apiParam, "{$procNamePrefix}CITY_SELECT");

        $hasChosonShikuguns = [];

        // ATHOME_HP_DEV-5001
        // 特集 & choson_search_enabled = 0 だが、area_5,6設定の場合は条件に含める
        $spChosonFlg = false;
        if ($isSpecial && $settingObject->area_search_filter->choson_search_enabled == 0) {
            // method: getCityListSpl で設定済み
            if(!empty($settingObject->area_search_filter->area_5->getAll())) {
                $spChosonFlg = true;
            }
        }

		// ATHOME_HP_DEV-5001
		// 町村コードの付与条件として、spChosonFlgも対象とする
        if ($settingObject->area_search_filter->canChosonSearch() || $spChosonFlg) {
            foreach ($shikugunCodes as $code) {
                if (
                    isset($areaSearchFilter->area_5[$ken_cd][$code]) &&
                    is_array($areaSearchFilter->area_5[$ken_cd][$code]) &&
                    isset($areaSearchFilter->area_5[$ken_cd][$code])
                ) {
                    $hasChosonShikuguns[] = $code;
                }
            }
        }

        // 町村設定がない場合は返却
        if (!$hasChosonShikuguns) {
            return $shikugunWithLocateCd;
        }

        /**
         * 町村設定がある場合は町名検索で検索する
         * (bukken_shozaichi_cdだとパラメータ多すぎてエラーになる)
         */
        // BApi用パラメータ作成
        $apiParam = new BApi\ChosonParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setShikugunCd($hasChosonShikuguns);
        $apiParam->setChizuKensakuFukaFl($spatial_flag);
        if ($isSpecial) {
            // 「2次広告自動公開物件」
            // カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
            // $apiParam->setNijiKokokuJidoKokaiFl($settingObject->second_estate_enabled);
            // 「２次広告物件（他社物件）のみ抽出」
            // 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
            // $apiParam->setOnlySecond($settingObject->only_second);
            // 「２次広告物件除いて（自社物件）抽出」
            // $apiParam->setExcludeSecond($settingObject->exclude_second);
            if ($methodSetting->hasInvidialMethod($settingObject->method_setting)) {
                $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($settingObject->houses_id));
            } else {
                // 「オーナーチェンジ」
                $apiParam->setOwnerChangeFl($settingObject->owner_change);
                // 「自社物件」「2次広告物件」「2次広告自動公開物件」
                $apiParam->setKokaiType($settingObject->jisha_bukken, $settingObject->niji_kokoku, $settingObject->niji_kokoku_jido_kokai);
                // 検索エンジンレンタルのみ公開の物件だけを表示する
                $apiParam->setOnlyEREnabled($settingObject->only_er_enabled);
                if ($methodSetting->hasRecommenedMethod($settingObject->method_setting)) {
                    $apiParam->setOsusumeKokaiFl('true');
                } else {
                    // 「エンド向け仲介手数料不要の物件」
                    $apiParam->setEndMukeEnabled($settingObject->end_muke_enabled);
                    // 「手数料」
                    $apiParam->setSetTesuryo($settingObject->tesuryo_ari_nomi, $settingObject->tesuryo_wakare_komi);
                    // 「広告費」
                    $apiParam->setKokokuhiJokenAri($settingObject->kokokuhi_joken_ari);
                }
            }
        }
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter, null, $isSpecial);
        }

        // 全会員リンク番号をキーに物件API：町名一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Choson();
        $chosonList = $apiObj->getChoson($apiParam, "{$procNamePrefix}CHOSON_SELECT");

        // 市区群ごとに件数を計算
        $shikugunCounts = [];
        foreach ($chosonList['shikuguns'] as $shikugun) {
            $count = 0;
            $selectableChosons = $areaSearchFilter->area_5[$ken_cd][$shikugun['shikugun_cd']];
            foreach ($shikugun['chosons'] as $choson) {
                if (in_array($choson['code'], $selectableChosons)) {

                    // 町字が選択されている場合
                    if(isset($areaSearchFilter->area_6[$ken_cd][$shikugun['shikugun_cd']][ $choson['code'] ])
                    && count($areaSearchFilter->area_6[$ken_cd][$shikugun['shikugun_cd']][ $choson['code'] ]) > 0) {
                        $selectableChoazas = $areaSearchFilter->area_6[$ken_cd][$shikugun['shikugun_cd']][ $choson['code'] ];
                        foreach($choson['choazas'] as $choaza) {
                            if(in_array($choaza['code'], $selectableChoazas)) {
                                $count += $choaza['count'];
                            }
                        }
                    } else {
                        $count += $choson['count'];
                    }
                }
            }
            $shikugunCounts[ $shikugun['shikugun_cd'] ] = $count;
        }

        // 計算結果を市区群に反映
        foreach($shikugunWithLocateCd['shikuguns'] as $prefKey => $pref) {
            foreach ($pref['locate_groups'] as $locateGroupKey => $locateGroup) {
                foreach ($locateGroup['shikuguns'] as $shikugunKey => $shikugun) {
                    if (isset($shikugunCounts[ $shikugun['code'] ])) {
                        $shikugunWithLocateCd['shikuguns'][$prefKey]['locate_groups'][$locateGroupKey]['shikuguns'][$shikugunKey]['count'] = $shikugunCounts[ $shikugun['code'] ];
                    }
                }
            }
        }

        return $shikugunWithLocateCd;
    }

	public function getCityListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            $searchFilter,
			$spatial_flag = false)
	{
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();

		/**
		 * 市区町村選択
		 */
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// 検索設定の所在地コードを取得

        if (!$specialSetting->area_search_filter->getShikugunCodes($ken_cd) ) {
            throw new \Exception('都道府県の市区郡が設定されていない', 404);
        }

		// ATHOME_HP_DEV-5001
		// 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
		if($specialSetting->area_search_filter->choson_search_enabled == 0) {
			// 特集指定の物件種別の『物件検索設定』地域設定を取得する
			$estateClassArea = $settings->search->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;

            // 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
			if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
				if(!empty($estateClassArea->area_5->getAll())) {
					$specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;

					if(!empty($estateClassArea->area_6->getAll())) {
						$specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
					}
				}
			}
		}

        return $this->_getCityList(
            $comId, $kaiinLinkNo, $shumoku, $ken_cd, $spatial_flag,
            $searchFilter,
            $specialSetting,
            true
        );
	}

    /**
     * 町名選択画面用のデータリストを返す。
     * @param Params $params
     * @param Settings $settings
     * @param ParamNames $pNames
     */
    public function getChosonList(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        Front $searchFilter=null,
        $spatial_flag = false)
    {
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $ken_cd  = $pNames->getKenCd();

        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

        $settingObject = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
        if (!$settingObject->area_search_filter->canChosonSearch()) {
            throw new \Exception('町名検索設定ではない', 404);
        }

        $shozaichi_cd = array();
        // リクエストパラメータ　ローマ字→コード
        foreach ($pNames->getShikuguns() as $shikugunObj) {
            array_push($shozaichi_cd, $shikugunObj->code);
        }

        // BApi用パラメータ作成
        $apiParam = new BApi\ChosonParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setShikugunCd($shozaichi_cd);
        $apiParam->setChizuKensakuFukaFl($spatial_flag);

        // こだわり条件
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter);
        }

        // 全会員リンク番号をキーに物件API：町名一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Choson();
        $chosonList = $this->filterSelectableChoson($apiObj->getChoson($apiParam, 'CHOSON_SELECT'), $ken_cd, $settingObject->area_search_filter);
        if (!$chosonList) {
            throw new \Exception('都道府県の町名が設定されていない', 404);
        }
        return $chosonList;
    }

    /**
     * 町名選択画面用のデータリストを返す。
     * @param Params $params
     * @param Settings $settings
     * @param ParamNames $pNames
     */
    public function getChosonListSpl(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        SearchFilterAbstract $searchFilter=null,
        $spatial_flag = false)
    {
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $ken_cd  = $pNames->getKenCd();

        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        if (!$specialSetting->area_search_filter->canChosonSearch()) {
            throw new \Exception('町名検索設定ではない', 404);
        }

        $shozaichi_cd = array();
        // リクエストパラメータ　ローマ字→コード
        foreach ($pNames->getShikuguns() as $shikugunObj) {
            array_push($shozaichi_cd, $shikugunObj->code);
        }

        // BApi用パラメータ作成
        $apiParam = new BApi\ChosonParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setShikugunCd($shozaichi_cd);
        $apiParam->setChizuKensakuFukaFl($spatial_flag);

        // 「2次広告自動公開物件」
        // カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
        // $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
        // 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
        // $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
        // $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id));
        } else {
            // 「オーナーチェンジ」
            $apiParam->setOwnerChangeFl($specialSetting->owner_change);
            // 「自社物件」「2次広告物件」「2次広告自動公開物件」
            $apiParam->setKokaiType($specialSetting->jisha_bukken, $specialSetting->niji_kokoku, $specialSetting->niji_kokoku_jido_kokai);
            // 検索エンジンレンタルのみ公開の物件だけを表示する
		    $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
            if ($methodSetting->hasRecommenedMethod($specialSetting->method_setting)) {
                $apiParam->setOsusumeKokaiFl('true');
            } else {
                // 「エンド向け仲介手数料不要の物件」
                $apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
                // 「手数料」
                $apiParam->setSetTesuryo($specialSetting->tesuryo_ari_nomi, $specialSetting->tesuryo_wakare_komi);
                // 「広告費」
                $apiParam->setKokokuhiJokenAri($specialSetting->kokokuhi_joken_ari);
                // 絞り込み検索設定
                $apiParam->setSearchFilter($specialSetting->search_filter, null, true);
            }
        }

        // こだわり条件
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter, null, true);
        }

        // 全会員リンク番号をキーに物件API：町名一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $areaSearchFilter = $specialSetting->area_search_filter;
        $apiObj = new BApi\Choson();
        $chosonList = $this->filterSelectableChoson($apiObj->getChoson($apiParam, 'CHOSON_SELECT'), $ken_cd, $areaSearchFilter);
        if (!$chosonList) {
            throw new \Exception('都道府県の町名が設定されていない', 404);
        }
        return $chosonList;
    }

    /**
     * @param array $chosonList
     * @param int $prefCode
     * @param Library\Custom\Estate\Setting\AreaSearchFilter\Basic $areaSearchFilter
     * @return array
     */
    public function filterSelectableChoson($chosonList, $prefCode, $areaSearchFilter) {
        $result = array();
        if ($areaSearchFilter->canChosonSearch()) {
            // 町村検索可能な場合DB設定に存在する町名リストを作成
            foreach ($chosonList['shikuguns'] as $shikugun) {
                if (
                    !isset($areaSearchFilter->area_5[$prefCode][$shikugun['shikugun_cd']]) ||
                    !is_array($areaSearchFilter->area_5[$prefCode][$shikugun['shikugun_cd']]) ||
                    !$areaSearchFilter->area_5[$prefCode][$shikugun['shikugun_cd']]
                ) {
                    // 町名設定がない場合は全町名選択可能
                    $result[] = $shikugun;
                    continue;
                }

                /**
                 * DB設定にある町名のみを抽出する
                 */
                $selectableChosons = $areaSearchFilter->area_5[$prefCode][$shikugun['shikugun_cd']];
                $chosons = array();
                foreach ($shikugun['chosons'] as $choson) {
                    if (in_array($choson['code'], $selectableChosons)) {

                        // 町字が選択されている場合
                        if($choson['count'] > 0
                        && count($choson['choazas'])
                        && isset($areaSearchFilter->area_6[$prefCode][$shikugun['shikugun_cd']][$choson['code']])
                        && count($areaSearchFilter->area_6[$prefCode][$shikugun['shikugun_cd']][$choson['code']]) > 0 ) {
                            $selectableChoazas = $areaSearchFilter->area_6[$prefCode][$shikugun['shikugun_cd']][$choson['code']];

                            $newCount = 0;
                            foreach($choson['choazas'] as $choaza) {
                                if(in_array($choaza['code'], $selectableChoazas, true)) {
                                    $newCount += $choaza['count'];
                                }
                            }
                            $choson['count'] = $newCount;
                        }

                        $chosons[] = $choson;
                    }
                }

                if (!$chosons) {
                    // 有効な町名が無い場合はSkip
                    continue;
                }

                $shikugun['chosons'] = $chosons;
                $result[] = $shikugun;
            }
        }
        return $result;
    }

	/**
	 * モーダルエリア選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getModalCityList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            Front $searchFilter,
            $spatial_flag = false)
	{
		// 通常のエリア選択画面用のデータリストと同じ
		return $this->getCityList($params, $settings, $pNames, $searchFilter, $spatial_flag);
	}

	public function getModalCityListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter,
			$spatial_flag = false)
	{
		// 通常のエリア選択画面用のデータリストと同じ
		return $this->getCityListSpl($params, $settings, $pNames, $searchFilter, $spatial_flag);
	}

	public function getModalChosonList(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        Front $searchFilter,
        $spatial_flag = false
    ) {
	    $result = [];
	    try {
	        // 市区群設定がある場合のみ
            $shikuguns = $pNames->getShikuguns();
            if ($shikuguns && count($shikuguns) <= 5) {
                // 通常のエリア選択画面用のデータリストと同じ
                $result = $this->getChosonList($params, $settings, $pNames, $searchFilter, $spatial_flag);
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

    public function getModalChosonListSpl(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        SearchFilterAbstract $searchFilter,
        $spatial_flag = false
    ) {
        $result = [];
        try {
            // 市区群設定がある場合のみ
            $shikuguns = $pNames->getShikuguns();
            if ($shikuguns && count($shikuguns) <= 5) {
                // 通常のエリア選択画面用のデータリストと同じ
                $result = $this->getChosonListSpl($params, $settings, $pNames, $searchFilter, $spatial_flag);
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

    public function getPrefRecommendByshikugun($params, $settings, $pNames, $searchFilter, $specialRow, $searchObject) {
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();

        /**
         * 市区町村選択
         */
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        $ken_cd = $searchObject->area_search_filter->area_1;
        $shumoku    = $pNames->getShumokuCd();
        $shikugunCodes = $searchObject->area_search_filter->area_2->getAll();

        $apiParam = new BApi\ShikugunParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setGrouping($apiParam::GROUPING_TYPE_LOCATE_CD);
        $apiParam->setShozaichiCd($shikugunCodes);
        // 「オーナーチェンジ」
        $apiParam->setOwnerChangeFl($specialRow->owner_change);
        // 「自社物件」「2次広告物件」「2次広告自動公開物件」
        $apiParam->setKokaiType($specialRow->jisha_bukken, $specialRow->niji_kokoku, $specialRow->niji_kokoku_jido_kokai);
        // 検索エンジンレンタルのみ公開の物件だけを表示する
        $apiParam->setOnlyEREnabled($specialRow->only_er_enabled);
        $apiParam->setOsusumeKokaiFl('true');
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter, null, true);
        }
        $apiObj = new BApi\Shikugun();
        $shikugunList = $apiObj->getShikugunWithLocateCd($apiParam, "SPL_CITY_SELECT");
        $prefs = [];
        foreach($shikugunList['shikuguns'] as $shikuguns) {
            $count = 0;
            foreach($shikuguns['locate_groups'] as $locate_groups) {
                foreach($locate_groups['shikuguns'] as $shikugun) {
                    $count += $shikugun['count'];
                }
            }
            if ($count > 0) {
                $prefs[] = $shikuguns['ken_cd'];
            }
        }
        return $prefs;
    }
}
