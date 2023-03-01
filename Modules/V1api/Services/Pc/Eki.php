<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;
use Library\Custom\Estate\Setting\SearchFilter\Front;

class Eki extends Services\AbstractElementService
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
        $ensen_nm = $pNames->getEnsenName();
        $this->header = $ensen_nm ?
            "<h1 class='tx-explain'>${shumoku_nm}情報を${ensen_nm}(${ken_nm})から探す</h1>":
            "<h1 class='tx-explain'>${shumoku_nm}情報を複数の沿線から探す</h1>";

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

        $pNames = $datas->getParamNames();

        //都道府県チェック
        $ken_cd  = $pNames->getKenCd();
        $prefs = $datas->getPrefSetting();
        $check = false;
        foreach ($prefs as $key => $value) {
            if($value == $ken_cd) {
                $check=true;
            }
        }

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
		$ensen_nm = $pNames->getEnsenName();

		$head = new Services\Head();
        if ($ensen_nm) {
            $head->title = "${ensen_nm}(${ken_nm})から${shumoku_nm}を探す｜${siteName}";
            $head->keywords = "${ensen_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},沿線・駅から探す,${keyword}";
            $head->description = "【${comName}】${ensen_nm}(${ken_nm})から${shumoku_nm}を探す。${description}";
        } else {
            $head->title = "複数の沿線から${shumoku_nm}を探す｜${siteName}";
            $head->keywords = "駅選択,${keyword}";
            $head->description = "【${comName}】複数の沿線から${shumoku_nm}を探す。${description}";
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
		$doc = $this->getTemplateDoc("/eki/content.tpl");
        $pSearchFilter = $params->getSearchFilter();

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
        $ken_nm  = $pNames->getKenName();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        $ensen_nm = $pNames->getEnsenName();


        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞(都道府県選択：4＞){$沿線名}の{$物件種目}から探す
        $top = $this->getSearchTopFileName($searchCond);
        $levels["/${top}"] = $this::BREAD_CRUMB_1ST_SHUMOKU;
        if (count($datas->getPrefSetting()) !== 1) {
            $levels["/${type_ct}/"] = $this::BREAD_CRUMB_2ND_PREF;
        }
        if ($ensen_nm) {
            $levels[''] = "${ensen_nm}の${shumoku_nm}から探す";
        } else {
            $levels[''] = "複数の沿線から${shumoku_nm}を探す";
        }
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        $doc['h2']->text("${ken_nm}の${shumoku_nm}を沿線・駅から探す");
        $doc['.heading-lv1-1column']->next()->remove(); // 特集用テキストの削除

        $doc['h3:first']->text('駅を選択してください');

        if ($ensen_nm) {
            $lead_sentence = 'お探しの駅をお選びください。'.
                "あなたのご希望に合った${shumoku_nm}の物件がきっと見つかります。" .
                "${ensen_nm}(${ken_nm})で${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";
        } else {
            $lead_sentence = 'お探しの駅をお選びください。'.
                "あなたのご希望に合った${shumoku_nm}の物件がきっと見つかります。" .
                "${ken_nm}で${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";
        }
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);
        /*
         * タブ削除
         */
        $doc["div.element-tab-search"]->remove();

        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
        if($settingRow->display_freeword){
            if (isset($pSearchFilter["fulltext_fields"])) {
                $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
            }
        } else{
            $doc[".element-input-search"]->remove();
        }
        /**
         * 駅選択
         */
        $ekiMaker = new Element\Eki();
        $ekiWithEnsen = $datas->getEkiList();
        $ekiSettingOfKen = $datas->getEkiSettingOfKen();
        $searchAreaElem = $ekiMaker->createElement($ekiWithEnsen, $type_ct, $ken_ct, false, $ekiSettingOfKen);

        $doc['div.element-search-area']->replaceWith($searchAreaElem);

        // こだわり条件
        $frontSearchFilter = new Front();
        $frontSearchFilter->loadEnables(Estate\TypeList::getInstance()->getTypeByUrl($type_ct));
        $searchFilterElement = new Element\SearchFilter( $frontSearchFilter );
        $searchFilterElement->renderTable($type_id, $doc);

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->searchConditions(
        		$doc, $params, $settings, $datas,
        		Element\SEOLinks::DISP_EKI);

        return $doc->html();
    }

}