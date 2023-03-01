<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class SplPref extends Services\AbstractElementService
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
		$this->header = "<h1 class='tx-explain'>{$specialRow->title}の物件情報を都道府県から探す</h1>";

		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$prefs = $datas->getPrefSetting();
		if (is_null($prefs))
		{
			throw new \Exception('都道府県が設定されていない', 404);
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
		// 特集の取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();

		$head = new Services\Head();
		$head->title = "{$specialRow->title}｜都道府県から探す｜${siteName}";
		$head->keywords = "都道府県選択,{$specialRow->title},${keyword}";
		$head->description = "{$specialRow->title}：【${comName}】都道府県選択画面。${description}";
		return $head->html();
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
		$doc = $this->getTemplateDoc("/pref/content.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();

        // 特集取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();

        /*
         * パンくず作成
         */
        // ホーム＞都道府県から選択する
        $levels = [
            // URL = NAME
            '' => "都道府県から選択する"
        ];
        $this->breadCrumb = $this->createSpecialBreadCrum($doc['div.breadcrumb'], $levels, $specialRow->title);

        /*
         * 見出し処理
         */
        $doc['h2']->text($specialRow->title);
        // 特集名
        if ($specialRow->comment) {
        	$doc['.heading-lv1-1column']->next()->find('p')->html(nl2br(htmlspecialchars($specialRow->comment)));
        }
        else {
        	$doc['.heading-lv1-1column']->next()->find('p')->remove();
        }

        $doc['h3:first']->text('お探しの都道府県を選んでください');

        $lead_sentence = "{$specialRow->title}｜お探しの都道府県をお選びください。あなたのご希望に合った物件がきっと見つかります。不動産情報をお探しなら、${comName}におまかせ！";
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);


        /*
         * タブの作成
         */
        $canAreaSearch = $specialSetting->area_search_filter->hasAreaSearchType();
        $canLineSearch = $specialSetting->area_search_filter->hasLineSearchType();
        $canSpatialSearch = Services\ServiceUtils::canSplSpatialSearch($params,$settings, $datas);


        $doc["div.element-tab-search ul"]->empty();
        if ($canAreaSearch)
        {
            $liElem = "<li class='active shikugun'><a href='#'>地域から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canLineSearch) {
            $liElem = "<li class='ensen'><a href='#'>沿線・駅から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canSpatialSearch) {
            $liElem = "<li class='spatial'><a href='#'>地図から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        /*
         * エリア選択の作成
         */
        $prefMaker = new Element\Pref();
        $prefs = $datas->getPrefSetting();
        $doc["table.element-search-table"]->replaceWith($prefMaker->createElement($prefs, $specialRow->filename));

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->searchConditions(
	            $doc, $params, $settings, $datas,
        		Element\SEOLinks::DISP_PREF);

        return $doc->html();
    }
}