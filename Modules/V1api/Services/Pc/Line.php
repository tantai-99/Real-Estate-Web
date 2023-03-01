<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;
class Line extends Services\AbstractElementService
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

		$pNames = $datas->getParamNames();
		$shumoku_nm = $pNames->getShumokuName();
		$ken_nm = $pNames->getKenName();
		$this->header = "<h1 class='tx-explain'>${shumoku_nm}情報を${ken_nm}の沿線から探す</h1>";

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

	    // 沿線から探す設定がない場合
        if (! $settings->search->canLineSearch($params->getTypeCt())) {
            throw new \Exception('沿線・駅から探す設定ではありません。', 404);
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
		$shumoku_nm = $pNames->getShumokuName();
		$ken_nm = $pNames->getKenName();

		$head = new Services\Head();
		$head->title = "${ken_nm}の沿線から${shumoku_nm}を探す｜${siteName}";
		$head->keywords = "${ken_nm} ${shumoku_nm},沿線・駅から探す,${keyword}";
		$head->description = "【${comName}】${ken_nm}の${shumoku_nm}を沿線・駅から探す。${description}";

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
		$doc = $this->getTemplateDoc("/line/content.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;

		$pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県名の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;

        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}の{$物件種目}を沿線から探す
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
        $levels += [
            ''             => "${ken_nm}の${shumoku_nm}を沿線から探す"
        ];
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        $doc['h2']->text("${ken_nm}の${shumoku_nm}を沿線・駅から探す");
        $doc['.heading-lv1-1column']->next()->remove(); // 特集用テキストの削除

        $doc['h3:first']->text('路線を選択してください。（５つまで選択できます。）');

        $lead_sentence = 'お探しの路線をお選びください。'.
            "あなたのご希望に合った${shumoku_nm}の物件がきっと見つかります。" .
            "${ken_nm}で${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);

        /*
         * タブの作成
         */
        $canAreaSearch = $searchCond->canAreaSearch($type_ct);
        $canLineSearch = $searchCond->canLineSearch($type_ct);
		$canSpatialSearch = $searchCond->canSpatialSearch($type_ct);

        // 都道府県がひとつの場合
        $prefs = $datas->getPrefSetting();
        $isOnePref = count($prefs) === 1;

        $doc["div.element-tab-search ul"]->empty();
        $hasTab = false;

        if ($canAreaSearch && ($params->getDirectAccess() || $isOnePref))
        {
            $hasTab = true;
            $liElem = "<li class='shikugun'><a href='/${type_ct}/${ken_ct}/'>地域から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canLineSearch && ($params->getDirectAccess() || $isOnePref)) {
            $hasTab = true;
            $liElem = "<li class='active ensen'><a href='/${type_ct}/${ken_ct}/line.html'>沿線・駅から探す</a></li>";
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if ($canSpatialSearch && ($params->getDirectAccess() || $isOnePref)) {
            $hasTab = true;
            $liElem = pq("<li class='spatial'><a href='/${type_ct}/${ken_ct}/map.html'>地図から探す</a></li>");
            $doc["div.element-tab-search ul"]->append($liElem);
        }

        if (!$hasTab) {
            $doc['div.element-tab-search']->addClass('no-tab');
            $doc["div.element-tab-search ul"]->remove();
        }

        // {$都道府県名}すべての物件を見る
		// 設定がある場合
		if ($settings->search->canAreaSearch($params->getTypeCt())) {
	        $doc['div.element-tab-search p.link-all-result a']->attr('href', "/${type_ct}/${ken_ct}/result/")->text("${ken_nm}すべての物件を見る");
		} else {
			$doc['div.element-tab-search p.link-all-result']->remove();
		}

        /**
         * 沿線選択
         */
        $ensenMaker = new Element\Ensen();
        $ensenWithGroup = $datas->getLineList();
        $ensenCountList = $datas->getLineCountList();
        $searchAreaElem = $ensenMaker->createElement($ensenWithGroup, $ensenCountList, $type_ct, $ken_ct, false);
        $doc['div.element-search-area']->replaceWith($searchAreaElem);

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->searchConditions(
        		$doc, $params, $settings, $datas,
        		Element\SEOLinks::DISP_LINE);

        return $doc->html();
    }
}