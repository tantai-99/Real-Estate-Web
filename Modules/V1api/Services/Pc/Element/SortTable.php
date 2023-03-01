<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Library\Custom\Model\Estate;
use phpQuery;
use Library\Custom\Registry;

class SortTable
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;
    
    private $isPicBukken;

    public function __construct()
    {
        
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    public function createElement($shumoku, $sort, $isPicBukken)
    {
    	$this->isPicBukken = $isPicBukken;
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/sorttable.tpl";
        $html = file_get_contents($template_file);
        $doc = phpQuery::newDocument($html);

        if (is_array($shumoku) && count($shumoku) === 1) {
            $shumoku = $shumoku[0];
        }

        if (!is_array($shumoku)) {
            // 単一種目
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

        } else {
            // 複合種目
            $compositeType = Estate\TypeList::getInstance()->getComopsiteTypeByShumokuCd($shumoku);
            switch ($compositeType) {
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_2:
                    // 貸店舗と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_TENPO);
                    $sortElem = $doc["div." . $shumokuCt];
                    $this->createKasiTenpoOffice($sortElem, $sort);

                    // 物件種目ソート
                    $cell = $sortElem['th.cell7'];
                    $link = $cell['span'];
                    $cell->empty();
                    $cell->append($this->createShumokuSortLink($sort));
                    $cell->append('<br/>');
                    $cell->append($link);
                    break;
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3:
                    // 貸その他と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_OTHER);
                    $sortElem = $doc["div." . $shumokuCt];
                    $this->createKasiOther($sortElem, $sort);

                    // 物件種目ソート
                    $cell = $sortElem['th.cell7'];
                    $link = $cell['span'];
                    $cell->empty();
                    $cell->append($this->createShumokuSortLink($sort));
                    $cell->append('<br/>');
                    $cell->append($link);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2:
                $sortElem = $doc["div.baibai-kyoju"];
                    $this->createBaibaiKyoju($sortElem, $sort);

                    // 物件種目ソート
                    $cell = $sortElem['th.cell7'];
                    $link = $cell['span'];
                    $cell->empty();
                    $cell->append($this->createShumokuSortLink($sort));
                    $cell->append('<br/>');
                    $cell->append($link);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2:
                    // 売り店舗と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_URI_TENPO);
                    $sortElem = $doc["div." . $shumokuCt];
                    $this->createUriTenpoOffice($sortElem, $sort);

                    // 物件種目ソート
                    $cell = $sortElem['th.cell7'];
                    $link = $cell['span'];
                    $cell->empty();
                    $cell->append($this->createShumokuSortLink($sort));
                    $cell->append('<br/>');
                    $cell->append($link);
                    break;
            }

        }

        return $sortElem;
    }

    private function createChintai($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
            $cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);

        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }

            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 間取り・面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_MADORI_INDEX);
        if ($sort == Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX_DESC);
        }
        if ($sort == Params::SORT_MADORI_INDEX_DESC)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX);
        }
        
        $sortTableElem['th.cell6 span:last']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }

    private function createKasiTenpoOffice($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC)
        {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
        	$sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
        	$sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
        	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
            // 間取り・面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_MADORI_INDEX);
        if ($sort == Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX_DESC);
        }
        if ($sort == Params::SORT_MADORI_INDEX_DESC)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX);
        }
        
        $sortTableElem['th.cell6 span:last']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }
    
    private function createParking($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>図面';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">図面</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
    }
    
    private function createKasiTochi($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>地形図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">地形図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 土地面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('descend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
    }
    
    private function createKasiOther($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
            //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 間取り・面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('descend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }
    
    /**
     * 売買：マンション
     */
    private function createMansion($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 間取り・面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_MADORI_INDEX);
        if ($sort == Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX_DESC);
        }
        if ($sort == Params::SORT_MADORI_INDEX_DESC)
        {
        	$sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX);
        }
        
        $sortTableElem['th.cell6 span:last']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell6 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }
    
    private function createKodate($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 間取り・建物面積・土地面積
        $sortTableElem['th.cell5 span:first']->attr('data-value', Params::SORT_MADORI_INDEX);
        if ($sort == Params::SORT_MADORI_INDEX)
        {
        	$sortTableElem['th.cell5 span:first']->removeClass('descend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_MADORI_INDEX_DESC);
        }
        if ($sort == Params::SORT_MADORI_INDEX_DESC)
        {
        	$sortTableElem['th.cell5 span:first']->removeClass('descend')->addClass('descend active')
        	->attr('data-value', Params::SORT_MADORI_INDEX);
        }
        
        $sortTableElem['th.cell5 span:eq(1)']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
        	$sortTableElem['th.cell5 span:eq(1)']->removeClass('ascend')->addClass('descend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
        	$sortTableElem['th.cell5 span:eq(1)']->removeClass('ascend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }

        $sortTableElem['th.cell5 span:eq(2)']->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        if ($sort == Params::SORT_TOCHI_MS_DESC)
        {
        	$sortTableElem['th.cell5 span:eq(2)']->removeClass('ascend')->addClass('descend active')
        	->attr('data-value', Params::SORT_TOCHI_MS);
        }
        if ($sort == Params::SORT_TOCHI_MS)
        {
        	$sortTableElem['th.cell5 span:eq(2)']->removeClass('ascend')->addClass('ascend active')
        	->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }
    
    private function createUriTochi($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>地形図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">地形図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 面積
            $sortTableElem['th.cell5 span:last']->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        if ($sort == Params::SORT_TOCHI_MS_DESC)
        {
            $sortTableElem['th.cell5 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_TOCHI_MS);
        }
        if ($sort == Params::SORT_TOCHI_MS)
        {
            $sortTableElem['th.cell5 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        }
    }
    
    private function createUriTenpoOffice($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 面積
        $sortTableElem['th.cell5 span:last']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell5 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell5 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:last']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:last']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:last']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }
    
    
    private function createUriOther($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');
        
        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
        	$cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);
        
        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_EKI_KYORI);
        }
        
            // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }
        
        // 面積
        $sortTableElem['th.cell5 span:first']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell5 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell5 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }
        
        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }

    private function createBaibaiKyoju($sortElem, $sort)
    {
        $sortTableElem = $sortElem['table'];
        $sortTableElem['span']->removeClass('active');

        // 物件写真、間取り図の切り替え
        // 1:物件写真　2:間取り図
        $cell1Elem = '<span><a href="#" class="picture" data-id="1">物件写真</a></span> /<br>間取図';
        if ($this->isPicBukken) {
            $cell1Elem = '物件写真 /<br><span><a href="#" class="floor-plan" data-id="1">間取図</a></span>';
        }
        $sortElem['th.cell1']->text('')->append($cell1Elem);

        //　交通・所在地
        $sortTableElem['th.cell2 span:first']->attr('data-value', Params::SORT_ENSEN_EKI);
        if ($sort == Params::SORT_ENSEN_EKI) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_ENSEN_EKI_DESC) {
            $sortTableElem['th.cell2 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_ENSEN_EKI);
        }

        $sortTableElem['th.cell2 span:last']->attr('data-value', Params::SORT_SHOZAICHI);
        if ($sort == Params::SORT_SHOZAICHI) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_SHOZAICHI_DESC) {
            $sortTableElem['th.cell2 span:last']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SHOZAICHI);
        }

        //　駅徒歩
        $sortTableElem['th.cell3 span:first']->attr('data-value', Params::SORT_EKI_KYORI);
        if ($sort == Params::SORT_EKI_KYORI)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_EKI_KYORI_DESC);
        }
        if ($sort == Params::SORT_EKI_KYORI_DESC)
        {
            $sortTableElem['th.cell3 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_EKI_KYORI);
        }

        // 賃料
        $sortTableElem['th.cell4 span:first']->attr('data-value', Params::SORT_KAKAKU);
        if ($sort == Params::SORT_KAKAKU)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('ascend active')
                ->attr('data-value', Params::SORT_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_KAKAKU_DESC)
        {
            $sortTableElem['th.cell4 span:first']
                ->removeClass('ascend')
                ->addClass('descend active')
                ->attr('data-value', Params::SORT_KAKAKU);
        }

        // 間取り・建物面積・土地面積
        $sortTableElem['th.cell6 span:first']->attr('data-value', Params::SORT_MADORI_INDEX);
        if ($sort == Params::SORT_MADORI_INDEX)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX_DESC);
        }
        if ($sort == Params::SORT_MADORI_INDEX_DESC)
        {
            $sortTableElem['th.cell6 span:first']->removeClass('descend')->addClass('descend active')
                ->attr('data-value', Params::SORT_MADORI_INDEX);
        }

        $sortTableElem['th.cell6 span:eq(1)']->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        if ($sort == Params::SORT_SENYUMENSEKI_DESC)
        {
            $sortTableElem['th.cell6 span:eq(1)']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI);
        }
        if ($sort == Params::SORT_SENYUMENSEKI)
        {
            $sortTableElem['th.cell6 span:eq(1)']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SENYUMENSEKI_DESC);
        }

        $sortTableElem['th.cell6 span:eq(2)']->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        if ($sort == Params::SORT_TOCHI_MS_DESC)
        {
            $sortTableElem['th.cell6 span:eq(2)']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_TOCHI_MS);
        }
        if ($sort == Params::SORT_TOCHI_MS)
        {
            $sortTableElem['th.cell6 span:eq(2)']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_TOCHI_MS_DESC);
        }

        // 物件種目・築年月
        // if ($sort == Params::SORT_SHUMOKU)
        // {
        //     $sortTableElem['th.cell7 span:first']->removeClass('descend')->addClass('ascend active');
        // }
        // $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_SHUMOKU);

        $sortTableElem['th.cell7 span:first']->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        if ($sort == Params::SORT_CHIKUNENGETSU_DESC)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU);
        }
        if ($sort == Params::SORT_CHIKUNENGETSU)
        {
            $sortTableElem['th.cell7 span:first']->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_CHIKUNENGETSU_DESC);
        }
    }

    private function createShumokuSortLink($sort) {
        $shumokuLink = pq('<span class="ascend"><a href="#">物件種目</a></span>')
            ->attr('data-value', Params::SORT_SHUMOKU);
        if ($sort == Params::SORT_SHUMOKU_DESC)
        {
            $shumokuLink->removeClass('ascend')->addClass('descend active')
                ->attr('data-value', Params::SORT_SHUMOKU);
        }
        if ($sort == Params::SORT_SHUMOKU)
        {
            $shumokuLink->removeClass('ascend')->addClass('ascend active')
                ->attr('data-value', Params::SORT_SHUMOKU_DESC);
        }
        return $shumokuLink;
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

}