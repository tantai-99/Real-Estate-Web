<?php
namespace Modules\V1api\Services\Pc\Element;
class CityMap
{

    /**
     * 市区郡選択の要素を作成して返します。
     *
     * @param $shikugunWithLocateCd 市区郡一覧の検索結果
     * @param $type_ct 物件種目のローマ字
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @param $chosonSearchEnabled 未使用（改修時点の都合で、CityとIF揃えただけ）
     * @return 市区郡選択のtable要素
     */
    public function createElement($shikugunWithLocateCd, $type_ct, $ken_ct, $isModal, $chosonSearchEnabled = false)
    {
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div class='element element-search-area' />");

        // 市区町村一覧情報の作成
        $locateGroups =
            isset($shikugunWithLocateCd['shikuguns']) ?
                 $shikugunWithLocateCd['shikuguns'][0]['locate_groups'] : array();
        foreach ($locateGroups as $locate)
        {
            $locateCd = $locate['locate_cd'];
            $locateName = $locate['locate_nm'];
            $locateRoman = $locate['locate_roman'];
            $seirei_fl = $locate['seirei_fl']; // 政令指定都市判定

            $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            $sectionElem = pq($sectionTxt);
            $sectionElem->append('<h4 class="heading-area"/>');
            $inputElem = "<label>" .
                "${locateName}</label>";
            $sectionElem['h4']->append($inputElem);

            $ulElem = pq('<ul>');
            $shikugunGroups = $locate['shikuguns'];
            $countLocate = 0;
            foreach ($shikugunGroups as $shikugun)
            {
                $shikugunCode  = $shikugun['code'];
                $shikugunName  = $shikugun['shikugun_nm'];
                $shikugunCount  = $shikugun['count'];
                $countLocate = $countLocate + $shikugun['count'];
                $shikugunRoman  = $shikugun['shikugun_roman'];
                // li要素の新規作成
                $liElem = pq('<li>');


                $spanElem = pq('<span>');
                if ($shikugunCount == '0') {
                    $spanElem->addClass('tx-disable')->text($shikugunName . "(".number_format($shikugunCount).")");
                 } else {
                    $cityUrl = "/${type_ct}/${ken_ct}/result/${shikugunRoman}-map.html";
                    $spanElem->append("<a href='${cityUrl}'>${shikugunName}</a>(".number_format($shikugunCount).")");
                }
                $liElem->append($spanElem);

                $ulElem->append($liElem);
            }

            $sectionElem->append($ulElem);
            $searchAreaElem->append($sectionElem);
        }

        return $searchAreaElem;
    }
}