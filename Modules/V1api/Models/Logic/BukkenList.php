<?php
namespace Modules\V1api\Models\Logic;

use Modules\V1api\Models\BApi;
use Library\Custom\Model\Estate;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\ParamNames;
use Modules\V1api\Models\EnsenEki;
use Library\Custom\Estate\Setting\Special;
use Library\Custom\Estate\Setting\SearchFilter\Front;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\SearchCondSettings;
use Library\Custom\Estate\Setting\SearchFilter\Special as SearchFilterSpecial;

class BukkenList
{
	public function getInqBukkenList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
        // 検索処理
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new BApi\BukkenSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        // 最大５０件
        $apiParam->setPerPage('50');
        $apiParam->setId($params->getBukkenId());
        // 全会員リンク番号をキーに物件APIにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\BukkenSearch();
        return $apiObj->search($apiParam, 'INQ_BUKKENLIST');
	}

	public function getKomaBukkenList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
        $spPath = $params->getSpecialPath();
        if (! $spPath) throw new \Exception('Param:special_path is null.');
        $specialRow = $settings->special->findByUrl($spPath);
        if (empty($specialRow)) throw new \Exception('special row is null.');
        $specialSetting = $specialRow->toSettingObject();

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
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $searchFilter->categories = $searchFilter->setSearchFilterInvidial($searchFilter);
        }
        $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
        $searchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        $frontSearchFilter->setValues($specialSetting->enabled_estate_type, $params->getSearchFilter());
        $frontSearchFilter->loadDesiredEnables($specialSetting->enabled_estate_type);
        $frontSearchFilter->loadPopularItems($specialSetting->enabled_estate_type);

        // 検索
        $apiObj = new BApi\BukkenSearch();
        return $apiObj->getKomaBukkenList($params,$settings, $specialSetting, $searchFilter, $frontSearchFilter);
	}

	public function getHistoryBukkenList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		// 検索処理
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		// お気に入りは最大５０件
		$apiParam->setPerPage('50');
		$apiParam->setId($params->getBukkenId());

        // ソート順
		$apiParam->setOrderBy($params->getParam('personal_sort'));

		// 全会員リンク番号をキーに物件APIにアクセスし情報を取得
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\BukkenSearch();
		return $apiObj->search($apiParam, 'HISTORY_BUKKENLIST');
	}

	public function getFavoriteBukkenList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		// 検索処理
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

        // BApi用パラメータ作成
        $apiParam = new BApi\BukkenSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        // お気に入りは最大５０件
        $apiParam->setPerPage('50');
        $apiParam->setId($params->getBukkenId());

        // ソート順
        $apiParam->setOrderBy($params->getParam('personal_sort'));

        // 全会員リンク番号をキーに物件APIにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\BukkenSearch();
        return $apiObj->search($apiParam, 'FAVORITE_BUKKENLIST');
	}

	public function getHistoryKoma(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		// 検索処理
		$comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);

		// NHP-5120 物件詳細の種目コード取得
		$apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());

		// 最大５件
		$apiParam->setPerPage('5');
		$history = (array) $params->getHistory();
		$history = array_slice($history, 0, 5);
		$apiParam->setId($history);
		$apiObj = new BApi\BukkenSearch();
		return $apiObj->search($apiParam, 'HISTORY_BUKKENLIST');
	}

	public function getBukkenList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			Front $searchFilter)
	{
        $apiObj = new BApi\BukkenSearch();
        $bukkenList = $apiObj->getBukkenList($params, $settings, $pNames, $searchFilter);
        return $bukkenList;
	}

	/**
	 *
	 * @param Modules\V1api\Models\Params $params
	 * @param Modules\V1api\Models\Settings $settings
	 * @param $types string or array  type_idの配列。指定されなかった場合は、$params->getTypeCt()で物件種目を判定。
	 */
	public function getRecommendList($params, $settings, $types = null)
	{
		/**
		 * おすすめ物件
		 */
		// $typesを$shumokuに変換
		$shumoku = array();
		$typeList = Estate\TypeList::getInstance();

		if ($types) {
			$types = (array) $types;
			foreach($types as $type) {
				array_push($shumoku, $typeList->getShumokuCode($type));
			}
		} else {
			$typeCt = (array)$params->getTypeCt();
            foreach($typeCt as $ct) {
                array_push($shumoku, $typeList->getShumokuCode($typeList->getTypeByUrl($ct)));
            }
		}
		$apiObj = new BApi\BukkenSearch();

		return $apiObj->getRecommendList($params, $settings, $shumoku);
	}

	// @TODO countとロジックが同じ？
	public function getBukkenListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter,
			$frontSearchFilter)
	{
		$specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

		// ATHOME_HP_DEV-5001
		// 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
		$spChosonFlg = false;
		if($specialSetting->area_search_filter->choson_search_enabled == 0) {
			// 特集指定の物件種別の『物件検索設定』地域設定を取得する
			$estateClassArea = $settings->search->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;

			// 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
			if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
				if(!empty($estateClassArea->area_5->getAll())) {
					$specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;
					$spChosonFlg = true;

					if(!empty($estateClassArea->area_6->getAll())) {
						$specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
					}
				}
			}
		}

		// 検索タイプ
		$s_type = $params->getSearchType();
		// 種目情報の取得
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
			$ensen_nm = $ensenObj->ensen_nm;
		}

		// 市区町村の取得（複数指定の場合は使用できない）
		$shikugun_ct = $params->getShikugunCt(); // 単数or複数
		$shikugun_cd = $pNames->getShikugunCd();
		// 政令指定都市の取得（複数指定の場合は使用できない）
		$locate_ct = $params->getLocateCt(); // 単数or複数
		$locate_cd = $pNames->getLocateCd();

        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();
		$comId = $params->getComId();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		switch ($s_type)
		{
			/*
			 * 沿線・駅検索
			 */
			case $params::SEARCH_TYPE_LINE:
				// $ensen_ct isn't null. $eki_ct is null
				// 沿線パラメータをキーに、駅設定を取得し検索実行。
				$ensen_cd_api = array();
				// リクエストパラメータ　ローマ字→コード
				$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
				foreach ($ensenObjList as $ensenObj) {
					array_push($ensen_cd_api, $ensenObj['code']);
				}

				// 沿線パラメータをキーに、特集設定の駅コードを取得し設定する。
				$ensen_eki_cd_list = $this->getSplEkiSettings($areaSearchFilter->area_4, $ensen_cd_api);
				$apiParam->setEnsenEkiCd($ensen_eki_cd_list);
				break;
			case $params::SEARCH_TYPE_EKI:
				// 駅パラメータだけで検索実行。
				$ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
				// リクエストパラメータ　ローマ字→コード
				$eki_cd = array();
				foreach ($ekiObjList as $ekiObj) {
					array_push($eki_cd, $ekiObj['code']);
				}
				$apiParam->setEnsenEkiCd(implode(",", $eki_cd));
				break;
			case $params::SEARCH_TYPE_LINEEKI_POST:
				// 駅パラメータが指定されていたら、駅パラメータだけで検索実行。
				// 駅パラメータが無い場合、沿線パラメータをキーに、駅設定を取得し検索実行。

				if (is_null($eki_ct)) {
					// 沿線系の処理
					$ensen_cd_api = array();
					// リクエストパラメータ　ローマ字→コード
					$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
					foreach ($ensenObjList as $ensenObj) {
						array_push($ensen_cd_api, $ensenObj['code']);
					}
					// 沿線パラメータをキーに、特集設定の駅コードを取得し設定する。
					$ensen_eki_cd_list = $this->getSplEkiSettings($areaSearchFilter->area_4, $ensen_cd_api);
					$apiParam->setEnsenEkiCd($ensen_eki_cd_list);
				}
				else
				{
					// 駅系の処理
					$ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
					// リクエストパラメータ　ローマ字→コード
					$eki_cd = array();
					foreach ($ekiObjList as $ekiObj) {
						array_push($eki_cd, $ekiObj['code']);
					}
					$apiParam->setEnsenEkiCd(implode(",", $eki_cd));
				}
				break;
				/*
				 * エリア検索
				 */
			case $params::SEARCH_TYPE_PREF:   // $shikugun_ct is null
				// ATHOME_HP_DEV-5001
				// 物件検索設定の町村もくっつける
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 1;

				// 県コードをキーに、特集の市区郡設定を取得し設定
				$apiParam->setShozaichiCd($specialSetting->area_search_filter->getShozaichiCodes($ken_cd));

				// ATHOME_HP_DEV-5001 - 解除 -
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 0;

				break;
			case $params::SEARCH_TYPE_SEIREI: // $shikugun_ct is null
				// ATHOME_HP_DEV-5001
				// 物件検索設定の町村もくっつける
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 1;

				// 政令指定都市コードをキーに、対象市区郡情報を取得
				// 対象市区郡から、市区郡設定に該当しないものを除去し検索実行
				$locateObjList = Services\ServiceUtils::getLocateObjByConst($ken_cd, $locate_ct);
				// 特集の市区郡設定を取得する。
				$searchCondShikuguns = $areaSearchFilter->area_2[$ken_cd];
				$shikugunObjList = $locateObjList->shikuguns;
				$shikugun_cd_api = array();
				foreach ($shikugunObjList as $shikugunObj) {
					if (in_array($shikugunObj['code'], $searchCondShikuguns)) {
						array_push($shikugun_cd_api, $shikugunObj['code']);
					}
				}
                // 町村コード付与
                $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
                $apiParam->setShozaichiCd($shikugun_cd_api);

				// ATHOME_HP_DEV-5001 - 解除 -
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 0;

				break;

			case $params::SEARCH_TYPE_CITY:   // $shikugun_ct is singullar or pullural
			case $params::SEARCH_TYPE_CITY_POST: // $shikugun_ct is singullar or pullural
				// ATHOME_HP_DEV-5001
				// 物件検索設定の町村もくっつける
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 1;

				// 市区郡パラメータだけで検索実行。
				$shikugun_cd_api = array();
				// リクエストパラメータ　ローマ字→コード
				$shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $shikugun_ct);
				foreach ($shikugunObjList as $shikugunObj) {
					array_push($shikugun_cd_api, $shikugunObj['code']);
				}

                $areaSearchFilter = $specialSetting->area_search_filter;
                // 町村コード付与
                $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
                $apiParam->setShozaichiCd($shikugun_cd_api);

				// ATHOME_HP_DEV-5001 - 解除 -
                if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 0;

				break;
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $shozaichiCodes = [];
                // 町村コード付与
                foreach ($pNames->getChosonCd() as $shikugun_cd => $chosons) {
                    foreach ($chosons as $choson_cd) {
                        $areaSearchFilter->getShozaichiCodesByChoson($shozaichiCodes, $ken_cd, $shikugun_cd, $choson_cd);
                    }
                }
                $apiParam->setShozaichiCd($shozaichiCodes);
                break;
			default:
				throw new \Exception('Illegal Value. s_type=' . $s_type);
		}
		$apiParam->setPerPage($params->getPerPage());
		$apiParam->setPage($params->getPage());
		$apiParam->setOrderBy($params->getSort($params->getSearchType(),$params->getTypeCt()));
		// こだわり条件
		$apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true,$specialSetting->isSpecialShumokuSort());
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
            }
        }

        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
                $apiParam->setDislayModel();
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
            }
        }

		return $this->search($apiParam, 'SPL_BUKKEN_LIST');
	}

	private function getSplEkiSettings($area_4, $targetEnsenList)
	{
		$ensen_eki_cd_list = array();
		foreach ($area_4 as $ken => $ensen_ekiList) {
			foreach ($ensen_ekiList as $ensen_eki)
			{
				$ensen = substr($ensen_eki, 0, 4);
				if (in_array($ensen, $targetEnsenList)) {
					array_push($ensen_eki_cd_list, $ensen_eki);
				}
			}
		}
		return $ensen_eki_cd_list;
	}

	// @TODO countとロジックが同じ？
	public function getBukkenListSplDirect(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter,
			$frontSearchFilter)
	{
        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;
		// 種目情報の取得
		$shumoku    = $pNames->getShumokuCd();
		$shumoku_nm = $pNames->getShumokuName();

        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();
		$comId = $params->getComId();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());

        // エリア
		if ($specialSetting->area_search_filter->hasAreaLineSearchType()) {
			$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($specialSetting, $params, $settings));
		}else {
			if ($specialSetting->area_search_filter->hasAreaSearchType() ||
				$specialSetting->area_search_filter->hasSpatialSearchType()) {
				$apiParam->setShozaichiCd(implode(",", $areaSearchFilter->getShozaichiCodes()));
			} else {
				$apiParam->setEnsenEkiCd(implode(",", $areaSearchFilter->area_4->getAll()));
			}
		}
		$apiParam->setPerPage($params->getPerPage());
		$apiParam->setPage($params->getPage());
		$apiParam->setOrderBy($params->getSort($params->getSearchType()));
		// こだわり条件
		$apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true,$specialSetting->isSpecialShumokuSort());
        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
                $apiParam->setDislayModel();
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
            }
        }
		// 「2次広告自動公開物件」
		// カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
		// $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
		// 「２次広告物件（他社物件）のみ抽出」
		// 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
		// $apiParam->setOnlySecond($specialSetting->only_second);
		// 「２次広告物件除いて（自社物件）抽出」
		// $apiParam->setExcludeSecond($specialSetting->exclude_second);

        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setKenCd($areaSearchFilter->area_1);
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
            }
        }

		return $this->search($apiParam, 'SPL_DIRECT_BUKKENLIST');
	}


	/* -----------------------------------------------------------
	 * count用メソッド群
	 */
	private function search($apiParam, $procName) {
		$apiObj = new BApi\BukkenSearch();
		return $apiObj->search($apiParam, $procName);
	}
	/**
	 * 物件API用のパラメータを設定します。
	 */
	public function getCountBukkenList(
			Params $params,
			PageInitialSettings $pageInitialSettings,
			SearchCondSettings $searchCond,
			Front $searchFilter,
			$pNames)
	{
		// 種目情報の取得
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);

		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();

		// 会員の設定
		$comId = $params->getComId();
		$apiParam->setGroupId($comId);
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
		$apiParam->setKaiinLinkNo($kaiinLinkNo);

		// 物件種目の設定
		$apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());
		// 都道府県の取得
		$ken_cd  = $pNames->getKenCd();

		// 沿線・駅の設定
		if ($eki_ct = $params->getEkiCt()) {
			$ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
			// リクエストパラメータ　ローマ字→コード
			$eki_cd = array();
			foreach ($ekiObjList as $ekiObj) {
				array_push($eki_cd, $ekiObj['code']);
			}
			$apiParam->setEnsenEkiCd($eki_cd);
		}
		else if ($ensen_ct = $params->getEnsenCt()) {
			$ensen_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
			$ensen_eki_cd = $searchCond->getEkiByEnsen($type_id, $ensen_cd_api);
            $apiParam->setEnsenEkiCd($ensen_eki_cd);
		}
		// 市区郡の設定
		else if ($shikugun_ct = $params->getShikugunCt()) {
			$shikugun_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $shikugun_ct);
			foreach ($shikugunObjList as $shikugunObj) {
				array_push($shikugun_cd_api, $shikugunObj['code']);
			}
			$apiParam->setShozaichiCd($shikugun_cd_api);
		}
		// 政令指定都市の設定
		else if ($locate_ct = $params->getLocateCt()) {
			$locateObjList = Services\ServiceUtils::getLocateObjByConst($ken_cd, $locate_ct);
			$searchCondShikuguns = $searchCond->getShikugun($type_id, $ken_cd);
			$shikugunObjList = $locateObjList->shikuguns;
			$shikugun_cd_api = array();
			foreach ($shikugunObjList as $shikugunObj) {
				if (in_array($shikugunObj['code'], $searchCondShikuguns)) {
					array_push($shikugun_cd_api, $shikugunObj['code']);
				}
			}
			$apiParam->setShozaichiCd($shikugun_cd_api);
		}
		// 都道府県の設定
		else if ($ken_cd) {
			$apiParam->setShozaichiCd($searchCond->getShikugun($type_id, $ken_cd));
		}

		// こだわり条件
		$apiParam->setSearchFilter($searchFilter, $searchFilter);
        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
            	$apiParam->setDislayModel();
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
            }
        }

		// ファセットのみ
		$apiParam->setFacetsOnly();

		return $this->search($apiParam, 'COUNT_BUKKEN_LIST');
	}
	/**
	 * 物件API用のパラメータを設定します。
	 */
	public function getCountSpecialBukkenList(
			Params $params,
			PageInitialSettings $pageInitialSettings,
			SearchCondSettings $searchCond,
			Special $specialSetting,
			SearchFilterSpecial $searchFilter,
			Front $frontSearchFilter,
			$pNames)
	{
		// 種目情報の取得
		$type_id = $specialSetting->enabled_estate_type;

        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();

		// 会員の設定
		$comId = $params->getComId();
		$apiParam->setGroupId($comId);
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
		$apiParam->setKaiinLinkNo($kaiinLinkNo);

		// 物件種目の設定
		$apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());
		// 都道府県の取得
		$ken_cd  = $pNames->getKenCd();

		// 沿線・駅の設定
		if ($eki_ct = $params->getEkiCt()) {
			// 駅系の処理
			$ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
			// リクエストパラメータ　ローマ字→コード
			$eki_cd = array();
			foreach ($ekiObjList as $ekiObj) {
				array_push($eki_cd, $ekiObj['code']);
			}
			$apiParam->setEnsenEkiCd(implode(",", $eki_cd));
		}
		else if ($ensen_ct = $params->getEnsenCt()) {
			$ensen_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
			// 沿線パラメータをキーに、特集設定の駅コードを取得し設定する。
			$ensen_eki_cd_list = $this->getSplEkiSettings($areaSearchFilter->area_4, $ensen_cd_api);
			$apiParam->setEnsenEkiCd($ensen_eki_cd_list);
		}
		// 市区郡の設定
		else if ($shikugun_ct = $params->getShikugunCt()) {
			// 市区郡パラメータだけで検索実行。
			$shikugun_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $shikugun_ct);
			foreach ($shikugunObjList as $shikugunObj) {
				array_push($shikugun_cd_api, $shikugunObj['code']);
			}
			$apiParam->setShozaichiCd(implode(",", $shikugun_cd_api));
		}
		// 政令指定都市の設定
		else if ($locate_ct = $params->getLocateCt()) {
			// 政令指定都市コードをキーに、対象市区郡情報を取得
			// 対象市区郡から、市区郡設定に該当しないものを除去し検索実行
			$locateObjList = Services\ServiceUtils::getLocateObjByConst($ken_cd, $locate_ct);
			// 特集の市区郡設定を取得する。
			$searchCondShikuguns = $areaSearchFilter->area_2[$ken_cd];
			$shikugunObjList = $locateObjList->shikuguns;
			$shikugun_cd_api = array();
			foreach ($shikugunObjList as $shikugunObj) {
				if (in_array($shikugunObj['code'], $searchCondShikuguns)) {
					array_push($shikugun_cd_api, $shikugunObj['code']);
				}
			}
			$apiParam->setShozaichiCd(implode(",", $shikugun_cd_api));
		}
		// 都道府県の設定
		else if ($ken_cd) {
			// 県コードをキーに、特集の市区郡設定を取得し設定
			$apiParam->setShozaichiCd($areaSearchFilter->area_2[$ken_cd]);
		}

		// こだわり条件
		$apiParam->setSearchFilter($searchFilter, $frontSearchFilter);
        
        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
            	$apiParam->setDislayModel();
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
            }
        }
		// 「2次広告自動公開物件」
		// カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
		// $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
		// 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
        // $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
		// $apiParam->setExcludeSecond($specialSetting->exclude_second);

        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id, $areaSearchFilter->area_1));
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
            }
        }

		// ファセットのみ
		$apiParam->setFacetsOnly();

		return $this->search($apiParam, 'COUNT_BUKKEN_LIST');
	}
	/**
	 * 物件API用のパラメータを設定します。
	 */
	public function getCountSpecialDirectBukkenList(
			Params $params,
			PageInitialSettings $pageInitialSettings,
			SearchCondSettings $searchCond,
			Special $specialSetting,
			SearchFilterSpecial $searchFilter,
			Front $frontSearchFilter,
			$pNames)
	{
		// 種目情報の取得
		$type_id = $specialSetting->enabled_estate_type;

        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();

		// 会員の設定
		$comId = $params->getComId();
		$apiParam->setGroupId($comId);
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
		$apiParam->setKaiinLinkNo($kaiinLinkNo);

		// 物件種目の設定
		$apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());
		// 都道府県の取得
		$ken_cd  = $pNames->getKenCd();

		// 沿線・駅の設定
		if ($eki_ct = $params->getEkiCt()) {			
			// 駅系の処理
			$ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
			// リクエストパラメータ　ローマ字→コード
			$eki_cd = array();
			foreach ($ekiObjList as $ekiObj) {
				array_push($eki_cd, $ekiObj['code']);
			}
			$apiParam->setEnsenEkiCd(implode(",", $eki_cd));
		}
		else if ($ensen_ct = $params->getEnsenCt()) {
			$ensen_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
			// 沿線パラメータをキーに、特集設定の駅コードを取得し設定する。
			$ensen_eki_cd_list = $this->getSplEkiSettings($areaSearchFilter->area_4, $ensen_cd_api);
			$apiParam->setEnsenEkiCd($ensen_eki_cd_list);
		}
		// 市区郡の設定
		else if ($shikugun_ct = $params->getShikugunCt()) {
			// 市区郡パラメータだけで検索実行。
			$shikugun_cd_api = array();
			// リクエストパラメータ　ローマ字→コード
			$shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $shikugun_ct);
			foreach ($shikugunObjList as $shikugunObj) {
				array_push($shikugun_cd_api, $shikugunObj['code']);
			}
			$apiParam->setShozaichiCd(implode(",", $shikugun_cd_api));
		}
		// 政令指定都市の設定
		else if ($locate_ct = $params->getLocateCt()) {
			// 政令指定都市コードをキーに、対象市区郡情報を取得
			// 対象市区郡から、市区郡設定に該当しないものを除去し検索実行
			$locateObjList = Services\ServiceUtils::getLocateObjByConst($ken_cd, $locate_ct);
			// 特集の市区郡設定を取得する。
			$searchCondShikuguns = $areaSearchFilter->area_2[$ken_cd];
			$shikugunObjList = $locateObjList->shikuguns;
			$shikugun_cd_api = array();
			foreach ($shikugunObjList as $shikugunObj) {
				if (in_array($shikugunObj['code'], $searchCondShikuguns)) {
					array_push($shikugun_cd_api, $shikugunObj['code']);
				}
			}
			$apiParam->setShozaichiCd(implode(",", $shikugun_cd_api));
		}
		// 都道府県の設定
		else if ($ken_cd) {
			// 県コードをキーに、特集の市区郡設定を取得し設定
			$apiParam->setShozaichiCd($areaSearchFilter->area_2[$ken_cd]);
		}

		// こだわり条件
		$apiParam->setSearchFilter($searchFilter, $frontSearchFilter);
		// 「2次広告自動公開物件」
		// カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
		// $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
		// 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
        // $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
		// $apiParam->setExcludeSecond($specialSetting->exclude_second);

        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setKenCd($areaSearchFilter->area_1);
            $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id, $areaSearchFilter->area_1));
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
            }
        }

		// ファセットのみ
		$apiParam->setFacetsOnly();

		return $this->search($apiParam, 'COUNT_BUKKEN_LIST');
	}
    /**
     * API saerch freeword
     */
    public function getBukkenListFreeword(
            Params $params,
			Settings $settings,
			ParamNames $pNames,
			Front $searchFilter)
    {
		// 検索処理
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		$shumoku    = $pNames->getShumokuCd();
		if (is_array($shumoku)) {
			$shumoku = implode(',', $shumoku);
		}
		// BApi用パラメータ作成
		$apiParam = new BApi\BukkenSearchParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		$type_ct = (array) $params->getTypeCt();
		$settingRow = $settings->search->getSearchSettingRowByTypeCt(@$type_ct[0]);
		if ($settingRow) {
			// ATHOME_HP_DEV-5296
			$settingRow = $settingRow->toSettingObject();
			if ($settingRow->area_search_filter->hasAreaLineSearchType()) {
				$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $params, $settings));
			}else {
				if ($settingRow->area_search_filter->hasAreaSearchType() ||
					$settingRow->area_search_filter->hasSpatialSearchType()) {
					$apiParam->setShozaichiCd($settingRow->area_search_filter->getShozaichiCodes());
				} else {
					$apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
				}
			}
		}
		$apiParam->setPerPage($params->getPerPage());
		$apiParam->setPage($params->getPage());
		$apiParam->setOrderBy($params->getSort($params->getSearchType()));
		
		$apiParam->setSearchFilter($searchFilter, $searchFilter);
		if (!empty($params->getSearchFilter())) {
			if (!empty($params->getSearchFilter()["fulltext_fields"])) {
				$apiParam->setDislayModel();
				$apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
				$apiParam->setFulltextFields();
				$apiParam->setDataModelFulltextFields();
				$apiParam->fieldHighlightLenght();
			}
		}
		
		$apiObj = new BApi\BukkenSearch();
		return $apiObj->search($apiParam, 'BUKKENLIST');
    }

    public function getBukkenListHouseAll(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        $searchFilter)
    {
    // 検索処理
    $comId = $params->getComId();
    $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
    $shumoku    = $pNames->getShumokuCd();

    // BApi用パラメータ作成
    $apiParam = new BApi\BukkenSearchParams();
    $apiParam->setGroupId($comId);
    $apiParam->setKaiinLinkNo($kaiinLinkNo);
    $apiParam->setCsiteBukkenShumoku($shumoku);

    $type_ct = (array) $params->getTypeCt();
    $settingRow = $settings->search->getSearchSettingRowByTypeCt(@$type_ct[0])->toSettingObject();
    if ($settingRow) {
		if ($settingRow->area_search_filter->hasAreaLineSearchType()) {
			$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $settings, $params));
		}else {
			if ($settingRow->area_search_filter->hasAreaSearchType() ||
            	$settingRow->area_search_filter->hasSpatialSearchType()) {
            	$apiParam->setShozaichiCd($settingRow->area_search_filter->getShozaichiCodes());
			} else {
				$apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
			}
		}
    }
    $apiParam->setSearchFilter($searchFilter, $searchFilter, true);
    $apiParam->setPerPage($params->getPerPage());
    $apiParam->setPage($params->getPage());
    $apiParam->setOrderBy($params->getSortCMS());

    // 公開ステータスコード : 公開中 = '03';
    $apiParam->setKokaiPublishType();

    $apiObj = new BApi\BukkenSearch();
    return $apiObj->search($apiParam, 'BUKKENLIST');
    }

    public function getBukkenListSearchHouse(
        Params $params,
        Settings $settings,
        ParamNames $pNames,
        $searchFilter)
    {
    // 検索処理
    $comId = $params->getComId();
    $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
    // BApi用パラメータ作成
    $apiParam = new BApi\BukkenSearchParams();
    $apiParam->setGroupId($comId);
    $apiParam->setKaiinLinkNo($kaiinLinkNo);
	$type_ct = (array) $params->getTypeCt();
	$settingRow = $settings->search->getSearchSettingRowByTypeCt(@$type_ct[0]);
	if ($settingRow) {
		$settingRow = $settingRow->toSettingObject();
		if (($params->getBukkenNo() || $params->getBukkenId())) {
				$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $settings, $params));
		}else {
			if ($settingRow->area_search_filter->hasAreaSearchType() ||
				$settingRow->area_search_filter->hasSpatialSearchType()) {
				$apiParam->setShozaichiCd($settingRow->area_search_filter->getShozaichiCodes());
			} else {
				$apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
			}
		}
	} else {
		if ($params->getLinkPage()) {
			$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionAllSearch($params, $settings));
		}
	}

    if (!is_null($bukkenNo = $params->getBukkenNo()) || !is_null($bukkenId = Services\ServiceUtils::setBukkenIdPublish($params->getBukkenId()))) {
        $shumoku    = $pNames->getShumokuCd();
        $apiParam->setCsiteBukkenShumoku($shumoku);
        // 公開ステータスコード : 公開中 = '03';
        $apiParam->setKokaiPublishType();
    }

    if (! is_null($bukkenNo = $params->getBukkenNo())) {
        $apiParam->setBukkenNo($bukkenNo);
    }

    if (! is_null($bukkenId = Services\ServiceUtils::setBukkenIdPublish($params->getBukkenId()))) {
        // if (!$params->getLinkPage()) {
        //     $apiParam->setKenCd(implode(',', $settingRow->area_search_filter->area_1));
        // }
        $apiParam->setId($bukkenId);
    }
    $apiParam->setSearchFilter($searchFilter, $searchFilter, true);
    $apiParam->setPerPage($params->getPerPage());
    $apiParam->setPage($params->getPage());
    $apiParam->setOrderBy($params->getSortCMS());

    $apiObj = new BApi\BukkenSearch();
    return $apiObj->search($apiParam, 'BUKKENLIST');
    }

    public function getBukkenListSearchCondition(
        Params $params,
        Settings $settings,
        ParamNames $pNames)
    {
    // 検索処理
    $comId = $params->getComId();
    $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
    $shumoku    = $pNames->getShumokuCd();

    // BApi用パラメータ作成
    $apiParam = new BApi\BukkenSearchParams();
    $apiParam->setGroupId($comId);
    $apiParam->setKaiinLinkNo($kaiinLinkNo);
    $apiParam->setCsiteBukkenShumoku($shumoku);
    $specialSettingObject = new Special( $params->getSetting());
    if ($specialSettingObject->area_search_filter->hasAreaSearchTypeCondition()) {
        $apiParam->setShozaichiCd(implode(',', $specialSettingObject->area_search_filter->getShozaichiCodesCondition()));
    } elseif ($specialSettingObject->area_search_filter->hasLineSearchTypeCondition()) {
        $apiParam->setEnsenEkiCd(implode(",", $specialSettingObject->area_search_filter->area_4->getAll()));
    } else {
        $type_ct = (array) $params->getTypeCt();
        $settingRow = $settings->search->getSearchSettingRowByTypeCt(@$type_ct[0])->toSettingObject();
		// ATHOME_HP_DEV-6564
        if ($settingRow->area_search_filter->hasAreaLineSearchType()) {
			$apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $params, $settings));
		} else {
	        if ($settingRow->area_search_filter->hasAreaSearchType() ||
	            $settingRow->area_search_filter->hasSpatialSearchType()) {
	                $apiParam->setShozaichiCd(implode(',', $settingRow->area_search_filter->getShozaichiCodes()));
	        } else {
	            $apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
	        }
	    }
    }
    $apiParam->setPerPage($params->getPerPage());
    $apiParam->setPage($params->getPage());
    $apiParam->setOrderBy($params->getSortCMS());
    // こだわり条件
	$apiParam->setSearchFilter($specialSettingObject->search_filter, $specialSettingObject->search_filter, true);
    // 検索エンジンレンタルのみ公開の物件だけを表示する
    $apiParam->setOnlyEREnabled($specialSettingObject->only_er_enabled);
    // 「エンド向け仲介手数料不要の物件」
    $apiParam->setEndMukeEnabled($specialSettingObject->end_muke_enabled);

    // 「自社物件」「2次広告物件」「2次広告自動公開物件」
    $apiParam->setKokaiType($specialSettingObject->jisha_bukken, $specialSettingObject->niji_kokoku, $specialSettingObject->niji_kokoku_jido_kokai);

    // 「手数料」
    $apiParam->setSetTesuryo($specialSettingObject->tesuryo_ari_nomi, $specialSettingObject->tesuryo_wakare_komi);
    // 「広告費」
    $apiParam->setKokokuhiJokenAri($specialSettingObject->kokokuhi_joken_ari);

    // 「オーナーチェンジ」
    $apiParam->setOwnerChangeFl($specialSettingObject->owner_change);
    
    // 公開ステータスコード : 公開中 = '03';
    $apiParam->setKokaiPublishType();

    $apiObj = new BApi\BukkenSearch();
    return $apiObj->search($apiParam, 'BUKKENLIST');
    }

    public function getCountBukkenListSearchCondition(
        Params $params,
        Settings $settings,
        ParamNames $pNames)
    {
    // 検索処理
    $comId = $params->getComId();
    $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
    $shumoku    = $pNames->getShumokuCd();
	
    // BApi用パラメータ作成
    $apiParam = new BApi\BukkenSearchParams();
    $apiParam->setGroupId($comId);
    $apiParam->setKaiinLinkNo($kaiinLinkNo);
    $apiParam->setCsiteBukkenShumoku($shumoku);
    $specialSettingObject = new Special( $params->getSetting());
    if ($specialSettingObject->area_search_filter->hasAreaSearchTypeCondition()) {
        $apiParam->setShozaichiCd(implode(',', $specialSettingObject->area_search_filter->getShozaichiCodesCondition()));
    } elseif ($specialSettingObject->area_search_filter->hasLineSearchTypeCondition()) {
        $apiParam->setEnsenEkiCd(implode(",", $specialSettingObject->area_search_filter->area_4->getAll()));
    } else {
        $type_ct = (array) $params->getTypeCt();
        $settingRow = $settings->search->getSearchSettingRowByTypeCt(@$type_ct[0])->toSettingObject();
        if ($settingRow->area_search_filter->hasAreaSearchType() ||
            $settingRow->area_search_filter->hasSpatialSearchType()) {
                $apiParam->setShozaichiCd(implode(',', $settingRow->area_search_filter->getShozaichiCodes()));
        } else {
            $apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
        }
    }
    $apiParam->setOrderBy($params->getSortCMS());
    // こだわり条件
	$apiParam->setSearchFilter($specialSettingObject->search_filter, $specialSettingObject->search_filter, true);
    // 検索エンジンレンタルのみ公開の物件だけを表示する
    $apiParam->setOnlyEREnabled($specialSettingObject->only_er_enabled);
    // 「エンド向け仲介手数料不要の物件」
    $apiParam->setEndMukeEnabled($specialSettingObject->end_muke_enabled);

    // 「自社物件」「2次広告物件」「2次広告自動公開物件」
    $apiParam->setKokaiType($specialSettingObject->jisha_bukken, $specialSettingObject->niji_kokoku, $specialSettingObject->niji_kokoku_jido_kokai);

    // 「手数料」
    $apiParam->setSetTesuryo($specialSettingObject->tesuryo_ari_nomi, $specialSettingObject->tesuryo_wakare_komi);
    // 「広告費」
    $apiParam->setKokokuhiJokenAri($specialSettingObject->kokokuhi_joken_ari);

    // 「オーナーチェンジ」
    $apiParam->setOwnerChangeFl($specialSettingObject->owner_change);

    // 公開ステータスコード : 公開中 = '03';
    $apiParam->setKokaiPublishType();

    $apiObj = new BApi\BukkenSearch();
    return $apiObj->search($apiParam, 'COUNT_BUKKENLIST');
    }

}