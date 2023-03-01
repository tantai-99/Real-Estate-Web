<?php
namespace Modules\V1api\Services\Pc\Element;
use Library\Custom\Model\Estate;

class PrefMap
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
        foreach ($prefs as $prefCode)
        {
            $prefName = $prefModel->get($prefCode);
            $prefUrl  = $prefModel->getUrl($prefCode);
            $areaCode  = $prefModel->getArea($prefCode);
            $areaPref[$areaCode][] = [$prefUrl, $prefName];
        }

    	$doc = pq("<table class='element-search-table'>");
        foreach ($areaPref as $areaCode => $prefList)
        {
            $ulElem = pq('<ul>');
            foreach ($prefList as $prefInfo)
            {
                $prefUrl  = $prefInfo[0];
                $prefName = $prefInfo[1];
                $prefNameWithSuffix = $prefModel->getNameByUrl($prefUrl,true);

                $classAttr = $ken_nm && $ken_nm === $prefNameWithSuffix ? 'selected' : '';
                $liElem = "<li><a href='/${type_ct}/${prefUrl}/map.html' data-name='${prefUrl}' class='${classAttr}'>${prefName}</a></li>";
                $ulElem->append($liElem);
            }

            $areaName  = $areaModel->get($areaCode);
            $trElem = pq("<tr><th>${areaName}</th><td></td></tr>");
	        $trElem["td"]->append($ulElem);
            $doc->append($trElem);
        }
        return $doc;
    }
}