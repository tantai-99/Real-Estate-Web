<?php
namespace Modules\V1api\Services\Pc\Element;
class City
{

    /**
     * 市区郡選択の要素を作成して返します。 
     *
     * @param $shikugunWithLocateCd 市区郡一覧の検索結果
     * @param $type_ct 物件種目のローマ字
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @param $chosonSearchEnabled 町村検索
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
            $inputElem = "<label for='LC${locateCd}'>" .
                "<input type='checkbox' name='locate_ct' value='${locateRoman}' id='LC${locateCd}'/>" .
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
                if ($isModal) {
                    $inputText = "<input type='checkbox' name='shikugun_ct' value='${shikugunRoman}'"
                        . " id='SG_${shikugunRoman}'>";
                    $liElem->append($inputText);
                    $labelElem = pq("<label for='SG_${shikugunRoman}'>");
                    if ($shikugunCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $labelElem->addClass('tx-disable')->append($shikugunName . "(".number_format($shikugunCount).")");
                     } else {
                        $labelElem->append("${shikugunName}(".number_format($shikugunCount).")");
                    }
                    $liElem->append($labelElem);
                } else {
                    $inputText = "<input type='checkbox' name='shikugun_ct' value='${shikugunRoman}'>";
                    $liElem->append($inputText);
                    $spanElem = pq('<span>');
                    if ($shikugunCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $spanElem->addClass('tx-disable')->text($shikugunName . "(".number_format($shikugunCount).")");
                     } else {
                        $cityUrl = "/${type_ct}/${ken_ct}/result/${shikugunRoman}-city.html";
                        $spanElem->append("<a href='${cityUrl}'>${shikugunName}</a>(".number_format($shikugunCount).")");
                    }
                    $liElem->append($spanElem);
                }
                $ulElem->append($liElem);
            }

            $sectionElem->append($ulElem);
            // 町名で絞り込むボタン
            if ($chosonSearchEnabled) {
$chosonSearchBtn = <<< EOT
<div class="element-search-area-item-btn-filter">
  <div class="element-search-area-item-btn-filter-inner">
    <input type="submit" value="町名で絞り込む" class="element-search-area-item-btn-filter-btn">
    <div class="element-search-area-item-btn-filter-note">
      <p>1つ以上市区郡を選択すると<br>町名で絞り込めます。<br>※5つまで選択できます。</p>
    </div>
  </div>
</div>
EOT;
                $sectionElem->append($chosonSearchBtn);
            }

            $searchAreaElem->append($sectionElem);

            // 政令指定都市の場合のみ、すべての物件を見るのリンクを付加。
            if ($countLocate != 0 && ! $isModal && $seirei_fl) {
                $mcityUrl = "/${type_ct}/${ken_ct}/result/${locateRoman}-mcity.html";
                $locateLinkElem = "<p class='link-all-result'><a href='${mcityUrl}'>${locateName}すべての物件を見る</a></p>";
                $sectionElem->append($locateLinkElem);
            }
        }
        
        return $searchAreaElem;
    }
}