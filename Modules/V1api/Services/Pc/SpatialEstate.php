<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;
use Modules\V1api\Services\BApi;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;

class SpatialEstate extends Services\AbstractElementService
{
    public $content;
    public $aside;
    public $detachedHouse;

    public function create(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        $this->content = $this->content($params, $settings, $datas);
        $this->aside = $this->aside($params, $settings, $datas);
        $this->detachedHouse = $this->detachedHouse($params, $settings, $datas);
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
        $spatialEstate = $datas->getSpatialEstate();
        $coordinates = $spatialEstate['coordinates'];
        $total_count = $spatialEstate['total_count'];
        return compact('coordinates', 'total_count');
    }

    private function aside(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        $doc = $this->getTemplateDoc("/spatial/aside.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;
        $pSearchFilter = $params->getSearchFilter();

        $pNames = $datas->getParamNames();
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = (array) $params->getTypeCt();
        $type_id = [];
        foreach ($type_ct as $ct) {
            $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
        }
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

        // 都道府県名と市区郡
        if ($ken_ct) {
            $doc['.articlelist-side-section .area']->text($ken_nm);
            $doc['.articlelist-side-section .area-detail']->text($shikugun_nm);
        }
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct[0]);

        // こだわり条件
        $searchFilterSplCms = null;
        // 通常検索
        if (is_null($params->getSpecialPath())) {
            $searchFilter = $datas->getSearchFilter();
        // 特集
        } else {
            $searchFilter = $datas->getFrontSearchFilter();
            $searchFilterSplCms = $datas->getSearchFilter();
            $settingRow = $settings->special->getCurrentPagesSpecialRow();
        }
        if($settingRow->display_freeword){
            if (!empty($pSearchFilter)) {
                if (!empty(trim($pSearchFilter["fulltext_fields"]))) {
                    $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
                }
            }
        }else{
            $doc[".element-input-search-result"]->remove();
        }

        $searchFilterElement = new Element\SearchFilter( $searchFilter );
        if ($searchFilterSplCms) {
            $searchFilterElement->setSearchFilterSplCms( $searchFilterSplCms );
        }

        $bukkenList = $datas->getBukkenList();
        $facet = new BApi\SearchFilterFacetTranslator();
        $facet->setFacets($bukkenList['facets']);
        $total_count = $bukkenList['total_count'];
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($settingRow->estate_class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($settingRow->estate_class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['p.btn-request']->append($estateRequest['requestUrl']);
            } else {
                $doc['p.request']->remove();
            }
        } else {
            if(isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $settingRow->estate_class);
                if($hpPageRow) {
                    $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                    $doc['p.btn-request']->append($requestUrl);
                }else{
                    $doc['p.request']->remove();
                }
            }
        }
        $searchFilterElement->renderAside($type_id, $total_count, $facet, $doc);

        return $doc->html();
    }

    public function detachedHouse(
        Params $params,
        Settings $settings,
        Datas $datas)
    {
        if (is_null($params->getSpecialPath())) {
            $searchFilter = $datas->getSearchFilter();
        // 特集
        } else {
            $searchFilter = $datas->getFrontSearchFilter();
        }
        $bukkenList = $datas->getBukkenList();
        $total_count = $bukkenList['total_count'];
        if ($searchFilter->checkSearchFiltershumoku(39) && $total_count == 0) {
            return '<div class="tx-nohit" style="padding: 40px 0 60px;
            font-size: 16px;font-weight: bold;text-align: center;line-height: 1.7;
            position: absolute;width: 100%;height: 100%;background-color: white;">申し訳ございません。<br>
            お探しの条件に該当する物件は、現在のところ登録がありません。<br>
            検索条件を変更して、再検索をお願いします。
            </div>';
        }
        return null;
    }
}