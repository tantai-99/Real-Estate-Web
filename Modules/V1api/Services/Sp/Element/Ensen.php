<?php
namespace Modules\V1api\Services\Sp\Element;

class Ensen
{

    /**
     * 沿線選択の要素を作成して返します。 
     *
     * @param $ensenWithGroup 沿線一覧の
	 * @param $ensenCountList
     * @param $type_ct 物件種目のローマ字
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @return 沿線選択のtable要素
     */	
    public function createElement($ensenWithGroup, $ensenCountList, $type_ct, $ken_ct, $isModal)
    { 
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div />");
        
        // 沿線一覧情報の作成
        $locateGroups = $ensenWithGroup['ensens'][0]['ensen_groups'];
        foreach ($locateGroups as $locate)
        {
            $ensen_group_cd = $locate['ensen_group_cd'];
            $ensen_group_nm = $locate['ensen_group_nm'];
            
            // $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            // $sectionElem = pq($sectionTxt);
            // $sectionElem->append('<h4 class="heading-area"/>');
            // $inputElem = "<label>${ensen_group_nm}</label>";
            // $sectionElem['h4']->append($inputElem);
            $sectionElem = pq('<section />');
            $sectionElem->append('<h4 class="heading-select-set"/>');
            $sectionElem['h4']->text($ensen_group_nm);
            
            // $ulElem = pq('<ul>');
            $ulElem = pq('<ul class="list-select-set">');
            // すべての物件を見るを付加。　@TODO いらない模様
            // $allElem = 
            //     '<li class="check-all">
            //         <label>
            //             <span class="checkbox"><input type="checkbox"></span>
            //             <span class="name">すべて</span>
            //         </label>
            //     </li>';
            // $ulElem->append($allElem);
            
            $ensenGroups = $locate['ensens'];
            $countLocate = 0;
            foreach ($ensenGroups as $ensen)
            {
                $ensen_cd  = $ensen['code'];
                $ensen_nm  = $ensen['ensen_nm'];
                $ensenCount  = $ensenCountList[$ensen_cd];
                $countLocate = $countLocate + $ensenCount;
//                 $ensenCount  = $ensen['count'];
//                 $countLocate = $countLocate + $ensen['count'];
                $ensen_roman  = $ensen['ensen_roman'];
                // li要素の新規作成
                $liElem = pq('<li>');
                // if ($isModal) {
                //     $inputText = "<input type='checkbox' name='ensen_ct' value='${ensen_roman}'"
                //         . " id='SG_${ensen_roman}'>";
                //     $liElem->append($inputText);
                //     $labelElem = pq("<label for='SG_${ensen_roman}'>");
                //     if ($ensenCount == '0') {
                //         $liElem['input']->attr('disabled', 'disabled');
                //         $labelElem->addClass('tx-disable')->text($ensen_nm);
                //     } else {
                //         $labelElem->append("${ensen_nm}<span>(${ensenCount})</span>");
                //     }
                //     $liElem->append($labelElem);
                // } else {
                //     $inputText = "<input type='checkbox' name='ensen_ct' value='${ensen_roman}'>";
                //     $liElem->append($inputText);
                //     $spanElem = pq('<span>');
                //     if ($ensenCount == '0') {
                //         $liElem['input']->attr('disabled', 'disabled');
                //         $spanElem->addClass('tx-disable')->text($ensen_nm);
                //     } else {
                //         $ekiUrl = "/${type_ct}/${ken_ct}/${ensen_roman}-line/";
                //         $spanElem->append("<a href='${ekiUrl}'>${ensen_nm}</a>(${ensenCount})");
                //     }
                //     $liElem->append($spanElem);
                // }
                // $ulElem->append($liElem);
                $labelElem = pq('<label>');
                $spanElem = "<span class='checkbox'><input type='checkbox' name='ensen_ct' value='${ensen_roman}'></span>";
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='name'>${ensen_nm}</span>");
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='num'>(${ensenCount})</span>");
                $labelElem->append($spanElem);                    
                $liElem->append($labelElem);

                if ($ensenCount == '0') {
                    $liElem['input']->attr('disabled', 'disabled');
                    $liElem->addClass('tx-disable');
                }
                $ulElem->append($liElem);                
            }
            // 物件がゼロのエリアは表示しない。
            if ($countLocate > 0 || $this->displayAll($locateGroups, $ensenCountList)) {
                $sectionElem->append($ulElem);            
                $searchAreaElem->append($sectionElem);
            } 
        }
        
        return $searchAreaElem;
    }

    private $displayAllLine = null;

    /**
     * すべての沿線に物件がない場合は
     * すべての沿線を表示する
     * 
     * @param $locateGroups
     * @param $ensenCountList
     * @return bool
     */
    private function displayAll($locateGroups, $ensenCountList) {

        if (is_bool($this->displayAllLine)) {
            return $this->displayAllLine;
        }

        foreach ($locateGroups as $locate) {

            $ensenGroups = $locate['ensens'];

            $cnt = 0;
            foreach ($ensenGroups as $ensen) {
                $cnt += $ensenCountList[$ensen['code']];;
            }

            if ($cnt > 0) {
                return $this->displayAllLine = false;
            }
        }

        return $this->displayAllLine = true;
    }
}