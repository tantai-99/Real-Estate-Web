<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class SplEki extends Services\AbstractElementService
{
	public $head;
	public $header;
    public $content;
    public $breadCrumb;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);

		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 都道府県名の取得
        $pNames = $datas->getParamNames();
        $ken_nm = $pNames->getKenName();
        $ensen_nm = $pNames->getEnsenName();
        if ($ensen_nm) {
            $this->header = "<h1 class='tx-explain'>{$specialRow->title}の物件情報を${ensen_nm}(${ken_nm})から探す</h1>";
        } else {
            $this->header = "<h1 class='tx-explain'>{$specialRow->title}の物件情報を複数の沿線から探す</h1>";
        }

		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
		// 検索画面なし設定の場合
		if (!$specialSetting->area_search_filter->has_search_page) {
			throw new \Exception('指定された特集は検索画面なし設定です。', 404);
		}
		// 沿線から探す設定がない場合
		if (!$specialSetting->area_search_filter->hasLineSearchType()) {
			throw new \Exception('指定された特集は沿線・駅から探す設定ではありません。', 404);
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
		// 種目名称の取得
		$shumoku_nm = $datas->getParamNames()->getShumokuName();
		// 特集の取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();

		$pNames = $datas->getParamNames();
		// 都道府県名の取得
		$ken_nm = $pNames->getKenName();
		// 沿線名の取得
		$ensen_nm = $pNames->getEnsenName();

		$head = new Services\Head();
        if ($ensen_nm) {
            $head->title = "{$specialRow->title}｜${ensen_nm}(${ken_nm})から探す｜${siteName}";
            $head->keywords = "${ensen_nm} {$specialRow->title},${ken_nm} {$specialRow->title},駅選択,${keyword}";
            $head->description = "{$specialRow->title}：【${comName}】${ensen_nm}(${ken_nm})から物件を探す。${description}";
        } else {
            $head->title = "{$specialRow->title}｜複数の沿線から探す｜${siteName}";
            $head->keywords = "駅選択,${keyword}";
            $head->description = "{$specialRow->title}：【${comName}】複数の沿線から物件を探す。${description}";
        }
		return $head->html();
	}

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".sp.tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/eki/content.sp.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();

		$pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県名の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        $ensen_nm = $pNames->getEnsenName();

        /*
         * パンくず作成
         */
        // ホーム＞{$特集名}：(都道府県選択：35＞){$沿線名}から探す
        $levels = [];
        // 都道府県がひとつの場合は都道府県選択を表示しない
        if (count($specialSetting->area_search_filter->area_1) > 1) {
        	$levels[ "/{$specialRow->filename}" ] = "都道府県選択";
        }
        if ($ensen_nm) {
            $levels[''] = "{$ensen_nm}から探す";
        } else {
            $levels[''] = "複数の沿線から探す";
        }
        $this->breadCrumb = $this->createSpecialBreadCrumbSp($doc['div.breadcrumb'], $levels, $specialRow->title);

        /*
         * 見出し処理
         */
        $doc['h2']->text("{$specialRow->title}");
        // if ($specialRow->comment) {
        // 	$doc['.heading-lv1-1column']->next()->find('p')->html(nl2br(htmlspecialchars($specialRow->comment)));
        // }
        // else {
        // 	$doc['.heading-lv1-1column']->next()->find('p')->remove();
        // }

        $doc['h3:first']->text('駅を選択してください');

        // {$特集名}｜お探しの駅をお選びください。あなたのご希望に合った物件がきっと見つかります。
        // {$沿線名}({$都道府県名})で不動産情報をお探しなら、{$会社名}におまかせ！
        // $lead_sentence = "{$specialRow->title}｜お探しの駅をお選びください。".
        //     "あなたのご希望に合った物件がきっと見つかります。" .
        //     "${ensen_nm}(${ken_nm})で不動産情報をお探しなら、${comName}におまかせ！";
        // $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);
		/*
         * タブの作成
         */
        $canAreaSearch = $specialSetting->area_search_filter->hasAreaSearchType();
        $canLineSearch = $specialSetting->area_search_filter->hasLineSearchType();
        $canSpatialSearch = $specialSetting->area_search_filter->hasSpatialSearchType();

        $disable_tab = $params->getDisableSTypeTab();
        $doc["h3.heading-lv2-1column"]->after('<div class="element element-search-tab"><ul></ul></div>');
        $tabCount = 0;

        // 都道府県がひとつの場合
        $isOnePref = count($specialSetting->area_search_filter->area_1) === 1;

        if ($canAreaSearch && !$disable_tab && $isOnePref)
        {
            $tabCount++;
            $liElem = "<li class='shikugun'><a href='/{$specialRow->filename}/${ken_ct}/'>地域から探す</a></li>";
            $doc["div.element-search-tab ul"]->append($liElem);
        }

        if ($canLineSearch && !$disable_tab && $isOnePref) {
            $tabCount++;
            $liElem = "<li class='active ensen'><a href='#'>沿線・駅から探す</a></li>";
            $doc["div.element-search-tab ul"]->append($liElem);
        }
        if ($canSpatialSearch && !$disable_tab && $isOnePref) {
            $tabCount++;
            $liElem = '<li class="spatial"><a href="'."/{$specialRow->filename}/{$ken_ct}/map.html".'">地図から探す</a></li>';
            $doc["div.element-search-tab ul"]->append($liElem);
        }
        if ($canAreaSearch && $canLineSearch && $canSpatialSearch) {
            $doc["div.element-search-tab"]->addClass('three');
        }
        if ($tabCount < 2) {
            $doc["div.element-search-tab"]->remove();
        }
        if($specialRow->display_freeword){
            if (!empty($pSearchFilter["fulltext_fields"])) {
                if (!empty(trim($pSearchFilter["fulltext_fields"]))) {
                    $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
                }
            }
        }else{
            $doc[".element-input-search"]->remove();
        }
        /**
         * 駅選択
         */
        $ekiMaker = new Element\Eki();
        $ekiWithEnsen = $datas->getEkiList();
        $ekiSettingOfKen = $datas->getEkiSettingOfKen();
        $searchAreaElem = $ekiMaker->createElement($ekiWithEnsen, $specialRow->filename, $ken_ct, false, $ekiSettingOfKen);
        $doc['section.element-narrow-down section dl']->empty();
        $doc['section.element-narrow-down section dl']->append($searchAreaElem->children());

        // こだわり条件
        // $frontSearchFilter = $specialSetting->search_filter->toFrontSearchFilter($type_id);
        // $frontSearchFilter->setValues($type_id, []);
        // $frontSearchFilter->loadEnables($type_id);
        // $searchFilterElement = new Element\SearchFilter( $frontSearchFilter );
        // $doc['.qp-desired-container']->append( $searchFilterElement->createDesiredTableElement() );
        // $doc['.qp-particular-container']->append( $searchFilterElement->createParticularTableElement() );

        return $doc->html();
    }
}