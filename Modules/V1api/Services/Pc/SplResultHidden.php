<?php

namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\EnsenEki;
use Library\Custom\Model\Estate;

class SplResultHidden extends Services\AbstractElementService
{
    public $isModal = false;
    public $hidden;

    public function create(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $this->hidden = $this->hidden($params, $settings, $datas);
    }

    public function check(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
    }

    private function hidden(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            return;
        }
        $doc = $this->getTemplateDoc("/result/hidden.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;

        $pNames = $datas->getParamNames();

        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        // 種目情報の取得
        $type_id = $specialSetting->enabled_estate_type;
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();
        // 検索タイプ
        $s_type = $params->getSearchType();

        /*
         * 都道府県選択の作成
         */
        $prefs = $datas->getPrefSetting();
        switch ($s_type) {
            case $params::SEARCH_TYPE_MAP:
                $prefMaker = new Element\PrefMap();
                break;
            default:
                $prefMaker = new Element\Pref();
                break;
        }
        if (!empty($prefs)) {
            $doc["table.element-search-table"]->replaceWith($prefMaker->createElement($prefs, $specialRow->filename, $ken_nm));
        }

        switch ($s_type) {
            case $params::SEARCH_TYPE_LINE:
            case $params::SEARCH_TYPE_EKI:
            case $params::SEARCH_TYPE_LINEEKI_POST:
                $this->createLineHidden($doc, $params, $settings, $datas);
                break;
            case $params::SEARCH_TYPE_CITY:
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_SEIREI:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_MAP:
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                if ($params->isOnlyChosonModal()) {
                    $this->createChosonHidden($doc, $params, $settings, $datas);
                } else {
                    $this->createAreaHidden($doc, $params, $settings, $datas);
                }
                break;
        }

        // モーダルの場合は、こだわり条件を作らない
        if (!$this->isModal) {
            // こだわり条件
            $searchFilter = $datas->getFrontSearchFilter();
            $bukkenList = $datas->getBukkenList();
            $total_count = $bukkenList['total_count'];


            $searchFilterElement = new Element\SearchFilter($searchFilter);
            $searchFilterElement->setSearchFilterSplCms($specialSetting->search_filter);
            $facet = new Services\BApi\SearchFilterFacetTranslator();
            $facet->setFacets($bukkenList['facets']);
            $searchFilterElement->renderKodawariModal($type_id, $total_count, $facet, $doc);
        }

        return $doc->html();
    }


    public function createLineHidden(
        $doc,
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();
        $ken_ct = $params->getKenCt();

        $pNames = $datas->getParamNames();

        $ensenMaker = new Element\Ensen();
        $ensenWithGroup = $datas->getLineList();
        $ensenCountList = $datas->getLineCountList();
        $searchAreaElem = $ensenMaker->createElement($ensenWithGroup, $ensenCountList, $specialRow->filename, $ken_ct, false);

        // ◯◯のすべての物件を見る リンク削除
        $searchAreaElem['.link-all-result']->remove();

        // リクエストパラメータで指定された値は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        $doc['div.search-modal-railway div.element-search-area']->replaceWith($searchAreaElem);


        /*
         * 駅選択の作成
         */
        $ekiMaker = new Element\Eki();
        $ekiWithEnsen = $datas->getEkiList();
        $ekiSettingOfKen = $datas->getEkiSettingOfKen();
        $searchAreaElem = $ekiMaker->createElement($ekiWithEnsen, $specialRow->filename, $ken_ct, false, $ekiSettingOfKen);

        // ◯◯のすべての物件を見る リンク削除
        $searchAreaElem['.link-all-result']->remove();

        // リクエストパラメータで指定された値は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        // 　ー 沿線選択
        $ensenCtList = $params->getEnsenCt();
        if (empty($ensenCtList)) {
            // 駅複数の場合がある。
            $eki_ct = $params->getEkiCt();
            // 駅ローマ字より沿線コードを取得する
            $ensenCtList = array();
            foreach ((array) $eki_ct as $eki) {
                $ekiObj = EnsenEki::getObjBySingle($eki);
                array_push($ensenCtList, $ekiObj->getEnsenCt());
            }
        }
        if (!is_array($ensenCtList)) {
            $ensenCtList = is_null($ensenCtList) ? array() : array($ensenCtList);
        }
        $railElem = $doc["div.search-modal-railway div.element-search-area"];
        foreach ($ensenCtList as $ensenCt) {
            $railElem["input[value=${ensenCt}]"]->attr('checked', 'checked');
        }

        // ー 駅選択
        $ekiCtList = $params->getEkiCt();
        if (!is_array($ekiCtList)) {
            $ekiCtList = is_null($ekiCtList) ? array() : array($ekiCtList);
        }
        foreach ($ekiCtList as $ekiCt) {
            $searchAreaElem["input[value=${ekiCt}]"]->attr('checked', 'checked');
        }
        $doc['div.search-modal-station div.element-search-area']->replaceWith($searchAreaElem);

        // エリア選択モーダル
        $doc['div.search-modal-area']->remove();

        return $doc->html();
    }


    public function createAreaHidden(
        $doc,
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $pNames = $datas->getParamNames();
        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        // 種目情報の取得
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;
        $s_type = $params->getSearchType();

        /**
         * 市区町村選択
         */
        switch ($s_type) {
            case $params::SEARCH_TYPE_MAP:
                $cityMaker = new Element\CityMap();
                // 件数削除
                if ($doc['div.search-modal-area div.num-and-btn']) {
                    $doc['div.search-modal-area div.num-and-btn']->remove();
                    $doc['div.search-modal-area p.btn-change']->remove();
                }
                break;
            default:
                $cityMaker = new Element\City();
                break;
        }
        $shikugunWithLocateCd = $datas->getCityList();
        $searchAreaElem = $cityMaker->createElement($shikugunWithLocateCd, $specialRow->filename, $ken_ct, true, $specialSetting->area_search_filter->canChosonSearch());
        // リクエストパラメータで指定された市区郡は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        if ($params->getShikugunCt()) {
            // 市区郡のパラメータあり
            $shikugunCtList = $params->getShikugunCt();
            if (!is_array($shikugunCtList)) {
                $shikugunCtList = [$shikugunCtList];
            }
            foreach ($shikugunCtList as $shikugunCt) {
                $searchAreaElem["input[value=${shikugunCt}]"]->attr('checked', 'checked');
            }
        } else if ($params->getMcityCt()) {
            // 政令指定都市のパラメータあり
            $mcityCt = $params->getMcityCt();
            $searchAreaElem[".heading-area input[value={$mcityCt}]"]->attr('checked', 'checked');
        } else {
            // 市区郡、政令指定都市どちらもパラメータなし（=都道府県一覧）
            $searchAreaElem[".heading-area input"]->attr('checked', 'checked');
        }

        $doc['div.search-modal-area div.element-search-area']->replaceWith($searchAreaElem);

        // 沿線選択モーダル
        $doc['div.search-modal-railway']->remove();
        // 駅選択モーダル
        $doc['div.search-modal-station']->remove();

        return $doc->html();
    }

    public function createChosonHidden(
        $doc,
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $pNames = $datas->getParamNames();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();

        /**
         * 市区町村選択
         */

        $maker = new Element\Choson();
        $searchAreaElem = $maker->createElement($datas->getChosonList(), $specialRow->filename, $ken_ct, true, $pNames->getShikuguns());

        // リクエストパラメータで指定された町村は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        if ($chosonCtList = $params->getChosonCt()) {
            foreach ($chosonCtList as $chosonCt) {
                $searchAreaElem["input[value={$chosonCt}]"]->attr('checked', 'checked');
            }
        }

        $doc['div.search-modal-choson div.element-search-area']->replaceWith($searchAreaElem);

        // 沿線選択モーダル
        $doc['div.search-modal-railway']->remove();
        // 駅選択モーダル
        $doc['div.search-modal-station']->remove();

        return $doc->html();
    }
}
