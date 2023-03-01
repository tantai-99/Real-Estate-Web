<?php
namespace Modules\V1api\Services\Sp\Element;

use Modules\V1api\Models;

class Eki
{

    /**
     * 駅選択の要素を作成して返します。 
     *
     * @param $ekiWithEnsen 駅一覧の検索結果
     * @param $type_ct 物件種目のローマ字  (URL生成に使用)　特集は特集パス
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @param $isCrossed
     * @return 駅選択のtable要素
     */	
    public function createElement($ekiWithEnsen, $type_ct, $ken_ct, $isModal, $ekiSettingOfKen)
    { 
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div />");
        if (is_null($ekiWithEnsen)) return $searchAreaElem;

        // 駅一覧情報の作成
        $locateGroups = $ekiWithEnsen['ensens'];
        foreach ($locateGroups as $locate)
        {
            $ensenCd = $locate['code'];
            $ensenName = $locate['ensen_nm'];
            $ensenRoman = $locate['ensen_roman'];
            
            // $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            // $sectionElem = pq($sectionTxt);
            // $sectionElem->append('<h4 class="heading-area"/>');
            // $inputElem = "<label for='LC${ensenCd}'>" .
            //     "<input type='checkbox' name='ensen_ct' value='${ensenRoman}' id='LC${ensenCd}'/>" .
            //     "${ensenName}</label>";
            // $sectionElem['h4']->append($inputElem);
            $sectionElem = pq("<dt>${ensenName}</dt>");
            $sectionElem->append('<dd />');
            
            // $ulElem = pq('<ul>');
            $ulElem = pq('<ul class="list-select-set">');
            // すべての物件を見るを付加。
            $allElem = 
                '<li class="check-all">
                    <label>
                        <span class="checkbox"><input type="checkbox" name="ensen_ct" value="' . $ensenRoman . '"></span>
                        <span class="name">すべて</span>
                    </label>
                </li>';
            $ulElem->append($allElem);

            $ekiGroups = $locate['ekis'];
            $countLocate = 0;
            foreach ($ekiGroups as $eki)
            {
                $ekiCode  = $eki['code'];
                $ekiName  = $eki['eki_nm'];
                $ekiCount  = $eki['count'];
                // 選択県の駅かどうかの判断
                $isTargetKenEki = in_array($ekiCode, $ekiSettingOfKen);
                $countLocate = $countLocate + $eki['count'];
                $ekiRoman  = $eki['eki_roman'];
                // li要素の新規作成
                $liElem = pq('<li>');

                if ($isTargetKenEki) {
                    $liElem['li']->addClass('select-area');
                } else {
                    $liElem['li']->addClass('another-area');
                }
                // if ($isModal) {
                //     $inputText = "<input type='checkbox' name='eki_ct' value='${ekiRoman}'"
                //         . " id='SG_${ekiRoman}'>";
                //     $liElem->append($inputText);
                //     $labelElem = pq("<label for='SG_${ekiRoman}'>");
                //     if ($ekiCount == '0') {
                //         $liElem['input']->attr('disabled', 'disabled');
                //         $labelElem->addClass('tx-disable')->text($ekiName);
                //      } else {
                //         $labelElem->append("${ekiName}<span>(${ekiCount})</span>");
                //     }
                //     $liElem->append($labelElem);
                // } else {
                //     $inputText = "<input type='checkbox' name='eki_ct' value='${ekiRoman}'>";
                //     $liElem->append($inputText);
                //     $spanElem = pq('<span>');
                //     if ($ekiCount == '0') {
                //         $liElem['input']->attr('disabled', 'disabled');
                //         $spanElem->addClass('tx-disable')->text($ekiName);
                //      } else {
                //         $ekiUrl = "/${type_ct}/${ken_ct}/result/${ekiRoman}-eki.html";
                //         $spanElem->append("<a href='${ekiUrl}'>${ekiName}</a>(${ekiCount})");
                //     }
                //     $liElem->append($spanElem);
                // }
                // $ulElem->append($liElem);
                
                // 沿線ローマ字と駅ローマ字の結合
                $ekiObj = Models\EnsenEki::getObjByPair($ensenRoman, $ekiRoman);
                $ekiRoman  = $ekiObj->getEnsenEkiCt();
                
                $labelElem = pq('<label>');
                $spanElem = "<span class='checkbox'><input type='checkbox' name='eki_ct' value='${ekiRoman}'></span>";
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='name'>${ekiName}</span>");
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='num'>(${ekiCount})</span>");
                $labelElem->append($spanElem);                    
                $liElem->append($labelElem);
                
                if ($ekiCount == '0') {
                    $liElem['input']->attr('disabled', 'disabled');
                    $liElem->addClass('tx-disable');
                }
                $ulElem->append($liElem);                
            }
            // 物件がゼロのエリアは表示しない。
            if ($countLocate != 0) {
                $sectionElem['dd']->append($ulElem);            
                $searchAreaElem->append($sectionElem);
            } 
        }
        
        return $searchAreaElem;
    }
}