<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\SearchCondSettings;
use Modules\V1api\Models\SpecialSettings;
use Modules\V1api\Models\BApi;
use Library\Custom\Model\Estate;

// @TODO このクラスは使ってる？？
class SplResultHidden extends Services\AbstractElementService
{

    public function createElement(
        Params $params,
        PageInitialSettings $pageInitialSettings,
        SearchCondSettings $searchCond,
        SpecialSettings $specialSettings)
    {
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            return;
        }
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/result/hidden.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        // 特集を取得
        $specialRow = $specialSettings->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        $pNames = $params->getParamNames();
        // 種目情報の取得
        $type_id = $specialSetting->enabled_estate_type;
        $type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
        $shumoku    = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
        $shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;

        /*
         * 都道府県選択の作成
         */
        $prefs = $specialSetting->area_search_filter->area_1;
        $prefMaker = new Element\Pref();
        $doc["table.element-search-table"]->replaceWith($prefMaker->createElement($prefs, $type_ct, $ken_nm));

        // 検索タイプ
        $s_type = $params->getSearchType();
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
            case $params::SEARCH_TYPE_EKI:
            case $params::SEARCH_TYPE_LINEEKI_POST:
                $this->createLineHidden($doc, $params, $pageInitialSettings,$searchCond, $specialSettings);
                break;
            case $params::SEARCH_TYPE_CITY:
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_SEIREI:
            case $params::SEARCH_TYPE_CITY_POST:
                $this->createAreaHidden($doc, $params, $pageInitialSettings,$searchCond, $specialSettings);
                break;
        }        
        return $doc->html();
    }
    
    public function createLineHidden($doc, $params, $pageInitialSettings,$searchCond, $specialSettings)
    {
        // 特集を取得
        $specialRow = $specialSettings->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        $pNames = $params->getParamNames();
        // 種目情報の取得
        $type_id = $specialSetting->enabled_estate_type;
        $type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
        $shumoku    = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
        $shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;
        // 検索タイプ
        $s_type = $params->getSearchType();

        /*
         * 沿線選択の作成
         */
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
        // 特集設定の沿線コードを取得
        $ensen_cd = $specialSetting->area_search_filter->area_3[$ken_cd];

        // BApi用パラメータ作成
        $apiParam = new BApi\EnsenParams();
        $comId = $params->getComId();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setGrouping($apiParam::GROUPING_TYPE_TRUE);
        $apiParam->setEnsenCd($ensen_cd);
        // 全会員リンク番号をキーに物件API：沿線一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Ensen();
        $ensenWithGroup = $apiObj->getEnsenWithGroup($apiParam, 'モーダル：沿線選択');

        $ensenMaker = new Services\Sp_Element_Ensen();
        $searchAreaElem = $ensenMaker->createElement($ensenWithGroup, $type_ct, $ken_ct, false);

        // リクエストパラメータで指定された値は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        $ensenCtList = $params->getEnsenCt();
        if (! is_array($ensenCtList)) {
            $ensenCtList = array($ensenCtList);
        }
        foreach ($ensenCtList as $ensenCt)
        {
            $searchAreaElem["input[value=${ensenCt}]"]->attr('checked', 'checked');
        }
        $doc['div.search-modal-railway div.element-search-area']->replaceWith($searchAreaElem);


        /*
         * 駅選択の作成
         */
        // Paramの契約IDをキーに、関連会員番号を全て取得
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
        $ensen_cd_api = array();
        $ensen_eki_cd = null;

        // BApi用パラメータ作成
        $apiParam = new BApi\EkiParams();
        $comId = $params->getComId();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);

        // 駅モーダルを作成するタイミングは、
        // 駅選択ボタンを押される時と、沿線モーダルからの遷移のみ。
        // ・駅選択ボタンの場合
        //   　検索タイプ：駅は、駅情報から沿線CDを特定、DB駅設定を取得し駅一覧作成
        //   　検索タイプ：沿線　沿線CDからDB駅設定を取得し駅一覧作成
        // ・沿線モーダルの場合
        //     必ず沿線Paramが渡ってくるので、
        //     沿線CDからDB駅設定を取得し駅一覧作成
        if (count($ensenCtList) > 0) {
            // 沿線ローマ字より沿線コードを取得する
            $ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
            foreach ($ensenObjList as $ensenObj) {
                array_push($ensen_cd_api, $ensenObj['code']);
            }
        }
        else
        {
            $eki_ct = $params->getEkiCt();
            // @TODO 駅ローマ字より沿線コードを取得する
            // @TODO ダミー
            $ensen_cd_api = '2172';
            if ($eki_ct == 'yoyogi') {
            } else if ($eki_ct == 'shinjuku') {
            } else if ($eki_ct == 'harajuku') {
            } else if ($eki_ct == 'yotsuya') {
                $ensen_cd_api = '2199';
            } else if ($eki_ct == 'shinanomachi') {
                $ensen_cd_api = '2199';
            } else if ($eki_ct == 'sendagaya') {
                $ensen_cd_api = '2199';
            }
        }
        $apiParam->setEnsenCd($ensen_cd_api);
        // 沿線コードをキーに、DB駅設定を取得
        // 沿線パラメータをキーに、特集設定の駅コードを取得し設定する。
        $ensen_eki_cd_list = array();
        foreach ($specialSetting->area_search_filter->area_4[$ken_cd] as $ensen_eki)
        {
            $ensen = substr($ensen_eki, 0, 3);
            if (in_array($ensen, $ensen_cd_api)) {
                array_push($ensen_eki_cd_list, $ensen_eki);
            }                  
        }
        $apiParam->setEnsenEkiCd($ensen_eki_cd_list);
        // 検索エンジンレンタルのみ公開の物件だけを表示する
        $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
        // 「2次広告自動公開物件」
        $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「エンド向け仲介手数料不要の物件」
        $apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
        $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
        $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Eki();
        $ekiWithEnsen = $apiObj->getEkiWithEnsen($apiParam, 'モーダル：駅選択');

        $ekiMaker = new Services\Sp_Element_Eki();
        $searchAreaElem = $ekiMaker->createElement($ekiWithEnsen, $type_ct, $ken_ct, false);

        // リクエストパラメータで指定された値は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        $ensenCtList = $params->getEnsenCt();
        if (! is_array($ensenCtList)) {
            $ensenCtList = array($ensenCtList);
        }
        foreach ($ensenCtList as $ensenCt)
        {
            $searchAreaElem["input[value=${ensenCt}]"]->attr('checked', 'checked');
        }
        $doc['div.search-modal-station div.element-search-area']->replaceWith($searchAreaElem);

        // エリア選択モーダル
        $doc['div.search-modal-area']->remove();
        
        return $doc->html();
    }
    
    public function createAreaHidden($doc, $params, $pageInitialSettings,$searchCond, $specialSettings)
    {
        // 特集を取得
        $specialRow = $specialSettings->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        $pNames = $params->getParamNames();
        // 種目情報の取得
        $type_id = $specialSetting->enabled_estate_type;
        $type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
        $shumoku    = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
        $shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;

        /**
         * 市区町村選択
         */
        // Paramの契約IDをキーに、関連会員番号を全て取得
        $kaiinLinkNo = $pageInitialSettings->getAllRelativeKaiinLinkNo();
        // 特集の市区郡設定を取得する。
        $shozaichi_cd = $specialSetting->area_search_filter->area_2[$ken_cd];

        // BApi用パラメータ作成
        $apiParam = new BApi\ShikugunParams();
        $comId = $params->getComId();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setKenCd($ken_cd);
        $apiParam->setGrouping($apiParam::GROUPING_TYPE_LOCATE_CD);
        $apiParam->setShozaichiCd($shozaichi_cd);
        // 検索エンジンレンタルのみ公開の物件だけを表示する
        $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
        // 「2次広告自動公開物件」
        $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「エンド向け仲介手数料不要の物件」
        $apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
        $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
        $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        // 全会員リンク番号をキーに物件API：市区町村一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Shikugun();
        $shikugunWithLocateCd = $apiObj->getShikugunWithLocateCd($apiParam, 'モーダル：市区郡選択');

        $cityMaker = new Element\City();
        $searchAreaElem = $cityMaker->createElement($shikugunWithLocateCd, $type_ct, $ken_ct, true);
        // リクエストパラメータで指定された市区郡は、
        // チェック状態にする。
        // インプット要素に対しチェックを入れる。
        $shikugunCtList = $params->getShikugunCt();
        if (! is_array($shikugunCtList)) {
            $shikugunCtList = array($shikugunCtList);
        }
        foreach ($shikugunCtList as $shikugunCt)
        {
            $searchAreaElem["input[value=${shikugunCt}]"]->attr('checked', 'checked');
        }

        $doc['div.search-modal-area div.element-search-area']->replaceWith($searchAreaElem);

        // 沿線選択モーダル
        $doc['div.search-modal-railway']->remove();
        // 駅選択モーダル
        $doc['div.search-modal-station']->remove();
        
        return $doc->html();
    }
}