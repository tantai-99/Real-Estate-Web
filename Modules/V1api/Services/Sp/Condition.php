<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\EnsenEki;
use Library\Custom\Model\Estate;
class Condition extends Services\AbstractElementService
{
	public $head;
	public $header;
	public $content;
    public $display_freeword;
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
		$this->header = "<h1 class='tx-explain'>${shumoku_nm}情報を${ken_nm}の地域から探す</h1>";

		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
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
		$head->title = "${ken_nm}の地域から${shumoku_nm}を探す｜${siteName}";
		$head->keywords = "${ken_nm},${shumoku_nm},地域から探す,${keyword}";
		$head->description = "【${comName}】${ken_nm}の${shumoku_nm}を地域から探す。${description}";
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
		$searchCond = $settings->search;
        $pSearchFilter = $params->getSearchFilter();

		$pNames = $datas->getParamNames();

        // 検索タイプ
        $s_type = $params->getSearchType();

        // 種目情報の取得
        $type_ct = (array) $params->getTypeCt();
        $type_ct = $type_ct[0];
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

        // こだわり条件
        $searchFilter = $datas->getSearchFilter();

        /*
         * パンくず作成
         */
        // ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}の{$物件種目}を市区郡から探す
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [
            // URL = NAME
            "/${top}"      => $this::BREAD_CRUMB_1ST_SHUMOKU
        ];
        //count($datas->getPrefSetting()) !== 1
        if ($datas->getPrefSetting() !== null && count($datas->getPrefSetting())!==1) {
            $levels += [
                "/${type_ct}/" => $this::BREAD_CRUMB_2ND_PREF
            ];
        }
        // 検索タイプ
        $s_type = $params->getSearchType();
        switch ($s_type)
        {
// 1. ホーム＞種別選択：1＞(都道府県選択：4＞)沿線から探す：6＞{$沿線名：９}＞{$沿線名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_LINE:
                $levels += array("/${type_ct}/${ken_ct}/line.html" => '沿線から探す');
                $levels += array("/${type_ct}/${ken_ct}/${ensen_ct}-line/" => "${ensen_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/${ensen_ct}-line.html" => "${ensen_nm}の${shumoku_nm}一覧");
                break;
// 2. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$市区名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_CITY:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/${shikugun_ct}-city.html" => "${shikugun_nm}の${shumoku_nm}一覧");
                break;
// 3. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$政令指定都市}の{$物件種目}一覧
            case $params::SEARCH_TYPE_SEIREI:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/${locate_ct}-mcity.html" => "${locate_nm}の${shumoku_nm}一覧");
                break;
// 4. ホーム＞種別選択：1＞(都道府県選択：4＞)沿線から探す：6＞{$沿線名：９}＞{$駅名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_EKI:
                $levels += array("/${type_ct}/${ken_ct}/line.html" => '沿線から探す');
                $levels += array("/${type_ct}/${ken_ct}/${ensen_ct}-line/" => "${ensen_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/${eki_ct}-eki.html" => "${eki_nm}駅の${shumoku_nm}一覧");
                break;
// 5. ホーム＞種別選択：1＞(都道府県選択：4＞)地図から探す：7＞{$市区名}の{$物件種目}地図一覧
//  next
// 6. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_PREF:
                $levels += array("/${type_ct}/${ken_ct}/result/" => "${ken_nm}の${shumoku_nm}一覧");
                break;
// 7. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$都道府県名}の{$物件種目}一覧
// 8. ホーム＞種別選択：1＞(都道府県選択：4＞){$都道府県名}：5＞{$都道府県名}の{$物件種目}一覧
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/" => "${ken_nm}の${shumoku_nm}一覧");
                break;
            case $params::SEARCH_TYPE_MAP:
                $levels += array("/${type_ct}/${ken_ct}/" => "${ken_nm}");
                $levels += array("/${type_ct}/${ken_ct}/result/${shikugun_ct}-map.html" => "${ken_nm}の${shumoku_nm}一覧");
                break;
        }
        $levels += array('' => "条件絞り込み");
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        $doc['h2']->text("${shumoku_nm}の条件を絞り込む");
        // $doc['.heading-lv1-1column']->next()->remove(); // 特集用テキストの削除

        $doc['h3:first']->remove();

        // $lead_sentence = 'お探しの市区郡をお選びください。'.
        //     "あなたのご希望に合った${shumoku_nm}の物件がきっと見つかります。" .
        //     "${ken_nm}で${shumoku_nm}の不動産情報をお探しなら、${comName}におまかせ！";
        // $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);
        $doc["div.element-search-tab"]->remove();
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
        $this->display_freeword = $settingRow->display_freeword;
        if($settingRow->display_freeword){
            if (!empty($pSearchFilter["fulltext_fields"])) {
                if (!empty(trim($pSearchFilter["fulltext_fields"]))) {
                    $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
                }
            }
        }else{
            $doc[".element-input-search"]->remove();
        }

        $searchFilter->loadEnables(Estate\TypeList::getInstance()->getTypeByUrl($type_ct));
        $searchFilterElement = new Element\SearchFilter( $searchFilter );

        $doc['section.element-narrow-down section']->remove();
        $doc['section.element-narrow-down']->addClass('detail');

        /**
         * 町名検索
         */
        switch ($s_type) {
            case $params::SEARCH_TYPE_CITY:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $settingRow = $searchCond->getSearchSettingRowByTypeCt($type_ct);
                $settingObject = $settingRow->toSettingObject();
                if ($settingObject->area_search_filter->canChosonSearch()) {
                    if (count($pNames->getShikuguns()) > 5) {
                        $url = "/{$type_ct}/{$ken_ct}/";
                        $link = '<a class="list-select-set-link is-back" href="'.$url.'"><span class="list-select-set-link-note">さらに町名を選択する場合は、市区郡選択画面へ戻り、市区郡を5つ以下に絞り込んでください。</span><span>市区郡選択画面に戻る</span></a>';
                    } else {
                        $selectedChosonHtml = '';
                        $selectedCount = 0;
                        $displays = [];
                        if ($chosonShikuguns = $pNames->getChosonShikuguns()) {
                            foreach ($chosonShikuguns as $shikugunObj) {
                                $selectedCount += count($shikugunObj->chosons);
                                foreach ($shikugunObj->chosons as $chosonObj) {
                                    if (count($displays) == 2) {
                                        break;
                                    }
                                    $displays[] = $shikugunObj->shikugun_nm . $chosonObj->choson_nm;
                                }
                            }
                            $selectedChosonHtml .= '<span class="list-select-set-link-note">';
                            $selectedChosonHtml .= implode('・', $displays);
                            $hiddenCount = $selectedCount - count($displays);
                            if ($hiddenCount > 0) {
                                $selectedChosonHtml .= "（その他{$hiddenCount}地域）";
                            }
                            $selectedChosonHtml .= '</span>';
                        }
                        $url = "/{$type_ct}/{$ken_ct}/city/search/";
                        $link = '<a class="list-select-set-link is-goto js-select-choson" href="'.$url.'">'.$selectedChosonHtml.'<span>さらに町名を選択する</span></a>';
                    }
                    $doc['section.element-narrow-down']->append(
                        '<section>'.
                        '<h4 class="heading-select-set">町名</h4>'.
                        '<ul class="list-select-set">'.
                        '<li>'.$link.'</li>'.
                        '</ul>'.
                        '</section>'
                    );
                }
                break;
        }

        $doc['section.element-narrow-down']->append( $searchFilterElement->createDesiredTableElement() );
        $doc['section.element-narrow-down']->append( $searchFilterElement->createParticularTableElement() );
        if ($params->getCenter()) {
            $doc['h2']->after(sprintf('<input type="hidden" value="%s">', $params->getCenter()));
        }
        return $doc->html();
    }
}
