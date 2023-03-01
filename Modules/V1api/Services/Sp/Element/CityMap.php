<?php
namespace Modules\V1api\Services\Sp\Element;

class CityMap
{

    /**
     * 市区郡選択の要素を作成して返します。
     *
     * @param $shikugunWithLocateCd 市区郡一覧の検索結果
     * @param $type_ct 物件種目のローマ字
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @return 市区郡選択のtable要素
     */
    public function createElement($shikugunWithLocateCd, $type_ct, $ken_ct, $isModal)
    {
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div />");

        // 市区町村一覧情報の作成
        $locateGroups = $shikugunWithLocateCd['shikuguns'][0]['locate_groups'];
        foreach ($locateGroups as $locate)
        {
            $locateCd = $locate['locate_cd'];
            $locateName = $locate['locate_nm'];
            $locateRoman = $locate['locate_roman'];
            $seirei_fl = $locate['seirei_fl']; // 政令指定都市判定

            $sectionElem = pq('<section />');
            $sectionElem->append('<h4 class="heading-select-set"/>');
            $sectionElem['h4']->text($locateName);

            $ulElem = pq('<ul class="list-select-set">');
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

                $cityUrl = "/${type_ct}/${ken_ct}/result/${shikugunRoman}-map.html";
                $linkElem = pq("<a href='${cityUrl}'>");

                $labelElem = pq('<label>');
                $spanElem = "<span class='checkbox'></span>";
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='name'>${shikugunName}</span>");
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='num'>(${shikugunCount})</span>");
                $labelElem->append($spanElem);

                if ($shikugunCount == '0') {
                    $liElem['input']->attr('disabled', 'disabled');
                    $liElem->addClass('tx-disable');

                    $liElem->append($labelElem);
                } else {
                    $linkElem->append($labelElem);
                    $liElem->append($linkElem);
                }
                $ulElem->append($liElem);
            }
            // 物件がゼロのエリアは表示しない。
            if ($countLocate > 0 || $this->displayAllShikugun($locateGroups)) {
                $sectionElem->append($ulElem);
                $searchAreaElem->append($sectionElem);
            }
        }

        return $searchAreaElem;
    }

    private $displayAllShikugun = null;

    /**
     * すべての市区郡を強制的に表示するかのフラグ
     * - 全体で物件が1件をなかった場合に表示
     *
     * @param $locateGroups
     * @return bool
     */
    private function displayAllShikugun($locateGroups) {

        if (is_bool($this->displayAllShikugun)) {
            return $this->displayAllShikugun;
        }

        foreach ($locateGroups as $locate) {

            $cnt = 0;
            foreach ($locate['shikuguns'] as $shikugun) {
                $cnt += $shikugun['count'];
            }

            if ($cnt > 0) {
                $this->displayAllShikugun = false;
                return $this->displayAllShikugun;
            }
        }
        $this->displayAllShikugun = true;
        return $this->displayAllShikugun;
    }
}