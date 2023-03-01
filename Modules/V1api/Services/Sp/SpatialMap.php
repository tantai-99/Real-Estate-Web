<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class SpatialMap extends Services\AbstractElementService
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
    }

    public function check(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        // 地図検索オプションが無効、または、地図から探す設定がない場合
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
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();
        $title_txt   = "${ken_nm}${shikugun_nm}から${shumoku_nm}を地図で検索｜${siteName}";
        $keyword_txt = "${shikugun_nm} ${shumoku_nm},${ken_nm} ${shumoku_nm},地図検索,${keyword}";
        $desc_txt   = "【${comName}】${shikugun_nm}の${shumoku_nm}の地図検索結果一覧。${description}";

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
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();

        $h1_txt   = "${shumoku_nm}情報を${shikugun_nm}から地図で検索";
        return "<h1 class='tx-explain'>$h1_txt</h1>";
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
        $doc = $this->getTemplateDoc("/spatial/content.sp.tpl");

        return $doc->html();
    }
}