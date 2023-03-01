<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class SplChoson extends Services\AbstractElementService
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
		$pNames = $datas->getParamNames();
		$ken_nm = $pNames->getKenName();
        $shikugun_nm = $pNames->getShikugunName();
        $this->header = $shikugun_nm ?
            "<h1 class='tx-explain'>{$specialRow->title}の物件情報を{$shikugun_nm}の町名(${ken_nm})から探す</h1>":
            "<h1 class='tx-explain'>{$specialRow->title}の物件情報を複数の地域から探す</h1>";

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
        // 地域から探す設定がない場合
        if (!$specialSetting->area_search_filter->hasAreaSearchType()) {
            throw new \Exception('指定された特集は地域から探す設定ではありません。', 404);
        }
        // 町名から探す設定がない場合
		if (!$specialSetting->area_search_filter->canChosonSearch()) {
			throw new \Exception('町名から探す設定ではありません。', 404);
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

		$pNames = $datas->getParamNames();
		$ken_nm = $pNames->getKenName();
        $shikugun_nm = $pNames->getShikugunName();
        // 特集の取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();

		$head = new Services\Head();
        if ($shikugun_nm) {
            $head->title = "{$specialRow->title}｜{$shikugun_nm}の町名(${ken_nm})から探す｜${siteName}";
            $head->keywords = "{$shikugun_nm} {$specialRow->title},${ken_nm} {$specialRow->title},町名選択,${keyword}";
            $head->description = "{$specialRow->title}：【${comName}】{$shikugun_nm}の町名(${ken_nm})から物件を探す。${description}";
        } else {
            $head->title = "{$specialRow->title} | 複数の地域から探す｜${siteName}";
            $head->keywords = "町名選択,${keyword}";
            $head->description = "{$specialRow->title}：【${comName}】複数の地域から物件を探す。${description}";
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
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/choson/content.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;

		$pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku_nm = $pNames->getShumokuName();

        // 都道府県名の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();
        // 市区群の取得（複数指定の場合は使用できない）
        $shikugun_nm = $pNames->getShikugunName();

        // 特集取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();


        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}{$市区郡名}の{$物件種目}を町名から探す
        // ホーム＞種別選択：1＞(都道府県選択：4＞)複数の町名から{$物件種目}を探す
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [];
        // 都道府県がひとつの場合は都道府県選択を表示しない
        if (count($specialSetting->area_search_filter->area_1) > 1) {
            $levels[ "/{$specialRow->filename}" ] = "都道府県選択";
        }
        if ($shikugun_nm) {
            $levels[''] = "{$ken_nm}${shikugun_nm}の町名から探す";
        } else {
            $levels[''] = "複数の地域から探す";
        }
        $this->breadCrumb =  $this->createSpecialBreadCrum($doc['div.breadcrumb'], $levels, $specialRow->title);

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

        $doc['h3:first']->text('町名を選択してください');

        if ($shikugun_nm) {
            // お探しの町名をお選びください。あなたのご希望に合った{$物件種目}の物件がきっと見つかります。{$市区郡名}{$町名}({$都道府県名})で{$物件種目}の不動産情報をお探しなら、{$会社名}におまかせ！
            $lead_sentence = "{$specialRow->title}｜お探しの町名をお選びください。".
                "あなたのご希望に合った物件がきっと見つかります。" .
                "${shikugun_nm}(${ken_nm})で不動産情報をお探しなら、${comName}におまかせ！";
        } else {
            // お探しの町名をお選びください。あなたのご希望に合った{$物件種目}の物件がきっと見つかります。{$都道府県名}で{$物件種目}の不動産情報をお探しなら、{$会社名}におまかせ！
            $lead_sentence = "{$specialRow->title}｜お探しの町名をお選びください。".
                "あなたのご希望に合った物件がきっと見つかります。" .
                "${ken_nm}で不動産情報をお探しなら、${comName}におまかせ！";
        }
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);

        /*
         * タブの作成
         */
        $canAreaSearch = $specialSetting->area_search_filter->hasAreaSearchType();
        $canLineSearch = $specialSetting->area_search_filter->hasLineSearchType();
        $canSpatialSearch = $specialSetting->area_search_filter->hasSpatialSearchType();

        // 都道府県がひとつの場合
        $prefs = $datas->getPrefSetting();
        $isOnePref = count($prefs) === 1;

        $doc["div.element-tab-search ul"]->empty();
        $hasTab = false;
        if ($canAreaSearch && $isOnePref)
        {
            $hasTab = true;
            $liElem = '<li class="active shikugun"><a href="#">地域から探す</a></li>';
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canLineSearch && $isOnePref) {
            $hasTab = true;
            $liElem = '<li class="ense"><a href="'."/{$specialRow->filename}/{$ken_ct}/line.html".'">沿線・駅から探す</a></li>';
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canSpatialSearch && $isOnePref) {
            $hasTab = true;
            $liElem = "<li class='spatial'><a href='/{$specialRow->filename}/${ken_ct}/map.html'>地図から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if (!$hasTab) {
            $doc['div.element-tab-search']->addClass('no-tab');
            $doc["div.element-tab-search ul"]->remove();
        }

        if(!$specialRow['display_freeword']){
            $doc[".element-input-search"]->remove();
        }
        
        // {$都道府県名}すべての物件を見る
        $doc['div.element-tab-search p.link-all-result a']->attr('href', "/{$specialRow->filename}/${ken_ct}/result/")->text("${ken_nm}すべての物件を見る");

        /**
         * 駅選択
         */
        $maker = new Element\Choson();
        $searchAreaElem = $maker->createElement($datas->getChosonList(), $specialRow->filename, $ken_ct, false, $pNames->getShikuguns(),$specialSetting->area_search_filter->canChosonSearch());
        $doc['div.element-search-area']->replaceWith($searchAreaElem);

        // こだわり条件
        $searchFilterElement = new Element\SearchFilter( $datas->getFrontSearchFilter() );
        $searchFilterElement->setSearchFilterSplCms(  $specialSetting->search_filter );
        $searchFilterElement->renderTable($specialSetting->enabled_estate_type, $doc);

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->searchConditions(
        		$doc, $params, $settings, $datas,
        		Element\SEOLinks::DISP_CHOSON);

        return $doc->html();
    }

}