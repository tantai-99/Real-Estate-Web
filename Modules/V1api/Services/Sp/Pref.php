<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class Pref extends Services\AbstractElementService
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
		$this->header = "<h1 class='tx-explain'>{$datas->getParamNames()->getShumokuName()}情報を都道府県から探す</h1>";
		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		//種目チェック
        $type_ct = $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$settingShumoku = $settings->search->getShumoku();
		$check = false;
		foreach ($settingShumoku as $key => $value) {
			if($value == $type_id) $check = true;
		}
		if(!$check) throw new \Exception('種目が設定されていない', 404);

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
		$pageSetting = $settings->page;
		// 種目名称の取得
		$shumoku_nm = $datas->getParamNames()->getShumokuName();

		$head = new Services\Head();
		$head->title = "都道府県から{$shumoku_nm}を探す｜{$pageSetting->getSiteName()}";
		$head->keywords = "都道府県選択,${shumoku_nm},{$pageSetting->getKeyword()}";
		$head->description = "【{$pageSetting->getCompanyName()}】${shumoku_nm}の都道府県選択画面。{$pageSetting->getDescription()}";
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
		$doc = $this->getTemplateDoc("/pref/content.sp.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
		$specialSettings = $settings->special;

		$pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();

        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞都道府県から選択する
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [
            // URL = NAME
            "/${top}"  => $this::BREAD_CRUMB_1ST_SHUMOKU, // TODO
            ''          => '都道府県から選択する'
        ];
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        $doc['h2']->text("${shumoku_nm}を都道府県から探す");
        // $doc['.heading-lv1-1column']->next()->remove();
        $doc['.article-heading']->next()->find('p')->remove();

        $doc['h3']->text('お探しの都道府県を選んでください');

        // $lead_sentence = 'お探しの都道府県をお選びください。'.
        //     "あなたのご希望に合った${shumoku_nm}の物件がきっと見つかります。" .
        //     "${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";
        // $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);
        $doc[".heading-lv2-1column"]->next()->children()->remove();

        /*
         * タブの作成
         */
        $canAreaSearch    = $searchCond->canAreaSearch($type_ct);
        $canLineSearch    = $searchCond->canLineSearch($type_ct);
        $canSpatialSearch = Services\ServiceUtils::canSpatialSearch($params,$settings, $datas);

        $doc["div.element-search-tab ul"]->empty();
        if ($canAreaSearch)
        {
            $liElem = "<li class='active shikugun'><a href='#'>地域から探す</a></li>";
            $doc["div.element-search-tab ul"]->append($liElem);
        }
        if ($canLineSearch) {
            $liElem = pq("<li class='ensen'><a href='#'>沿線・駅から探す</a></li>");
            if (!$canAreaSearch && !$canSpatialSearch) {
                $liElem->addClass('active');
            }
            $doc["div.element-search-tab ul"]->append($liElem);
        }
        if ($canSpatialSearch) {
            $liElem = pq("<li class='spatial'><a href='#'>地図から探す</a></li>");
            if (!$canAreaSearch && !$canLineSearch) {
                $liElem->addClass('active');
            }
            $doc["div.element-search-tab ul"]->append($liElem);
            if ($searchCond->canMapSearchHere($type_ct)) {
                $here = pq('<ul class="element-search-list">');
                $tmp = pq("<li><a href='/$type_ct/here/result/here-map.html'>現在地から探す</a></li>");
                $here->append($tmp);
            }
        }
        if ($canAreaSearch && $canLineSearch && $canSpatialSearch) {
            $doc["div.element-search-tab"]->addClass('three');
        }

        /*
         * エリア選択の作成
         */
        $prefs = $datas->getPrefSetting();
        $prefMaker = new Element\Pref();
        $doc["dl.element-search-toggle"]->replaceWith($prefMaker->createElement($prefs, $type_ct));

        if (isset($here)) {
            $doc["div.element-search-tab"]->after($here);
        }


//         /**  @TODO 確認　SEOリンクはPC版だけのはず
//          * 特集・他の物件種目から探す
//          */
//         $otherSpecialElem = $doc['.element-search-from:eq(0)'];
//         $otherShumokuElem = $doc['.element-search-from:eq(1)'];

//         // 同種目の特集を取得
//         $specials = $specialSettings->pickSpecialRowsByTypeCt($type_ct);
//         if ($specials) {
//         	$otherSpecialElem['h4']->text("{$shumoku_nm}の特集から探す");
//         	$ul = $otherSpecialElem['ul'];
//         	$ul->empty();
//         	foreach ($specials as $row) {
//         		$li = pq('<li><a></a></li>');
//         		$li['a']->text($row->title)
//         		->attr('href', "/{$row->filename}")
//         		->attr('target', '_blank');
//         		$ul->append($li);
//         	}
//         }
//         else {
//         	$otherSpecialElem->remove();
//         }

//         // 他の物件種目
//         $typeList = Estate\TypeList::getInstance();
//         $otherShumoks = $searchCond->getShumokuWithoutTypeCt($type_ct);
//         if ($otherShumoks) {
//         	$ul = $otherShumokuElem['ul'];
//         	$ul->empty();
//         	foreach ($otherShumoks as $type) {
//         		$li = pq('<li><a></a></li>');
//         		$li['a']->text($typeList->get($type))
//         		->attr('href', "/{$typeList->getUrl($type)}")
//         		->attr('target', '_blank');
//         		$ul->append($li);
//         	}
//         }
//         else {
//         	$otherSpecialElem->removeClass('element-line');
//         	$otherShumokuElem->remove();
//         }

       return $doc->html();
    }
}