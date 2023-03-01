<?php
namespace Modules\V1api\Models\BApi;

use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\SearchCondSettings;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Model\Estate;
use Library\Custom\Estate\Setting\SearchFilter\Front;
use Modules\V1api\Models\BApi;
class BukkenSearch extends AbstractBApi
{

    /**
     * @param BukkenSearchParams
     * @return JSON
     */
    public function search(
        BukkenSearchParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_BUKKEN_SEARCH, $params, $procName);
    }

    /**
     * 物件APIに接続してお薦め物件一覧を取得します。
     */
    public function getRecommendList($params,  $settings, $shumoku)
    {
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new BukkenSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);

        // cmsで設定されている所在地を全設定
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

        // お薦め物件フラグ
        $apiParam->setOsusumeKokaiFl('true');
        // ２次広告自動公開フラグ
        $apiParam->setNijiKokokuJidoKokaiFl(false);
        $apiParam->setPerPage(10);
        $apiParam->setOrderBy(Params::SORT_RANDAM);

        return $this->search($apiParam, 'RECOMMEND_BUKKENLIST');
    }

    /**
     * 物件APIに接続して物件一覧を取得します。
     */
    public function getBukkenList(
            Params $params,
			Settings $settings,
			ParamNames $pNames,
    		Front $searchFilter)
    {
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
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


        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new BApi\BukkenSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        switch ($s_type)
        {
            /*
             * 沿線・駅検索
             */
            case $params::SEARCH_TYPE_LINE:
                // 沿線パラメータをキーに、駅設定を取得し検索実行。
                $ensen_cd_api = array();
                // リクエストパラメータ　ローマ字→コード
                $ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensen_ct);
                foreach ($ensenObjList as $ensenObj) {
                    array_push($ensen_cd_api, $ensenObj['code']);
                }
                // 沿線コードをキーに、DB駅設定を取得
				$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
                $apiParam->setEnsenEkiCd($ensen_eki_cd);
//                 $apiParam->setEnsenEkiCd($settings->search->getEkiByEnsen($type_id, $ensen_cd_api));
                break;
            case $params::SEARCH_TYPE_EKI:
                // 駅パラメータだけで検索実行。
                $ekiObjList = Services\ServiceUtils::getEkiListByConsts($eki_ct);
                // リクエストパラメータ　ローマ字→コード
                $eki_cd = array();
                foreach ($ekiObjList as $ekiObj) {
                    array_push($eki_cd, $ekiObj['code']);
                }
                $apiParam->setEnsenEkiCd($eki_cd);
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
					$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
                	$apiParam->setEnsenEkiCd($ensen_eki_cd);
//                     $apiParam->setEnsenEkiCd($settings->search->getEkiByEnsen($type_id, $ensen_cd_api));
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
                    $apiParam->setEnsenEkiCd($eki_cd);
                }
                break;
            /*
             * エリア検索
             */
            case $params::SEARCH_TYPE_PREF:   // $shikugun_ct is null
                // 町村コード付与
                $areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
                $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd);
                $apiParam->setShozaichiCd($shikugun_cd_api);
                break;
            case $params::SEARCH_TYPE_SEIREI: // $shikugun_ct is null
                // 政令指定都市コードをキーに、対象市区郡情報を取得
                // 対象市区郡から、市区郡設定に該当しないものを除去し検索実行
                $locateObjList = Services\ServiceUtils::getLocateObjByConst($ken_cd, $locate_ct);
                $searchCondShikuguns = $settings->search->getShikugun($type_id, $ken_cd);
                $shikugunObjList = $locateObjList->shikuguns;
                $shikugun_cd_api = array();
                foreach ($shikugunObjList as $shikugunObj) {
                    if (in_array($shikugunObj['code'], $searchCondShikuguns)) {
                        array_push($shikugun_cd_api, $shikugunObj['code']);
                    }
                }
                // 町村コード付与
                $areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
                $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
                $apiParam->setShozaichiCd($shikugun_cd_api);
                break;

            case $params::SEARCH_TYPE_CITY:   // $shikugun_ct is singullar or pullural
            case $params::SEARCH_TYPE_CITY_POST: // $shikugun_ct is singullar or pullural
                // 市区郡パラメータだけで検索実行。
                $shikugun_cd_api = array();
                // リクエストパラメータ　ローマ字→コード
                $shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $shikugun_ct);
                foreach ($shikugunObjList as $shikugunObj) {
                    array_push($shikugun_cd_api, $shikugunObj['code']);
                }
                // 町村コード付与
                $areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
                $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
                $apiParam->setShozaichiCd($shikugun_cd_api);
                break;
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $shozaichiCodes = [];
                // 町村コード付与
                $areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
                foreach ($pNames->getChosonCd() as $_shikugun_cd => $_chosons) {
                    foreach ($_chosons as $_choson_cd) {
                        $areaSearchFilter->getShozaichiCodesByChoson($shozaichiCodes, $ken_cd, $_shikugun_cd, $_choson_cd);
                    }
                }

                $apiParam->setShozaichiCd($shozaichiCodes);
                break;
            default:
                throw new \Exception('Illegal Value. s_type=' . $s_type);
        }
        $apiParam->setPerPage($params->getPerPage());
        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort($s_type,$params->getTypeCt()));
        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $searchFilter);
		if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setDislayModel();
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
            }
        }

        return $this->search($apiParam, 'BUKKENLIST');
    }

    /**
     * 物件APIに接続して物件一覧を取得します。
     */
    public function getKomaBukkenList(
        Params $params,
        Settings $settings,
        $specialSetting,
        $searchFilter,
        $frontSearchFilter)
    {
        $pageInitialSettings = $settings->page;
    	$searchCond = $settings->search;
        $pNames = new ParamNames($params);

        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

        // 検索処理
        $comId = $params->getComId();
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();

        // 種目情報の取得
        $shumoku    = $pNames->getShumokuCd();

        // BApi用パラメータ作成
        $apiParam = new BApi\BukkenSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        // 表示行数
        $row = $params->getKomaRows();
        // 並び順

        $sort = $params->getKomaSort();

        // columns
        $columns = 4;
        if ($params->getKomaColumns()) {
            $columns = $params->getKomaColumns();
        }
        switch ($sort)
        {
            case Estate\KomaSortOptionList::SORT_RANDOM:
                $apiParam->setPerPage($row * $columns);
            	$apiParam->setOrderBy(Params::SORT_RANDAM);
                break;
            case Estate\KomaSortOptionList::SORT_PRICE:
                $apiParam->setPerPage($row * $columns);
                $apiParam->setOrderBy($params::SORT_KAKAKU);
                break;
            case Estate\KomaSortOptionList::SORT_TIME:
                $apiParam->setPerPage($row * $columns);
                // 第１ソートが「新着」の場合は第２ソートに「ランダム」を設定する。
                $apiParam->setOrderBy($params::SORT_SHINCHAKU_DESC.",".Params::SORT_RANDAM);
                break;
            default:
                throw new \Exception('Illegal Argument. sort=' . $sort);
        }
        //○特集の場合
        //　1: 検索画面あり：「市区郡」から探す
        //　2: 検索画面あり：「沿線・駅」から探す
        //　3: 直接一覧：「市区郡」を対象にする
        //　4: 直接一覧：「沿線・駅」を対象にする
        if ($specialSetting->area_search_filter->hasAreaLineSearchType()) {
            $apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($specialSetting, $settings, $params, false, true));
        } else {
            $sp_sTypes = $specialSetting->area_search_filter->search_type;
            if (in_array(1, $sp_sTypes)) {
                // shikugun:choson
                $apiParam->setShozaichiCd($areaSearchFilter->getShozaichiCodes());
            } else if (in_array(3, $sp_sTypes)) {
                // shikugun
                // ATHOME_HP_DEV-5001
                // $apiParam->setShozaichiCd($this->mergeAllSettingsArea($areaSearchFilter->area_2));
                $apiParam->setShozaichiCd($areaSearchFilter->getShozaichiCodes());
            } else {
                // eki
                $apiParam->setEnsenEkiCd($this->mergeAllSettingsLine($areaSearchFilter->area_4));
            }
        }
        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true);

        // 「2次広告自動公開物件」
		// カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
		// $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
		// 「２次広告物件（他社物件）のみ抽出」
		// 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
		// $apiParam->setOnlySecond($specialSetting->only_second);
		// 「２次広告物件除いて（自社物件）抽出」
		// $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            // ATHOME_HP_DEV-5001
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

        $apiParam->setFulltext(urlencode($params->getFulltext()));
        // 全会員リンク番号をキーに物件APIにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\BukkenSearch();
        return $this->search($apiParam, 'BUKKEN_KOMA');
    }

    private function mergeAllSettingsArea($specialSettingArea)
    {
        $result = array();
        $kens = array_values((array) $specialSettingArea);
        foreach($kens as $citiesOfKen) {
            foreach($citiesOfKen as $city) {
            	$result[] = $city;
        	}
        }
        return $result;
    }

    private function mergeAllSettingsLine($specialSettingEki)
    {
        $result = array();
        $kens = array_values((array) $specialSettingEki);
        foreach($kens as $ekisOfKen) {
            foreach($ekisOfKen as $eki) {
            	$result[] = $eki;
        	}
        }
        return $result;
    }
}
