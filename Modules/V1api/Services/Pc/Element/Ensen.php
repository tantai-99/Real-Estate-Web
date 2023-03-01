<?php
namespace Modules\V1api\Services\Pc\Element;

class Ensen
{

    /**
     * 沿線選択の要素を作成して返します。 
     *
     * @param $ensenWithGroup 沿線一覧の表示情報
     * @param $ensenCountList 沿線ごとの物件数リスト
     * @param $type_ct 物件種目のローマ字
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @return 沿線選択のtable要素
     */	
    public function createElement($ensenWithGroup, $ensenCountList, $type_ct, $ken_ct, $isModal)
    { 
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div class='element element-search-area' />");

        if(is_null($ensenWithGroup)) return $searchAreaElem;
        // 沿線一覧情報の作成
        $locateGroups = $ensenWithGroup['ensens'][0]['ensen_groups'];

        foreach ($locateGroups as $locate)
        {
            $ensen_group_cd = $locate['ensen_group_cd'];
            $ensen_group_nm = $locate['ensen_group_nm'];
            
            $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            $sectionElem = pq($sectionTxt);
            $sectionElem->append('<h4 class="heading-area"/>');
            $inputElem = "<label>${ensen_group_nm}</label>";
            $sectionElem['h4']->append($inputElem);
                        
            $ulElem = pq('<ul>');
            $ensenGroups = $locate['ensens'];
            $countLocate = 0;
            foreach ($ensenGroups as $ensen)
            {
                $ensen_cd  = $ensen['code'];
                $ensen_nm  = $ensen['ensen_nm'];
                $ensenCount  = is_null($ensenCountList) ? 0 : $ensenCountList[$ensen_cd];
                $countLocate = $countLocate + $ensenCount;
                $ensen_roman  = $ensen['ensen_roman'];
                // li要素の新規作成
                $liElem = pq('<li>');
                if ($isModal) {
                    $inputText = "<input type='checkbox' name='ensen_ct' value='${ensen_roman}'"
                        . " id='SG_${ensen_roman}'>";
                    $liElem->append($inputText);
                    $labelElem = pq("<label for='SG_${ensen_roman}'>");
                    if ($ensenCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $labelElem->addClass('tx-disable')->append("${ensen_nm}<span>(".number_format($ensenCount).")</span>");
                    } else {
                        $labelElem->append("${ensen_nm}<span>(".number_format($ensenCount).")</span>");
                    }
                    $liElem->append($labelElem);
                } else {
                    $inputText = "<input type='checkbox' name='ensen_ct' value='${ensen_roman}'>";
                    $liElem->append($inputText);
                    $spanElem = pq('<span>');
                    if ($ensenCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $spanElem->addClass('tx-disable')->text($ensen_nm . "(".number_format($ensenCount).")");
                    } else {
                        $ekiUrl = "/${type_ct}/${ken_ct}/${ensen_roman}-line/";
                        $spanElem->append("<a href='${ekiUrl}'>${ensen_nm}</a>(".number_format($ensenCount).")");
                    }
                    $liElem->append($spanElem);
                }
                $ulElem->append($liElem);
            }
            $sectionElem->append($ulElem);            
            $searchAreaElem->append($sectionElem);
        }
        
        return $searchAreaElem;
    }
}