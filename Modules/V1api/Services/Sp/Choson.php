<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class Choson extends Services\AbstractElementService
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
        $shikugun_nm = $pNames->getShikugunName();
        $this->header = $shikugun_nm ?
            "<h1 class='tx-explain'>${shumoku_nm}情報を{$shikugun_nm}の町名(${ken_nm})から探す</h1>":
            "<h1 class='tx-explain'>${shumoku_nm}情報を複数の地域から探す</h1>";

		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		// 設定がない場合
		if (! $settings->search->canAreaSearch($params->getTypeCt())) {
			throw new \Exception('地域から探す設定がありません。', 404);
		}

        //都道府県チェック
        $pNames = $datas->getParamNames();
        $ken_cd  = $pNames->getKenCd();
        $prefs = $datas->getPrefSetting();
        $check = false;
        foreach ($prefs as $key => $value) {
            if($value == $ken_cd) {
                $check=true;
            }
        }
        if(!$check) throw new \Exception('都道府県設定がありません。', 404);

        // 町名から探す設定がない場合
        $settingObject = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        if (! $settings->search->canAreaSearch($params->getTypeCt()) || !$settingObject->area_search_filter->canChosonSearch()) {
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
        $shumoku_nm = $pNames->getShumokuName();
        $ken_nm = $pNames->getKenName();
        $shikugun_nm = $pNames->getShikugunName();

        $head = new Services\Head();
        if ($shikugun_nm) {
            // {$市区郡名}の町名({$都道府県名})から{$物件種目}を探す｜{$CMS初期設定サイト名}
            $head->title = "{$shikugun_nm}の町名(${ken_nm})から${shumoku_nm}を探す｜${siteName}";
            // {$市区郡名} {$物件種目},{$都道府県名} {$物件種目},町名選択,{$CMS初期設定キーワード}
            $head->keywords = "{$shikugun_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},町名選択,${keyword}";
            // 【{$会社名}】{$市区郡名}の町名({$都道府県名})から{$物件種目}を探す。{$CMS初期設定サイトの説明}
            $head->description = "【${comName}】{$shikugun_nm}の町名(${ken_nm})から${shumoku_nm}を探す。${description}";
        } else {
            // 複数の地域から{$物件種目}を探す｜{$CMS初期設定サイト名}
            $head->title = "複数の地域から${shumoku_nm}を探す｜${siteName}";
            // 町名選択,{$CMS初期設定キーワード}
            $head->keywords = "町名選択,${keyword}";
            // 【{$会社名}】複数の地域から{$物件種目}を探す。{$CMS初期設定サイトの説明}
            $head->description = "【${comName}】複数の地域から${shumoku_nm}を探す。${description}";
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
		$doc = $this->getTemplateDoc("/choson/content.sp.tpl");

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

        $shikugun_nm = $pNames->getShikugunName();

        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}{$市区郡名}の{$物件種目}を町名から探す
        // ホーム＞種別選択：1＞(都道府県選択：4＞)複数の町名から{$物件種目}を探す
        $top = $this->getSearchTopFileName($searchCond);
        $levels["/${top}"] = $this::BREAD_CRUMB_1ST_SHUMOKU;
        if (count($datas->getPrefSetting()) !== 1) {
            $levels["/${type_ct}/"] = $this::BREAD_CRUMB_2ND_PREF;
        }
        if ($shikugun_nm) {
            $levels[''] = "{$ken_nm}${shikugun_nm}の${shumoku_nm}を町名から探す";
        } else {
            $levels[''] = "複数の地域から${shumoku_nm}を探す";
        }
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        $doc['h2']->text("${ken_nm}の${shumoku_nm}を市区郡・町名から探す");
        // $doc['.heading-lv1-1column']->next()->remove(); // 特集用テキストの削除

        $doc['h3:first']->text('町名を選択してください');


        /**
         * エリア選択
         */
        $ekiMaker = new Element\Choson();
        $searchAreaElem = $ekiMaker->createElement($datas->getChosonList(), $type_ct, $ken_ct, false, $pNames->getShikuguns());

        $doc['section.element-narrow-down section dl']->empty();
        $doc['section.element-narrow-down section dl']->append($searchAreaElem->children());
        $pSearchFilter = $params->getSearchFilter();
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
        if($settingRow->display_freeword && isset($pSearchFilter["fulltext_fields"]) && !empty(trim($pSearchFilter["fulltext_fields"]))){
            $freewordText = $pSearchFilter["fulltext_fields"];
            $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars($freewordText));
        }else{
            $doc[".element-input-search-result"]->remove();
        }
        return $doc->html();
    }
}