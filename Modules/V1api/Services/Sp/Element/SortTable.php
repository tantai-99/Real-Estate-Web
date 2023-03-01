<?php
namespace Modules\V1api\Services\Sp\Element;

use Modules\V1api\Services;
use Modules\V1api\Models;

class SortTable
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;

    public function __construct()
    {
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    public function createElement($shumoku, $sort, $isPicBukken)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/sorttable.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        $shumokuCt = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $sortElem = $doc["div." . $shumokuCt];

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $this->createChintai($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_TENPO);
                $sortElem = $doc["div." . $shumokuCt];
                $this->createKasiTenpoOffice($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $this->createParking($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
                $this->createKasiTochi($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $this->createKasiOther($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $this->createMansion($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $this->createKodate($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $this->createUriTochi($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
                $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_URI_TENPO);
                $sortElem = $doc["div." . $shumokuCt];
                $this->createUriTenpoOffice($sortElem, $sort);
                break;
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $this->createUriOther($sortElem, $sort);
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
        }
        
        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture">物件写真</a></span> /<br>間取図';
        if ($isPicBukken) {
            $cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);

        return $sortElem;
    }

    private function createChintai($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 間取り・面積
        if ($sort == Models\Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_MADORI_INDEX);

        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->addClass('active');
        }
        $sortTableElem['th.cell6 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }

    private function createKasiTenpoOffice($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 間取り・面積
        if ($sort == Models\Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_MADORI_INDEX);

        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->addClass('active');
        }
        $sortTableElem['th.cell6 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }
    
    private function createParking($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }
    }
    
    private function createKasiTochi($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 土地面積
        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:first']->addClass('active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);
    }
    
    private function createKasiOther($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 間取り・面積
        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:first']->addClass('active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }
    
    /**
     * 売買：マンション
     */
    private function createMansion($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 間取り・面積
        if ($sort == Models\Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_MADORI_INDEX);

        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->addClass('active');
        }
        $sortTableElem['th.cell6 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }
    
    private function createKodate($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 間取り・建物面積・土地面積
        if ($sort == Models\Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell6 span:first']->attr('data-value', Models\Params::SORT_MADORI_INDEX);

        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:eq(1)']->addClass('active');
        }
        $sortTableElem['th.cell6 span:eq(1)']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        if ($sort == Models\Params::SORT_TOCHI_MS_DESC)
        {
            $sortTableElem['th.cell6 span:eq(2)']->addClass('active');
        }
        $sortTableElem['th.cell6 span:eq(2)']->attr('data-value', Models\Params::SORT_TOCHI_MS_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }
    
    private function createUriTochi($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 面積
        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->addClass('active');
        }
        $sortTableElem['th.cell6 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);
    }
    
    private function createUriTenpoOffice($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 面積
        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->addClass('active');
        }
        $sortTableElem['th.cell6 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }
    
    
    private function createUriOther($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        //　交通・所在地
        if ($sort == Models\Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:first']->attr('data-value', Models\Params::SORT_ENSEN_EKI);

        if ($sort == Models\Params::SORT_SHOZAICHI)
        {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell2 span:last']->attr('data-value', Models\Params::SORT_SHOZAICHI);

        //　駅徒歩
        if ($sort == Models\Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active');
        }
        $sortTableElem['th.cell3 span:first']->attr('data-value', Models\Params::SORT_EKI_KYORI);

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        if ($sort == Models\Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('descend')
                ->addClass('ascend active')
                ->attr('data-value', Models\Params::SORT_KAKAKU_DESC);
        }

        if ($sort == Models\Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->addClass('active')
                ->attr('data-value', Models\Params::SORT_KAKAKU);
        }

        // 面積
        if ($sort == Models\Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell5 span:last']->addClass('active');
        }
        $sortTableElem['th.cell5 span:last']->attr('data-value', Models\Params::SORT_SENYUMENSEKI_DESC);

        // 物件種目・築年月
        // if ($sort == Models\Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_SHUMOKU);

        if ($sort == Models\Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->addClass('active');
        }
        $sortTableElem['th.cell7 span:first']->attr('data-value', Models\Params::SORT_CHIKUNENGETSU_DESC);
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

}