<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class SplCity extends Services\AbstractElementService
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
        $ken_nm = Services\ServiceUtils::getKenNameByConst($params->getKenCt());
        $this->header = "<h1 class='tx-explain'>{$specialRow->title}の物件情報を{$ken_nm}の地域から探す</h1>";

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
		// 都道府県モデル
		$ken_nm = Services\ServiceUtils::getKenNameByConst($params->getKenCt());


		$head = new Services\Head();
		$head->title = "{$specialRow->title}｜{$ken_nm}の地域から探す｜${siteName}";
		$head->keywords = "{$ken_nm} {$specialRow->title},地域から探す,${keyword}";
		$head->description = "{$specialRow->title}：【${comName}】{$ken_nm}の物件を地域から探す。${description}";
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
		$doc = $this->getTemplateDoc("/city/content.sp.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();

        // 特集取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();

        $searchCond = $settings->search;
        $pNames = $datas->getParamNames();

//         // 検索用用絞り込み条件
//         $searchFilter = $specialSetting->search_filter;
//         // 表示用絞り込み条件
//         $frontSearchFilter = $searchFilter->toFrontSearchFilter($specialSetting->enabled_estate_type);
//         // 検索パラメータ初期化
//         $searchFilter->setValues($specialSetting->enabled_estate_type, []);
//         $frontSearchFilter->setValues($specialSetting->enabled_estate_type, []);
//         // 絞り込み条件未設定項目ロード
//         $frontSearchFilter->loadEnables($specialSetting->enabled_estate_type);

        // 都道府県名の取得
        // 都道府県がひとつの場合はその値を使用する
        $prefModel = Estate\PrefCodeList::getInstance();
        $ken_ct = $params->getKenCt();
        $ken_cd  = $prefModel->getCodeByUrl($ken_ct);
        //$ken_nm  = $prefModel->getNameByUrl($ken_ct);
        $ken_nm  = $pNames->getKenName();;
        /*
         * パンくず作成
         */
        // ホーム＞{$特集名}：(都道府県選択：35＞){$都道府県名}の市区郡から探す
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [];
        // 都道府県がひとつの場合は都道府県選択を表示しない
        if (count($specialSetting->area_search_filter->area_1) > 1) {
            $levels[ "/{$specialRow->filename}" ] = "都道府県選択";
        }
        $levels[''] = "${ken_nm}の市区郡から探す";
        $this->breadCrumb = $this->createSpecialBreadCrumbSp($doc['div.breadcrumb'], $levels, $specialRow->title);

        /*
         * 見出し処理
         */
        $doc['h2']->text($specialRow->title);
        // // 特集名
        // if ($specialRow->comment) {
        // 	$doc['.heading-lv1-1column']->next()->find('p')->html(nl2br(htmlspecialchars($specialRow->comment)));
        // }
        // else {
        // 	$doc['.heading-lv1-1column']->next()->find('p')->remove();
        // }

        $doc['h3']->text('市区郡を選択してください');

        $lead_sentence = "{$specialRow->title}｜お探しの市区郡をお選びください。あなたのご希望に合った物件がきっと見つかります。{$ken_nm}で不動産情報をお探しなら、${comName}におまかせ！";
        // $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);


        /*
         * タブの作成
         */
        $canAreaSearch = $specialSetting->area_search_filter->hasAreaSearchType();
        $canLineSearch = $specialSetting->area_search_filter->hasLineSearchType();
        $canSpatialSearch = $specialSetting->area_search_filter->hasSpatialSearchType();

        $disable_tab = $params->getDisableSTypeTab();
        $doc["div.element-search-tab ul"]->empty();
        $tabCount = 0;

        // 都道府県がひとつの場合
        $isOnePref = count($specialSetting->area_search_filter->area_1) === 1;

        if ($canAreaSearch && !$disable_tab && $isOnePref)
        {
            $tabCount++;
            $liElem = '<li class="active shikugun"><a href="#">地域から探す</a></li>';
            $doc["div.element-search-tab ul"]->append($liElem);
        }
        if ($canLineSearch && !$disable_tab && $isOnePref) {
            $tabCount++;
            $liElem = '<li class="ense"><a href="'."/{$specialRow->filename}/{$ken_ct}/line.html".'">沿線・駅から探す</a></li>';
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

        // {$都道府県名}すべての物件を見る
        $doc['div.element-search-tab p.link-all-result a']->attr('href', "/{$specialRow->filename}/${ken_ct}/result/")->text("${ken_nm}すべての物件を見る");

        /**
         * 市区町村選択
         */
        $cityMaker = new Element\City();
        $shikugunWithLocateCd = $datas->getCityList();
        $searchAreaElem = $cityMaker->createElement($shikugunWithLocateCd, $specialRow->filename, $ken_ct, false);
        $doc['section.element-narrow-down section section']->remove();
        $doc['section.element-narrow-down section']->append($searchAreaElem);

        //こだわり条件＿htmlには反映されてない
        // $searchFilterElement = new Element\SearchFilter( $frontSearchFilter );
        // $doc['.qp-desired-container']->append( $searchFilterElement->createDesiredTableElement() );
        // $doc['.qp-particular-container']->append( $searchFilterElement->createParticularTableElement() );

        if(!$specialRow->display_freeword || $this->removeSearchFreeword($shikugunWithLocateCd)){
            $doc[".element-input-search"]->remove();
        }

        return $doc->html();
    }
}