<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\Settings;

class SpatialModal extends Services\AbstractElementService
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
        $this->content = $this->content($params, $settings, $datas);
    }

    public function check(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
    }

    private function content(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        $doc = $this->getTemplateDoc("/result/content.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;

        $pNames = $datas->getParamNames();
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();
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
         * 見出し処理
         */
        // 検索タイプによって、テキストは異なる。
        $doc['section.articlelist-inner div:first']->remove();
        $h2Text = "地図上で表示されている物件の一覧";
        $doc['div.contents-right h2:first']->text($h2Text);
        /*
         * 物件一覧
         */
        $resultMaker = new Element\Result();
        $bukkenList = $datas->getBukkenList();
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, !!$params->getSpecialPath(), $settings->page, $settings->search);
        $total_count = $bukkenList['total_count'];

        // modal用に修正
        $doc['.contents']->wrap('<div class="contents-iframe search-modal-bl-all"></div>');
        $doc['dl.sort-select']->remove();
        $doc['.contents-iframe.search-modal-bl-all'] = $doc['.articlelist-inner'];
        $doc['.contents-iframe.search-modal-bl-all']->wrap('<div class="floatbox__map"></div>');
        $doc['.floatbox__map']->wrap('<div class="floatbox"></div>');
        $doc['.floatbox']->append('<p class="btn-close">閉じる</p>');

        return $doc->html();
    }
}
