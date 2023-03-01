<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class SplSpatialMap extends Services\AbstractElementService
{
	public $head;
	public $header;
	public $content;
	public $info;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
		$this->content = $this->content($params, $settings, $datas);
		$this->info = $this->info($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 地図検索オプションが無効、または、地図から探す設定がない場合
        if (! Services\ServiceUtils::canSplSpatialSearch($params,$settings, $datas)) {
            throw new \Exception('地図検索オプションが無効、または、指定された特集は地図から探す設定ではありません。', 404);
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
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();

        // 検索タイプ
        $s_type = $params->getSearchType();
        // {$特集名}｜{$都道府県名}{$市区名}から検索｜{$CMS初期設定サイト名}
        $title_txt   = "{$specialRow->title}｜${ken_nm}${shikugun_nm}から地図で検索｜${siteName}";
        // {$市区名} {$特集名},{$都道府県名} {$特集名},検索,{$CMS初期設定キーワード}
        $keyword_txt = "${shikugun_nm} {$specialRow->title},${ken_nm} {$specialRow->title},地図検索,${keyword}";
        // {$特集名}：【{$会社名}】{$市区名}の検索結果一覧。{$CMS初期設定サイトの説明}
        $desc_txt   = "{$specialRow->title}：【${comName}】${shikugun_nm}の地図検索結果。${description}";

		$head = new Services\Head();
		$head->title = $title_txt;
		$head->keywords = $keyword_txt;
		$head->description = $desc_txt;
		return $head->html();
	}

	private function header(
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
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();

		// {$特集名}の物件情報を{$市区名}から検索
		$h1_txt   = "{$specialRow->title}の物件情報を${shikugun_nm}から地図検索";

		return "<h1 class='tx-explain'>$h1_txt</h1>";
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
		$doc = $this->getTemplateDoc("/spatial/content.tpl");
		$doc['.articlelist-side-section section']->remove();
		$doc['.articlelist-side-section p']->remove();

        return $doc->html();
    }
	private function info(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		if (!is_null($params->getSpecialPath())) {
			// 特集を取得
			$specialRow = $settings->special->getCurrentPagesSpecialRow();
			$specialSetting = $specialRow->toSettingObject();
			$type_id = $specialSetting->enabled_estate_type[0];
			$type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
			return ['type' => $type_ct];
		}
	}
}