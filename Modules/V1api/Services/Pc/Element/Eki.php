<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Models;

class Eki
{
    protected $logger;
    protected $_config;
    
    public function __construct() {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);
        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

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
        $searchAreaElem = pq("<div class='element element-search-area' />");
        if (is_null($ekiWithEnsen)) return $searchAreaElem;

        // 駅一覧情報の作成
        $locateGroups = $ekiWithEnsen['ensens'];
        foreach ($locateGroups as $locate)
        {
            $ensenCd = $locate['code'];
            $ensenName = $locate['ensen_nm'];
            $ensenRoman = $locate['ensen_roman'];
            
            $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            $sectionElem = pq($sectionTxt);
            $sectionElem->append('<h4 class="heading-area"/>');
            $inputElem = "<label for='LC${ensenCd}'>" .
                "<input type='checkbox' name='ensen_ct' value='${ensenRoman}' id='LC${ensenCd}'/>" .
                "${ensenName}</label>";
            $sectionElem['h4']->append($inputElem);
            
            $ulElem = pq('<ul>');
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
                // 沿線ローマ字と駅ローマ字の結合
                $ekiObj = Models\EnsenEki::getObjByPair($ensenRoman, $eki['eki_roman']);
                $ekiRoman  = $ekiObj->getEnsenEkiCt();
                // li要素の新規作成
                $liElem = pq('<li>');
                if ($isModal) {
                    $inputText = "<input type='checkbox' name='eki_ct' value='${ekiRoman}'"
                        . " id='SG_${ekiRoman}'>";
                    $liElem->append($inputText);
                    $labelElem = pq("<label for='SG_${ekiRoman}'>");
                    if ($ekiCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $labelElem->addClass('tx-disable')->append("${ekiName}<span>(".number_format($ekiCount).")</span>");
                    } else {
                        $labelElem->append("${ekiName}<span>(".number_format($ekiCount).")</span>");
                    }
                    $liElem->append($labelElem);
				} else {
                    $inputText = "<input type='checkbox' name='eki_ct' value='${ekiRoman}'>";
                    $liElem->append($inputText);
                    $spanElem = pq('<span>');
                    if ($ekiCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $spanElem->addClass('tx-disable')->text($ekiName . "(".number_format($ekiCount).")");
                     } else {
                        $ekiUrl = "/${type_ct}/${ken_ct}/result/${ekiRoman}-eki.html";
                        $spanElem->append("<a href='${ekiUrl}'>${ekiName}</a>(".number_format($ekiCount).")");
                    }
                    $liElem->append($spanElem);
				}


                $liElem->addClass(($isTargetKenEki ? 'select-area' : 'another-area'));
                $ulElem->append($liElem);

            }
            
            // すべての物件を見るのリンクを付加。
            if ($countLocate != 0) {
                $lineUrl = "/${type_ct}/${ken_ct}/result/${ensenRoman}-line.html";
                $locateLinkElem = "<p class='link-all-result'><a href='${lineUrl}'>${ensenName}すべての物件を見る</a></p>";
                $sectionElem->append($locateLinkElem);
            }

            $sectionElem->append($ulElem);            
            $searchAreaElem->append($sectionElem);
        }
        
        return $searchAreaElem;
    }
}