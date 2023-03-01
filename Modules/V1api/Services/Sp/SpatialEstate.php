<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

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
            return '<div class="tx-nohit" style="margin: 0 0 30px;padding: 12px 0 0;font-size: 15px;font-weight: 700;text-align: center;">申し訳ございません。<br>
            お探しの条件に該当する物件は、現在のところ登録がありません。<br>
            検索条件を変更して、再検索をお願いします。
            </div>';
        }
        return null;
    }
}