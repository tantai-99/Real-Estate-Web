<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;

class SpatialMap extends Services\AbstractElementService
{
	public $head;
	public $header;
	public $content;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 設定がない場合
        if (! Services\ServiceUtils::canSpatialSearch($params,$settings, $datas)) {
            throw new \Exception('地図検索オプションが無効、または、地図から探す設定がありません。', 404);
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
        // 種目名称の取得
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県名の取得
        $ken_nm = $pNames->getKenName();
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();

        // 検索タイプ
        $s_type = $params->getSearchType();
        $title_txt   = "${shumoku_nm}を地図で検索｜${siteName}";
        $keyword_txt = "${shumoku_nm},地図検索,${keyword}";
        $desc_txt   = "【${comName}】${shumoku_nm}の地図検索結果。${description}";

        // ページID
        if ($params->getPage(1) != 1) {
            $page = $params->getPage();
            $page_txt = "【{$page}ページ目】";
            $title_txt .= $page_txt;
            $desc_txt  .= $page_txt;
        }

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

        $pNames = $datas->getParamNames();
        // 種目名称の取得
        $shumoku_nm = $pNames->getShumokuName();
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

        // ページ
        $page = $params->getPage(1);

        // 検索タイプ
        $s_type = $params->getSearchType();
        $h1_txt   = "${shumoku_nm}情報を地図で検索";


        if ($page > 1) {
            $h1_txt = $h1_txt . "　${page}ページ目";
        }

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
}