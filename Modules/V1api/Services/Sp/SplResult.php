<?php

namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\EnsenEki;
use Library\Custom\Model\Estate;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;

class SplResult extends Services\AbstractElementService
{
    public $head;
    public $header;
    public $content;
    public $info;
    public $breadCrumb;
    // 	public $directResult;

    public function create(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $this->head = $this->head($params, $settings, $datas);
        $this->header = $this->header($params, $settings, $datas);
        $this->content = $this->content($params, $settings, $datas);
        $this->info = $this->info($params, $settings, $datas);
        // 		$this->directResult = false;
    }

    public function check(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
    }

    private function head(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $pageInitialSettings = $settings->page;
        $siteName = $pageInitialSettings->getSiteName();
        $keyword = $pageInitialSettings->getKeyword();
        $comName = $pageInitialSettings->getCompanyName();
        $description = $pageInitialSettings->getDescription();
        // 種目名称の取得
        $shumoku_nm = $datas->getParamNames()->getShumokuName();
        // 特集の取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();

        // 検索タイプ
        $s_type = $params->getSearchType();

        $pNames = $datas->getParamNames();
        // 都道府県名の取得
        $ken_nm = $pNames->getKenName();
        // 沿線名の取得
        $ensen_nm = $pNames->getEnsenName();
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();

        $pSearchFilter = $params->getSearchFilter();

        if ($s_type == $params::SEARCH_TYPE_CHOSON) {
            // 単一町村で市区郡が複数の場合補完
            if (!$shikugun_nm) {
                foreach ($pNames->getChosonShikuguns() as $shikugunObj) {
                    $shikugun_nm = $shikugunObj->shikugun_nm;
                    break;
                }
            }
        }
        switch ($s_type) {
            case $params::SEARCH_TYPE_LINE:
                // {$特集名}｜{$沿線名}から検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜${ensen_nm}からを検索｜${siteName}";
                // {$沿線名} {$特集名},{$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "${ensen_nm} {$specialRow->title},${ken_nm} {$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$沿線名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${ensen_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CITY:
                // {$特集名}｜{$都道府県名}{$市区名}から検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜${ken_nm}${shikugun_nm}から検索｜${siteName}";
                // {$市区名} {$特集名},{$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "${shikugun_nm} {$specialRow->title},${ken_nm} {$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$市区名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${shikugun_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                // {$特集名}｜{$都道府県名}{$政令指定都市名}から検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜${ken_nm}${locate_nm}から検索｜${siteName}";
                // {$政令指定都市名} {$特集名},{$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "${locate_nm} {$specialRow->title},${ken_nm} {$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$政令指定都市名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${locate_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_EKI:
                // {$特集名}｜{$駅名}({$都道府県名})から検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜${eki_nm}駅(${ken_nm})から検索｜${siteName}";
                // {$駅名} {$特集名},{$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "${eki_nm}駅 {$specialRow->title},${ken_nm} {$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$駅名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${eki_nm}駅の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_PREF:
                // {$特集名}｜{$都道府県名}から検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜${ken_nm}から検索｜${siteName}";
                // {$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "${ken_nm} {$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$都道府県名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${ken_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                // {$特集名}｜物件を検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜物件を検索｜${siteName}";
                // {$特集名},検索,{$CMS初期設定キーワード}
                $keyword_txt = "{$specialRow->title},検索,${keyword}";
                // {$特集名}：【{$会社名}】{$都道府県名}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${ken_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $choson_nm = $pNames->getChosonName();
                // {$市区郡名}{$町名}({$都道府県名})から{$物件種目}を検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$specialRow->title}｜{$shikugun_nm}{$choson_nm}(${ken_nm})から検索｜${siteName}";
                // {$町名} {$物件種目},{$市区郡名} {$物件種目},{$都道府県名} {$物件種目},検索,{$CMS初期設定キーワード}
                $keyword_txt = "{$choson_nm} {$specialRow->title},${shikugun_nm} {$specialRow->title},${ken_nm} {$specialRow->title},検索,${keyword}";
                // 【{$会社名}】{$市区郡名}{$町名}の{$物件種目}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "{$specialRow->title}：【${comName}】${shikugun_nm}{$choson_nm}の検索結果一覧。${description}";
                break;
        }

        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $title_txt   = implode(' ', $freewords) . "を検索｜${siteName}";
            $keyword_txt = implode(' ', $freewords) . ",検索,${keyword}";
            $desc_txt = implode(' ', $freewords) . "の検索結果一覧。${description}";
        }

        // ページID
        if ($params->getPage(1) != 1) {
            $page = $params->getPage();
            $page_txt = "【{$page}ページ目】";
            $title_txt .= $page_txt;
            $desc_txt  .= $page_txt;
        }

        $head = new Services\Head();
        $head->title = $title_txt;
        $head->keywords = $keyword_txt;
        $head->description = $desc_txt;
        return $head->html();
    }

    private function header(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // 特集の取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();

        $pNames = $datas->getParamNames();
        // 都道府県名の取得
        $ken_nm = $pNames->getKenName();
        // 沿線名の取得
        $ensen_nm = $pNames->getEnsenName();
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();

        $pSearchFilter = $params->getSearchFilter();

        // 検索タイプ
        $h1_txt = '';
        $s_type = $params->getSearchType();

        if ($s_type == $params::SEARCH_TYPE_CHOSON) {
            // 単一町村で市区郡が複数の場合補完
            if (!$shikugun_nm) {
                foreach ($pNames->getChosonShikuguns() as $shikugunObj) {
                    $shikugun_nm = $shikugunObj->shikugun_nm;
                    break;
                }
            }
        }

        switch ($s_type) {
            case $params::SEARCH_TYPE_LINE:
                // {$特集名}の物件情報を{$沿線名}から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${ensen_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CITY:
                // {$特集名}の物件情報を{$市区名}から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${shikugun_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $choson_nm = $pNames->getChosonName();
                // {$物件種目}情報を{$市区郡名}{$町名}({$都道府県名})から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${shikugun_nm}{$choson_nm}(${ken_nm})から検索";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                // {$特集名}の物件情報を{$政令指定都市名}から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${locate_nm}から検索";
                break;
            case $params::SEARCH_TYPE_EKI:
                // {$特集名}の物件情報を{$駅名}({$都道府県名})から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${eki_nm}駅(${ken_nm})から検索";
                break;
            case $params::SEARCH_TYPE_PREF:
                // {$特集名}の物件情報を{$都道府県名}から検索
                $h1_txt   = "{$specialRow->title}の物件情報を${ken_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                // {$特集名}の物件情報を検索
                $h1_txt   = "{$specialRow->title}の物件情報を検索";
                break;
        }

        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $h1_txt   = implode(' ', $freewords) . "の{$specialRow->title}情報を検索";
        }

        // ページID
        if ($params->getPage(1) != 1) {
            $page = $params->getPage();
            $h1_txt .= "　{$page}ページ目";
        }

        return "<h1 class='tx-explain'>$h1_txt</h1>";
    }

    private function content(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/" . Services\ServiceUtils::checkDateMaitain() . ".sp.tpl");
            return $doc->html();
        }
        $doc = $this->getTemplateDoc("/result/content.sp.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;
        $pSearchFilter = $params->getSearchFilter();

        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();
        // 検索タイプ
        $s_type = $params->getSearchType();

        $pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        $ensen_nm = $pNames->getEnsenName();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        $eki_cd = $pNames->getEkiCd();
        $eki_nm = $pNames->getEkiName();
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
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();
        $locate_nm = $pNames->getLocateName();

        $choson_nm = $pNames->getChosonName();
        $choson_ct = $params->getChosonCt();
        if ($s_type == $params::SEARCH_TYPE_CHOSON) {
            // 単一町村で市区郡が複数の場合補完
            if (count($shikugun_ct) > 1) {
                foreach ($pNames->getChosonShikuguns() as $shikugunObj) {
                    $shikugun_ct = $shikugunObj->shikugun_roman;
                    $shikugun_nm = $shikugunObj->shikugun_nm;
                    break;
                }
            }
        }

        //物件リクエストリンクの表示設定 #####
        $class = Estate\TypeList::getInstance()->getClassByType($specialSetting->enabled_estate_type[0]);
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['div.btn-request-txt']->append($estateRequest['requestUrl']);
            } else {
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if (isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $class);
                if ($hpPageRow) {
                    $requestUrl = "<a href='/" . $hpPageRow->public_path . "' target='_blank'>物件リクエストはこちら</a>";
                    $doc['div.btn-request-txt']->append($requestUrl);
                } else {
                    $doc['div.btn-request-txt']->remove();
                }
            }
        }

        $freewordText = '';
        if ($specialRow->display_freeword && isset($pSearchFilter["fulltext_fields"]) && !empty(trim($pSearchFilter["fulltext_fields"]))) {
            $freewordText = $pSearchFilter["fulltext_fields"];
            $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars($freewordText));
            $stringslipt = preg_split('/(.{20})/us', $freewordText, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (count($stringslipt) > 1) {
                $freewordText = $stringslipt[0] . '...';
            }
        } else {
            $doc[".element-input-search-result"]->remove();
        }
        // ########

        /*
         * パンくず、H2、リード分作成
         * 検索タイプによって、作成するパンくずは異なる。
         */
        $levels = [];
        $h2Text = '';
        $lead_keyword = '';

        // 都道府県選択
        if (count($specialSetting->area_search_filter->area_1) > 1) {
            $levels["/{$specialRow->filename}"] = "都道府県選択";
        }

        // 1ページ目か
        $pageID = $params->getPage(1);
        $isFirstPage = $pageID == 1;
        switch ($s_type) {
                // 1. ホーム＞{$特集名}：(都道府県選択：35＞)沿線から探す：38＞{$沿線名：41}＞{$沿線名}の物件一覧
            case $params::SEARCH_TYPE_LINE:
                $levels["/{$specialRow->filename}/{$ken_ct}/line.html"] = "沿線から探す";
                $levels["/{$specialRow->filename}/{$ken_ct}/{$ensen_ct}-line/"] = $ensen_nm;
                if ($isFirstPage) {
                    $levels[""] = "{$ensen_nm}の物件一覧";
                } else {
                    // /sp-{$特集Path}/{$都道府県ID｜path}/result/{$沿線ID｜path}-line.html
                    $levels["/{$specialRow->filename}/{$ken_ct}/result/{$ensen_ct}-line.html"] = "{$ensen_nm}の物件一覧";
                    $levels[""] = "{$pageID}ページ目";
                }
                $h2Text = "{$ensen_nm}の{$specialRow->title}の物件一覧";

                $lead_keyword = $ensen_nm;
                break;
                // 2. ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}：36＞{$市区名}の物件一覧
            case $params::SEARCH_TYPE_CITY:
                $levels["/{$specialRow->filename}/{$ken_ct}/"] = $ken_nm;
                if ($isFirstPage) {
                    $levels[""] = "{$shikugun_nm}の物件一覧";
                } else {
                    // /sp-{$特集Path}/{$都道府県ID｜path}/result/{$市区ID｜path}-city.html
                    $levels["/{$specialRow->filename}/{$ken_ct}/result/{$shikugun_ct}-city.html"] = "{$shikugun_nm}の物件一覧";
                    $levels[""] = "{$pageID}ページ目";
                }

                $h2Text = "{$shikugun_nm}の{$specialRow->title}の物件一覧";

                $lead_keyword = $shikugun_nm;
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $levels += array("/{$specialRow->filename}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/{$specialRow->filename}/${ken_ct}/{$shikugun_ct}-city/" => "${shikugun_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "{$choson_nm}の物件一覧");
                } else {
                    $levels += array("/{$specialRow->filename}/${ken_ct}/result/{$choson_ct[0]}.html" => "{$choson_nm}の物件一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }

                $h2Text = "${shikugun_nm}{$choson_nm}の{$specialRow->title}の物件一覧";
                $lead_keyword = "${shikugun_nm}{$choson_nm}";

                break;
                // 3. ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}：36＞{$政令指定都市}の物件一覧
            case $params::SEARCH_TYPE_SEIREI:
                $levels["/{$specialRow->filename}/{$ken_ct}/"] = $ken_nm;
                if ($isFirstPage) {
                    $levels[""] = "{$locate_nm}の物件一覧";
                } else {
                    // /sp-{$特集Path}/{$都道府県ID｜path}/result/{$政令指定都市ID｜path}-mcity.html
                    $levels["/{$specialRow->filename}/{$ken_ct}/result/{$locate_ct}-city.html"] = "{$locate_nm}の物件一覧";
                    $levels[""] = "{$pageID}ページ目";
                }

                $h2Text = "{$locate_nm}の{$specialRow->title}の物件一覧";

                $lead_keyword = $locate_nm;
                break;
                // 4. ホーム＞{$特集名}：(都道府県選択：35＞)沿線から探す：38＞{$沿線名：41}＞{$駅名}の物件一覧
            case $params::SEARCH_TYPE_EKI:
                $levels["/{$specialRow->filename}/{$ken_ct}/line.html"] = "沿線から探す";
                $levels["/{$specialRow->filename}/{$ken_ct}/{$ensen_ct}-line/"] = $ensen_nm;
                if ($isFirstPage) {
                    $levels[""] = "{$eki_nm}駅の物件一覧";
                } else {
                    // /sp-{$特集Path}/{$都道府県ID｜path}/result/{$駅ID｜path}-eki.html
                    $levels["/{$specialRow->filename}/{$ken_ct}/result/{$eki_ct}-eki.html"] = "{$eki_nm}駅の物件一覧";
                    $levels[""] = "{$pageID}ページ目";
                }

                $h2Text = "{$eki_nm}駅の{$specialRow->title}の物件一覧";

                $lead_keyword = $eki_nm;
                break;
                // 5. 地図一覧

                // 6. ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}の物件一覧
            case $params::SEARCH_TYPE_PREF:
                if ($isFirstPage) {
                    $levels[""] = "{$ken_nm}の物件一覧";
                } else {
                    // /sp-{$特集Path}/{$都道府県ID｜path}/result/
                    $levels["/{$specialRow->filename}/{$ken_ct}/result/"] = "{$ken_nm}の物件一覧";
                    $levels[""] = "{$pageID}ページ目";
                }

                $h2Text = "{$ken_nm}の{$specialRow->title}の物件一覧";

                $lead_keyword = $ken_nm;
                break;
                // 7. ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}：36＞{$都道府県名}の物件一覧
                // 8. ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}：36＞{$都道府県名}の物件一覧
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                // フォームはページ毎のパンクズ処理なし
                $levels["/{$specialRow->filename}/{$ken_ct}/"] = $ken_nm;
                $levels[""] = "{$ken_nm}の物件一覧";

                $h2Text = "{$ken_nm}の{$specialRow->title}の物件一覧";

                $lead_keyword = $ken_nm;
                break;
        }
        // breadcrum freeword
        if ($freewordText != '') {
            $freewordText = htmlspecialchars($freewordText);
            $levels[""] = "${freewordText}一覧";
            $h2Text = "${freewordText} の物件一覧";
            $lead_keyword = $freewordText;
        }

        $this->breadCrumb = $this->createSpecialBreadCrumbSp($doc['div.breadcrumb'], $levels, $specialRow->title);

        $doc['h2.article-heading span']->text($h2Text);

        $resultMaker = new Element\Result();
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, true, $settings->page, $settings->search);

        //物件リクエスト
        $bukkenList = $datas->getBukkenList();
        $total_count = $bukkenList['total_count'];
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            if ($total_count == 0 && $get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['span.btn-request']->append($estateRequest['requestUrl']);
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if ($total_count == 0 && $hpPageRow) {
                $requestUrl = "<a href='/" . $hpPageRow->public_path . "' target='_blank'>リクエストはこちらから</a>";
                $doc['span.btn-request']->append($requestUrl);
                $doc['div.btn-request-txt']->remove();
            }
        }

        return $doc->html();
    }

    private function info(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $bukkenList = $datas->getBukkenList();
        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();
        $type_id = $specialSetting->enabled_estate_type[0];
        $type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
        return [
            'type' => $type_ct,
            'current_page' => $bukkenList['current_page'],
            'total_page' => $bukkenList['total_pages'],
            'total_count' => $bukkenList['total_count']
        ];
    }
}
