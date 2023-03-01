<?php
namespace Modules\V1api\Services\Sp;

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
        $shumoku_nm = $pNames->getShumokuName();
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
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県名の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;

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
        $this->breadCrumb = $this->createSpecialBreadCrumbSp($doc['div.breadcrumb'], $levels, $specialRow->title);

        /*
         * 見出し処理
         */
        $doc['h2']->text($specialRow->title);
        // $doc['.heading-lv1-1column']->next()->remove(); // 特集用テキストの削除

        $doc['h3:first']->text('町名を選択してください');


        /**
         * エリア選択
         */
        $ekiMaker = new Element\Choson();
        $searchAreaElem = $ekiMaker->createElement($datas->getChosonList(), $type_ct, $ken_ct, false, $pNames->getShikuguns());

        $doc['section.element-narrow-down section dl']->empty();
        $doc['section.element-narrow-down section dl']->append($searchAreaElem->children());
        if(!$specialRow->display_freeword){
            $doc[".element-input-search"]->remove();
        }
        return $doc->html();
    }
}