<?php

namespace Modules\V1api\Services\Sp\Element;

use Library\Custom\Model\Estate;

class Pref
{

    /**
     * 都道府県選択の要素を作成して返します。 
     *
     * @param 都道府県コードの配列
     * @param 物件種目のローマ字
     * @param 都道府県のローマ字
     * @return 都道府県選択のtable要素
     */
    public function createElement($prefs, $type_ct, $ken_nm = null)
    {
        // エリアを取得して配列を作る
        // { area_name => [url_name => ken_name, url_name =>ken_name] }
        $areaPref  = [];
        $prefModel = Estate\PrefCodeList::getInstance();
        $areaModel = Estate\AreaCategoryList::getInstance();
        if ($prefs == '') {
            throw new \Exception('対象の都道府県コードの配列設定がありません。', 404);
        }
        foreach ($prefs as $prefCode) {
            $prefName = $prefModel->get($prefCode);
            $prefUrl  = $prefModel->getUrl($prefCode);
            $areaCode  = $prefModel->getArea($prefCode);
            $areaPref[$areaCode][] = [$prefUrl, $prefName];
        }

        $doc = pq("<dl class='element-search-toggle js-search-toggle'>");
        foreach ($areaPref as $areaCode => $prefList) {
            $ulElem = pq('<ul>');
            foreach ($prefList as $prefInfo) {
                $prefUrl  = $prefInfo[0];
                $prefName = $prefInfo[1];
                $classAttr = $ken_nm && $ken_nm === $prefName ? 'selected' : '';
                $liElem = "<li><a href='/${type_ct}/${prefUrl}/' data-name='${prefUrl}' class='${classAttr}'>${prefName}<p style='float:right;padding-right:10px;'>&gt;&gt;<p style='clear:both;'></p></a></li>";
                $ulElem->append($liElem);
            }

            $areaName  = $areaModel->get($areaCode);
            $dtElem = pq("<dt>${areaName}</dt>");
            $doc->append($dtElem);
            $ddElem = pq("<dd/>");
            $ddElem->append($ulElem);
            $doc->append($ddElem);
        }
        return $doc;
    }
}
