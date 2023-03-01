<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;

class Result extends Services\AbstractElementService
{
	public $head;
	public $header;
	public $content;
    public $info;
    public $breadCrumb;
    public $token;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
		$this->content = $this->content($params, $settings, $datas);
        $this->info = $this->info($params, $settings, $datas);
        if (!isset($this->_session->token)) {
        //$this->_session = new Zend_Session_Namespace();
        $this->_session = app('request')->session();
        $this->_session->token = md5(uniqid(rand(),1));
        }
        $this->token = $this->_session->token;
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 検索タイプ
        $s_type = $params->getSearchType();
        $pNames = $datas->getParamNames();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_cd = $pNames->getEkiCd();
        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_cd = $pNames->getShikugunCd();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();

        //都道府県チェック
        $ken_cd  = $pNames->getKenCd();
        $prefs = $datas->getPrefSetting();
        $check = false;
        foreach ($prefs as $key => $value) {
            if($value == $ken_cd) {
                $check=true;
            }
        }
        if(!$check && !$params->isFreeword()) throw new \Exception('都道府県設定がありません。', 404);

        //cmsに登録されているデータとurlの整合性チェック
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            switch ($s_type)
            {
                case $params::SEARCH_TYPE_CITY:
                    if ($shikugun_cd) {
                        $shikugunWithLocateCd = $datas->getCityList();
                        foreach($shikugunWithLocateCd['shikuguns'][0]['locate_groups'] as $locates){
                        	foreach($locates['shikuguns'] as $city){
                        		if ($city['code'] === $shikugun_cd) {
                                	break 3;
                            	}
                        	}
                        }
                        throw new \Exception('対象の地区設定がありません。', 404);
                    }
                    break;
                case $params::SEARCH_TYPE_CHOSON:
                case $params::SEARCH_TYPE_CHOSON_POST:
                    if ($choson_cd = $pNames->getChosonCd()) {
                        $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt());
                        if ($settingRow) {
                            $areaSearchFilter = $settingRow->toSettingObject()->area_search_filter;
                            if ($areaSearchFilter->canChosonSearch()) {
                                foreach ($choson_cd as $_shikugun_cd => $_chosons) {
                                    if (!in_array($_shikugun_cd, $areaSearchFilter->area_2[$ken_cd])) {
                                        throw new \Exception('対象の地区設定がありません。', 404);
                                    }
                                    if (
                                        !isset($areaSearchFilter->area_5[$ken_cd][$_shikugun_cd]) ||
                                        !is_array($areaSearchFilter->area_5[$ken_cd][$_shikugun_cd]) ||
                                        !$areaSearchFilter->area_5[$ken_cd][$_shikugun_cd]
                                    ) {
                                        continue;
                                    }
                                    foreach ($_chosons as $_choson_cd) {
                                        if (!in_array($_choson_cd, $areaSearchFilter->area_5[$ken_cd][$_shikugun_cd])) {
                                            throw new \Exception('対象の地区設定がありません。', 404);
                                        }
                                    }
                                }
                                // OK
                                break;
                            }
                        }
                        throw new \Exception('対象の地区設定がありません。', 404);
                    }
                    break;
                case $params::SEARCH_TYPE_SEIREI:
                    if ($locate_cd) {
                        $shikugunWithLocateCd = $datas->getCityList();
                        foreach($shikugunWithLocateCd['shikuguns'][0]['locate_groups'] as $locates){
                            if ($locates['locate_cd'] === $locate_cd) {
                                    break 2;
                            }
                        }
                        throw new \Exception('対象の地区設定がありません。', 404);
                    }
                    break;
                case $params::SEARCH_TYPE_EKI:
                    if ($eki_cd) {
                        $ekiList = $datas->getEkiList();
                        foreach($ekiList['ensens'][0]['ekis'] as $eki) {
                            if ($eki['code'] === $eki_cd) {
                                break 2;
                            }
                        }
                        throw new \Exception('対象の駅設定がありません。', 404);
                    }
                    break;
            }
        }
	}

	private function head(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pageInitialSettings = $settings->page;
		$siteName = $pageInitialSettings->getSiteName();
		$keyword = $pageInitialSettings->getKeyword();
		$comName = $pageInitialSettings->getCompanyName();
		$description = $pageInitialSettings->getDescription();

        $pSearchFilter = $params->getSearchFilter();

        $pNames = $datas->getParamNames();
        // 種目名称の取得
        $shumoku_nm = $pNames->getShumokuName();
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

        // 検索タイプ
        $s_type = $params->getSearchType();
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $title_txt   = "${ensen_nm}から${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${ensen_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${ensen_nm}の${shumoku_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CITY:
                $title_txt   = "${ken_nm}${shikugun_nm}から${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${shikugun_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${shikugun_nm}の${shumoku_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $title_txt   = "${ken_nm}${locate_nm}から${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${locate_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${locate_nm}の${shumoku_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_EKI:
                $title_txt   = "${eki_nm}駅(${ken_nm})から${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${eki_nm}駅 ${shumoku_nm},${ken_nm} ${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${eki_nm}駅の${shumoku_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_PREF:
                $title_txt   = "${ken_nm}から${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${ken_nm} ${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${ken_nm}の${shumoku_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $title_txt   = "${shumoku_nm}を検索｜${siteName}";
                $keyword_txt = "${shumoku_nm},検索,${keyword}";
                $desc_txt   = "【${comName}】${ken_nm}の検索結果一覧。${description}";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $choson_nm = $pNames->getChosonName();
                // {$市区郡名}{$町名}({$都道府県名})から{$物件種目}を検索｜{$CMS初期設定サイト名}
                $title_txt   = "{$shikugun_nm}{$choson_nm}(${ken_nm})から${shumoku_nm}を検索｜${siteName}";
                // {$町名} {$物件種目},{$市区郡名} {$物件種目},{$都道府県名} {$物件種目},検索,{$CMS初期設定キーワード}
                $keyword_txt = "{$choson_nm} ${shumoku_nm},${shikugun_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},検索,${keyword}";
                // 【{$会社名}】{$市区郡名}{$町名}の{$物件種目}の検索結果一覧。{$CMS初期設定サイトの説明}
                $desc_txt   = "【${comName}】${shikugun_nm}{$choson_nm}の${shumoku_nm}の検索結果一覧。${description}";
                break;
        }
        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $title_txt   = implode(' ', $freewords)."を検索｜${siteName}";
            $keyword_txt = implode(' ', $freewords).",検索,${keyword}";
            $desc_txt = implode(' ', $freewords)."の検索結果一覧。${description}";
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
			Datas $datas)
	{

        $pNames = $datas->getParamNames();
        // 種目名称の取得
        $shumoku_nm = $pNames->getShumokuName();
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

        // ページ
        $page = $params->getPage(1);

        // 検索タイプ
        $s_type = $params->getSearchType();
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $h1_txt   = "${shumoku_nm}情報を${ensen_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CITY:
                $h1_txt   = "${shumoku_nm}情報を${shikugun_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $choson_nm = $pNames->getChosonName();
                // {$物件種目}情報を{$市区郡名}{$町名}({$都道府県名})から検索
                $h1_txt   = "${shumoku_nm}情報を${shikugun_nm}{$choson_nm}(${ken_nm})から検索";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $h1_txt   = "${shumoku_nm}情報を${locate_nm}から検索";
                break;
            case $params::SEARCH_TYPE_EKI:
                $h1_txt   = "${shumoku_nm}情報を${eki_nm}駅(${ken_nm})から検索";
                break;
            case $params::SEARCH_TYPE_PREF:
                $h1_txt   = "${shumoku_nm}情報を${ken_nm}から検索";
                break;
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $h1_txt   = "${shumoku_nm}情報を検索";
                break;
        }

        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $h1_txt   = implode(' ', $freewords)."の${h1_txt}";
        }

        if ($page > 1) {
            $h1_txt = $h1_txt . "　${page}ページ目";
        }

		return "<h1 class='tx-explain'>$h1_txt</h1>";
	}

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/result/content.tpl");
        $pSearchFilter = $params->getSearchFilter();

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
		$pNames = $datas->getParamNames();
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
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
            if ($shikugun_ct != null) {
                foreach ($pNames->getChosonShikuguns() as $shikugunObj) {
                    $shikugun_ct = $shikugunObj->shikugun_roman;
                    $shikugun_nm = $shikugunObj->shikugun_nm;
                    break;
                }
            }
        }

        // こだわり条件
        $searchFilter = $datas->getSearchFilter();

        //物件リクエストリンクの表示設定 #####
        $class = Estate\TypeList::getInstance()->getClassByType($type_id);
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['p.btn-request']->append($estateRequest['requestUrl']);
            } else {
                $doc['p.request']->remove();
            }
        } else {
            if(isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $class);
                if($hpPageRow) {
                    $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                    $doc['p.btn-request']->append($requestUrl);
                }else{
                    $doc['p.request']->remove();
                }
            }
        }
        $freewordText = '';
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
        if($settingRow->display_freeword){
            $asidetElem = $doc['div.articlelist-side.contents-left'];
            $searchFilterSection = $asidetElem->find('.articlelist-side-section:last');
            $searchElement = $asidetElem->find(".element-input-search-result");
            if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
                $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
                $freewordText = $pSearchFilter["fulltext_fields"];
                $stringslipt=preg_split('/(.{20})/us', $freewordText,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
                if(count($stringslipt)>1){
                    $freewordText = $stringslipt[0].'...';
                }
            } else {
                $searchElement->before($searchFilterSection);
            }
        }else{
            $doc[".element-input-search-result"]->remove();
        }
        // ########

        /*
         * パンくず作成
         * 検索タイプによって、作成するパンくずは異なる。
         */
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [
            // URL = NAME
            "/${top}"      => $this::BREAD_CRUMB_1ST_SHUMOKU
        ];
        if (count($datas->getPrefSetting()) !== 1) {
            $levels += [
                "/${type_ct}/" => $this::BREAD_CRUMB_2ND_PREF
            ];
        }
        // 1ページ目か
        $pageID = $params->getPage(1);
        $isFirstPage = $pageID == 1;
        switch ($s_type)
        {
// 1. ホーム＞種別選択：1＞(都道府県選択：4＞)沿線から探す：6＞{$沿線名：９}＞{$沿線名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_LINE:
                $levels += array("/${type_ct}/${ken_ct}/line.html" => '沿線から探す');
                $levels += array("/${type_ct}/${ken_ct}/${ensen_ct}-line/" => "${ensen_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "${ensen_nm}の${shumoku_nm}一覧");
                }
                else {
                    // /{$物件種目}/{$都道府県ID｜path}/result/{$沿線ID｜path}-line.html
                    $levels += array("/${type_ct}/${ken_ct}/result/${ensen_ct}-line.html" => "${ensen_nm}の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }

                break;
// 2. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$市区名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_CITY:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "${shikugun_nm}の${shumoku_nm}一覧");
                }
                else {
                    // /{$物件種目}/{$都道府県ID｜path}/result/{$市区ID｜path}-city.html
                    $levels += array("/${type_ct}/${ken_ct}/result/${shikugun_ct}-city.html" => "${shikugun_nm}の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }
                break;
            // ホーム＞種別選択：1＞(都道府県選択：4＞)地域から探す：6＞{$市区郡名：９}＞{$町名}の{$物件種目}一覧
            // ホーム＞種別選択：1＞(都道府県選択：4＞)地域から探す：6＞{$市区郡名：９}＞{$町名}の{$物件種目}一覧：13＞{$pageID}ページ目
            case $params::SEARCH_TYPE_CHOSON:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/${type_ct}/${ken_ct}/{$shikugun_ct}-city/" => "${shikugun_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "{$choson_nm}の${shumoku_nm}一覧");
                }
                else {
                    $levels += array("/${type_ct}/${ken_ct}/result/{$choson_ct[0]}.html" => "{$choson_nm}の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }
                break;
// 3. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$政令指定都市}の{$物件種目}一覧
            case $params::SEARCH_TYPE_SEIREI:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "${locate_nm}の${shumoku_nm}一覧");
                }
                else {
                    // /{$物件種目}/{$都道府県ID｜path}/result/{$政令指定都市ID｜path}-mcity.html
                    $levels += array("/${type_ct}/${ken_ct}/result/${locate_ct}-mcity.html" => "${locate_nm}の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }
                break;
// 4. ホーム＞種別選択：1＞(都道府県選択：4＞)沿線から探す：6＞{$沿線名：９}＞{$駅名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_EKI:
                $levels += array("/${type_ct}/${ken_ct}/line.html" => '沿線から探す');
                $levels += array("/${type_ct}/${ken_ct}/${ensen_ct}-line/" => "${ensen_nm}");
                if ($isFirstPage) {
                    $levels += array('' => "${eki_nm}駅の${shumoku_nm}一覧");
                }
                else {
                    // /{$物件種目}/{$都道府県ID｜path}/result/{$駅ID｜path}-eki.html
                    $levels += array("/${type_ct}/${ken_ct}/result/${eki_ct}-eki.html" => "${eki_nm}駅の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }
                break;
// 5. ホーム＞種別選択：1＞(都道府県選択：4＞)地図から探す：7＞{$市区名}の{$物件種目}地図一覧
//  next
// 6. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_PREF:
                if ($isFirstPage) {
                    $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                    $levels += array('' => "${ken_nm}の${shumoku_nm}一覧");
                }
                else {
                    // /{$物件種目}/{$都道府県ID｜path}/result/
                    $levels += array("/${type_ct}/${ken_ct}/result/" => "${ken_nm}の${shumoku_nm}一覧");
                    $levels += array('' => "{$pageID}ページ目");
                }
                break;
// 7. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$都道府県名}の{$物件種目}一覧
// 8. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$都道府県名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array('' => "${ken_nm}の${shumoku_nm}一覧");
                break;
            case $params::SEARCH_TYPE_FREEWORD:
                $levels = array('' => "${shumoku_nm}一覧");
                break;
        }
        // breadcrum freeword
        if ($freewordText != '') {
            $freewordText = htmlspecialchars($freewordText);
            $levels[''] = "${freewordText}一覧";
        }
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);


        /*
         * 見出し処理
         */
        // 検索タイプによって、テキストは異なる。
        $doc['section.articlelist-inner div:first']->remove(); // 特集用テキストの削除

        $h2Text = '';
        $lead_keyword = '';
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $h2Text = "${ensen_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = $ensen_nm;
                break;
            case $params::SEARCH_TYPE_CITY:
                $h2Text = "${shikugun_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = $shikugun_nm;
                break;
            case $params::SEARCH_TYPE_CHOSON:
                // {$市区郡名}{$町名}の{$物件種目}の物件一覧
                $h2Text = "${shikugun_nm}{$choson_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = "${shikugun_nm}{$choson_nm}";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $h2Text = "${locate_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = $locate_nm;
                break;
            case $params::SEARCH_TYPE_EKI:
                $h2Text = "${eki_nm}駅の${shumoku_nm}の物件一覧";
                $lead_keyword = $eki_nm.'駅';
                break;
            case $params::SEARCH_TYPE_PREF:
                $h2Text = "${ken_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = $ken_nm;
                break;
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $h2Text = "${ken_nm}の${shumoku_nm}の物件一覧";
                $lead_keyword = $ken_nm;
                break;
            case $params::SEARCH_TYPE_FREEWORD:
                $h2Text = "${shumoku_nm}の物件一覧";
                break;
        }
        if ($freewordText != '') {
            $h2Text = "${freewordText} の物件一覧";
            $lead_keyword = $freewordText;
        }
        $lead_sentence = "${lead_keyword}の検索結果（${shumoku_nm}）ページです。ご希望の条件で更に絞り込むことも可能です。".
                    "また、ご希望に合った物件が見つからない場合は、絞り込み条件を変更して検索してみてはいかがでしょうか。" .
                    "${lead_keyword}で${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";

        $doc['div.contents-right h2:first']->text($h2Text);
        if ($isFirstPage) {
            $doc['div.contents-right div:last p']->text($lead_sentence);
        }
        else {
            $doc['div.contents-right div:last p']->remove();
        }

        /*
         * 物件一覧
         */
        $resultMaker = new Element\Result();
        $bukkenList = $datas->getBukkenList();
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, false, $settings->page, $settings->search);

        $total_count = $bukkenList['total_count'];
        //物件リクエスト
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            if ($total_count == 0 && $get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['span.btn-request']->append($estateRequest['requestUrl']);
            }
        } else {
            if($total_count == 0 && $hpPageRow) {
                $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                $doc['span.btn-request']->append($requestUrl);
            }
        }

        // こだわり条件
        $searchFilterElement = new Element\SearchFilter( $searchFilter );
        $facet = new Services\BApi\SearchFilterFacetTranslator();
        $facet->setFacets($bukkenList['facets']);
        $searchFilterElement->renderAside($type_id, $total_count, $facet, $doc);

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->result(
        		$doc, $params, $settings, $datas);

        return $doc->html();
    }

    private function info(
			Params $params,
			Settings $settings,
			Datas $datas)
    {
        $bukkenList = $datas->getBukkenList();
    	return [
    			'current_page' => $bukkenList['current_page'],
    			'total_page' => $bukkenList['total_pages'],
    			'total_count' => $bukkenList['total_count']
    	];
    }
}