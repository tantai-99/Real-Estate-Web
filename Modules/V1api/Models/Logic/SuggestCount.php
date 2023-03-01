<?php
namespace Modules\V1api\Models\Logic;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\SearchCondSettings;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Estate\Setting;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\BApi;
class SuggestCount
{
    /**
	 * エリア選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getSuggestList(
        Params $params,
        Settings $settings,
        Setting\SearchFilter\Front $searchFilter,
        $pNames)
    {
        $pageInitialSettings = $settings->page;
        $searchCond = $settings->search;
        $type_ct = $params->getTypeCt();
        if (!is_array($type_ct)) {
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        }
        $shumoku    = $pNames->getShumokuCd();
        if (is_array($shumoku)) {
            $shumoku = implode(',', $shumoku);
        }
        $ken_cd  = $pNames->getKenCd();

        $comId = $params->getComId();
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
        $apiParam = new BApi\SuggestCountParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
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
            $areaSearchFilter = $searchCond->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
            $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
            if ($chosonCodes = $params->getChosonCt()) {
                $shikugun_cd_api = Services\ServiceUtils::getShikugunCdApiList($chosonCodes, $shikugunObjList);
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
        } else {
            $type_ct = (array) $type_ct;
            $settingRow = $searchCond->getSearchSettingRowByTypeCt(@$type_ct[0]);
            if ($settingRow) {
                // ATHOME_HP_DEV-5296
                $settingRow = $settingRow->toSettingObject();
                if ($settingRow->area_search_filter->hasAreaLineSearchType()) {
                    $apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $settings, $params));
                } else {
                    if ($settingRow->area_search_filter->hasAreaSearchType() ||
                        $settingRow->area_search_filter->hasSpatialSearchType()) {
                        $apiParam->setShozaichiCd($settingRow->area_search_filter->getShozaichiCodes());
                    } else {
                        // 沿線・駅のみ
                        $apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
                    }
                }
            }
        }
        if ($params->getFromSearchmap()) {
            $apiParam->setChizuKensakuFukaFl(true);
            $apiParam->setChizuHyojiKaFl(true);
        }
        $apiParam->setSearchFilter($searchFilter, $searchFilter);
        $fulltext = trim($params->getFulltext());
        if($fulltext) {
            $apiParam->setFulltext(urlencode($fulltext));
            $apiParam->setFulltextFields();
            $apiParam->setDataModelFulltextFields();
        }
        $apiParam->setFacetsOnly();
        $apiObj = new BApi\SuggestCount();
        return $apiObj->suggest($apiParam, 'BUKKEN_SUGGEST_LIST');
    }

    public function getSuggestSpecial(
        Params $params,
        PageInitialSettings $pageInitialSettings,
        SearchCondSettings $searchCond,
        Setting\Special $specialSetting,
        Setting\SearchFilter\Special $searchFilter,
        Setting\SearchFilter\Front $frontSearchFilter,
        $pNames)
{
    // 種目情報の取得
    $type_id = $specialSetting->enabled_estate_type;

    $methodSetting = Estate\SpecialMethodSetting::getInstance();
    $areaSearchFilter = $specialSetting->area_search_filter;

    // BApi用パラメータ作成
    $apiParam = new BApi\SuggestCountParams();

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
        if ($chosonCodes = $params->getChosonCt()) {
            $shikugun_cd_api = Services\ServiceUtils::getShikugunCdApiList($chosonCodes, $shikugunObjList);
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
    $apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true);
    
    $fulltext = trim($params->getFulltext());
    if($fulltext) {
        $apiParam->setFulltext(urlencode($fulltext));
        $apiParam->setFulltextFields();
        $apiParam->setDataModelFulltextFields();
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

    if ($params->getFromSearchmap()) {
        $apiParam->setChizuKensakuFukaFl(true);
        $apiParam->setChizuHyojiKaFl(true);
    }

    // ファセットのみ
    $apiParam->setFacetsOnly();

    $apiObj = new BApi\SuggestCount();
    return $apiObj->suggest($apiParam, 'BUKKEN_SUGGEST_LIST');
}

    public function getCount(
        Params $params,
        Settings $settings,
        Setting\SearchFilter\Front $searchFilter,
        $pNames)
    {
        $pageInitialSettings = $settings->page;
        $searchCond = $settings->search;
        $type_ct = $params->getTypeCt();
        if (!is_array($type_ct)) {
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        }
        $shumoku    = $pNames->getShumokuCd();
        if (is_array($shumoku)) {
            $shumoku = implode(',', $shumoku);
        }
        $ken_cd  = $pNames->getKenCd();

        $comId = $params->getComId();
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
        $apiParam = new BApi\SuggestCountParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
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
            $areaSearchFilter = $searchCond->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
            $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
            if ($chosonCodes = $params->getChosonCt()) {
                $shikugun_cd_api = Services\ServiceUtils::getShikugunCdApiList($chosonCodes, $shikugunObjList);
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
            $areaSearchFilter = $searchCond->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
            $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
            $apiParam->setShozaichiCd($shikugun_cd_api);
        }
        // 都道府県の設定
        else if ($ken_cd) {
            $apiParam->setShozaichiCd($searchCond->getShikugun($type_id, $ken_cd));
        } else {
            $type_ct = (array) $type_ct;
            $settingRow = $searchCond->getSearchSettingRowByTypeCt(@$type_ct[0]);
            $settingRow = $settingRow->toSettingObject();
            if ($settingRow) {
                // ATHOME_HP_DEV-5296
                if ($settingRow->area_search_filter->hasAreaLineSearchType()) {
                    $apiParam->setConditionAllSearch(Services\ServiceUtils::getConditionSearch($settingRow, $settings, $params));
                } else {
                    if ($settingRow->area_search_filter->hasAreaSearchType() ||
                        $settingRow->area_search_filter->hasSpatialSearchType()) {
                        $apiParam->setShozaichiCd($settingRow->area_search_filter->getShozaichiCodes());
                    } else {
                        // 沿線・駅のみ
                        $apiParam->setEnsenEkiCd(implode(",", $settingRow->area_search_filter->area_4->getAll()));
                    }
                }
            }
        }
        if ($params->getFromSearchmap()) {
            $apiParam->setChizuKensakuFukaFl(true);
            $apiParam->setChizuHyojiKaFl(true);
        }
        $apiParam->setSearchFilter($searchFilter, $searchFilter);
        $fulltext = trim($params->getFulltext());
        if($fulltext) {
            $apiParam->setFulltext(urlencode($fulltext));
            $apiParam->setFulltextFields();
            $apiParam->setDataModelFulltextFields();
        }
        $apiParam->setFacetsOnly();
        $apiObj = new BApi\SuggestCount();
        return $apiObj->count($apiParam, 'BUKKEN_COUNT');
    }

    public function getCountSpecial(
        Params $params,
        PageInitialSettings $pageInitialSettings,
        SearchCondSettings $searchCond,
        Setting\Special $specialSetting,
        Setting\SearchFilter\Special $searchFilter,
        Setting\SearchFilter\Front $frontSearchFilter,
        $pNames)
{
    // 種目情報の取得
    $type_id = $specialSetting->enabled_estate_type;

    $methodSetting = Estate\SpecialMethodSetting::getInstance();
    $areaSearchFilter = $specialSetting->area_search_filter;

    // BApi用パラメータ作成
    $apiParam = new BApi\SuggestCountParams();

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
        $areaSearchFilter = $specialSetting->area_search_filter;

        // ATHOME_HP_DEV-5001
		// 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
        if($areaSearchFilter->choson_search_enabled == 0) {
            // 特集指定の物件種別の『物件検索設定』地域設定を取得する
            $estateClassArea = $searchCond->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;

            // 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
            if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
                if(!empty($estateClassArea->area_5->getAll())) {
                    $specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;

                    // 特集は『町名まで検索させる』で件数検索
                    $specialSetting->area_search_filter->choson_search_enabled = 1;

                    if(!empty($estateClassArea->area_6->getAll())) {
                        $specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
                    }
                }
            }
        }
        $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
        if ($chosonCodes = $params->getChosonCt()) {
            $shikugun_cd_api = Services\ServiceUtils::getShikugunCdApiList($chosonCodes, $shikugunObjList);
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
        $shikugun_cd_api = $areaSearchFilter->getShozaichiCodes($ken_cd, $shikugun_cd_api);
        $apiParam->setShozaichiCd(implode(",", $shikugun_cd_api));
    }
    // 都道府県の設定
    else if ($ken_cd) {
        // 県コードをキーに、特集の市区郡設定を取得し設定
        $apiParam->setShozaichiCd($areaSearchFilterr->area_2[$ken_cd]);
    } else {
        if ($specialSetting->area_search_filter->hasAreaSearchType()) {
			$apiParam->setShozaichiCd(implode(",", $areaSearchFilter->getShozaichiCodes()));
		}
		// 沿線
		else {
			$apiParam->setEnsenEkiCd(implode(",", $areaSearchFilter->area_4->getAll()));
		}
    }
    if ($params->getFromSearchmap()) {
        $apiParam->setChizuKensakuFukaFl(true);
        $apiParam->setChizuHyojiKaFl(true);
    }
    // こだわり条件
    $apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true);
    
    $fulltext = trim($params->getFulltext());
    if($fulltext) {
        $apiParam->setFulltext(urlencode($fulltext));
        $apiParam->setFulltextFields();
        $apiParam->setDataModelFulltextFields();
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

    $apiObj = new BApi\SuggestCount();
    return $apiObj->count($apiParam, 'BUKKEN_COUNT');
    }
}