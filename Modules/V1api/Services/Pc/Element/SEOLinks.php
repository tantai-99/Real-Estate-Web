<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;

class SEOLinks
{

	public function shumoku($doc,
        Params $params,
        Settings $settings,
		Datas $datas)
	{
		$specialSettings = $settings->special;
        $rentSpecialElem = $doc['.element-search-from:eq(0)'];
        $purchaseSpecialElem = $doc['.element-search-from:eq(1)'];

        // 賃貸の特集を取得
        $specials = $datas->getSeoSpecialsRent();
        if ($specials) {
        	$ul = $rentSpecialElem['ul'];
        	$ul->empty();
        	foreach ($specials as $row) {
        		$li = pq('<li><a></a></li>');
        		$li['a']->text($row->title)
        			->attr('href', "/{$row->filename}/")
        			->attr('target', '_blank');
        		$ul->append($li);
        	}
        }
        else {
        	$rentSpecialElem->remove();
        }

        // 売買の特集を取得
        $specials = $datas->getSeoSpecialsPurchase();
        if ($specials) {
        	$ul = $purchaseSpecialElem['ul'];
        	$ul->empty();
        	foreach ($specials as $row) {
        		$li = pq('<li><a></a></li>');
        		$li['a']->text($row->title)
        			->attr('href', "/{$row->filename}/")
        			->attr('target', '_blank');
        		$ul->append($li);
        	}
        }
        else {
        	$rentSpecialElem->removeClass('element-line');
        	$purchaseSpecialElem->remove();
        }
    }

	public function rent($doc,
        Params $params,
        Settings $settings,
		Datas $datas)
	{
		$this->createElement(
			$doc, $params, $settings, $datas);
	}

	public function purchase($doc,
        Params $params,
        Settings $settings,
		Datas $datas)
	{
		$this->createElement(
			$doc, $params, $settings, $datas);
	}

    const DISP_PREF = 1;
    const DISP_CITY = 2;
    const DISP_LINE = 3;
    const DISP_EKI = 4;
	const DISP_MAP = 5;
	const DISP_CHOSON = 6;

	public function searchConditions($doc,
        Params $params,
        Settings $settings,
		Datas $datas,
			$dispType)
	{
        /*
         * お薦め物件コマと、特集から探すのリンク
         */
		$sp_cnt = $this->createElement(
			$doc, $params, $settings, $datas);

        // 特集から探すのリンクは、上のメソッドで作成
        $otherShumokuElem = $doc['.element-search-from:last'];

        $type_ct = $params->getTypeCt();
        // 他の物件種目
        $typeList = Estate\TypeList::getInstance();
        $otherShumoks = $settings->search->getShumokuWithoutTypeCt($type_ct);

        // 都道府県の取得
        $pNames = $datas->getParamNames();
        $ken_cd  = $pNames->getKenCd();
        $searchCond = $settings->search;

        if ($otherShumoks && ! is_array($params->getEnsenCt())) {
        	$ul = $otherShumokuElem['ul'];
        	$ul->empty();
            $cnt = 0;
        	foreach ($otherShumoks as $type) {

                //都道府県が存在しない場合は作成しない
                $pref_codes = $settings->company->getSearchSettingRowset()->getPref($type);
                if($ken_cd != null && !in_array($ken_cd, $pref_codes)) continue;

                // 同じ検索方法を設定していない種目は外す
                $loop_type_cd = Estate\TypeList::getInstance()->getShumokuCode($type);
                $loop_type_ct = Services\ServiceUtils::getShumokuCtByCd($loop_type_cd);
                if ($dispType == static::DISP_CITY) {
                    if (!$searchCond->canAreaSearch($loop_type_ct)) continue;
                } else if ($dispType == static::DISP_CHOSON) {
                    if (is_array($params->getShikugunCt())) continue;
                    if (!$searchCond->canAreaSearch($loop_type_ct)) continue;
                    $settingRow = $settings->search->getSearchSettingRowByTypeCt($loop_type_ct);
                    if (!$settingRow || !$settingRow->toSettingObject()->area_search_filter->canChosonSearch()) continue;
                } else if ($dispType == static::DISP_LINE || $dispType == static::DISP_EKI) {
                    if(!$searchCond->canLineSearch($loop_type_ct)) continue;
                } else if ($dispType == static::DISP_MAP){
                    if(!$searchCond->canSpatialSearch($loop_type_ct)) continue;
                }

        		$li = pq('<li><a></a></li>');
                $url = "/{$typeList->getUrl($type)}/";
                switch ($dispType) {
                    case static::DISP_PREF:
                        break;
                    case static::DISP_CITY:
                        $url .= $params->getKenCt() ."/";
                        break;
                    case static::DISP_CHOSON:
                        $url .= $params->getKenCt().'/'.$params->getShikugunCt().'-city/';
                        break;
                    case static::DISP_LINE:
                        $url .= $params->getKenCt().'/line.html';
                        break;
                    case static::DISP_EKI:
                        $url .= $params->getKenCt().'/'.$params->getEnsenCt().'-line/';
                        break;
                    case static::DISP_MAP:
                        $url .= $params->getKenCt() ."/map.html";
                        break;
                    default:
                        throw new \Exception('Illegal Argument');
                }
        		$li['a']->text($typeList->get($type))
        		->attr('href', $url)
        		->attr('target', '_blank');
        		$ul->append($li);
                $cnt++;

        	}
            if($cnt == 0) {
                $otherShumokuElem->remove();
                $doc['.element-search-from']->removeClass('element-line');
                //さらに特集もなかった場合はもっと消す
                if($sp_cnt == 0) {
                    $otherElem = $doc['.element-search-from'];
                    $otherElem->remove();
                }
            }
        }
        else {
            // その他の特集が削除されているパターンの場合は無視
            $otherSpecialElem = $doc['.element-search-from:eq(0)'];
        	$otherSpecialElem->removeClass('element-line');
        	$otherShumokuElem->remove();
        }
    }

    /**
     * 特集直接一覧
     */
    public function createElementDirectResult($doc,
        Params $params,
        Settings $settings,
        Datas $datas)
    {
        $this->searchConditions(
	            $doc, $params, $settings, $datas,
        		self::DISP_PREF);
    	$doc['.element-search-from:eq(0) .element-search-from-item:gt(0)']->remove();

        /**
         * 特集から探す
         */
        $specialElem = $doc['.element-search-from:eq(0)'];

        // 特集を取得
        $specials = $datas->getSeoSpecials();
        if ($specials) {
            $pNames = $datas->getParamNames();
            $shumoku_nm = $pNames->getShumokuName();
            if (!empty($shumoku_nm)) {
                $doc['h4.heading-search-from.recommend']->text($shumoku_nm . 'の特集から探す');
            }

            $ul = $specialElem['ul'];
            $ul->empty();
            foreach ($specials as $row) {
                $li = pq('<li><a></a></li>');
                $li['a']->text($row->title)
                    ->attr('href', "/{$row->filename}/")
                    ->attr('target', '_blank');
                $ul->append($li);
            }
        }
        else {
            $specialElem->remove();
        }
    }

    /*
     * お薦め物件コマと、特集から探すのリンク
     */
	private function createElement($doc,
        Params $params,
        Settings $settings,
        Datas $datas)
	{
        /**
         * おすすめ物件
         */
        $recommendElem = $doc['.element-recommend'];
        $recommendList = $datas->getRecommendList();
        // 0件は要素削除
        $total = $recommendList['total_count'];
        if ($total == 0) {
			$recommendElem->remove();
		} else {
            $komaMaker = new Koma();
            // ATHOME_HP_DEV-4841 : 第4引数として、PageInitialSettings を追加
			$komaMaker->createRecommendKoma($recommendElem, $recommendList, $params, $settings->page);
		}

        /**
         * 特集から探す
         */
        $specialElem = $doc['.element-search-from:eq(0)'];

        // 特集を取得
        $cnt = 0;
        $specials = $datas->getSeoSpecials();
        if ($specials) {
            $pNames = $datas->getParamNames();
            $shumoku_nm = $pNames->getShumokuName();
            if (!empty($shumoku_nm)) {
                $doc['h4.heading-search-from.recommend']->text($shumoku_nm . 'の特集から探す');
            }

        	$ul = $specialElem['ul'];
        	$ul->empty();
        	foreach ($specials as $row) {
                $cnt++;
        		$li = pq('<li><a></a></li>');
        		$li['a']->text($row->title)
        			->attr('href', "/{$row->filename}/")
        			->attr('target', '_blank');
        		$ul->append($li);
        	}
        }
        else {
        	$specialElem->remove();
        }

        return $cnt;
    }

    /**
     * 物件一覧
     */
	public function result($doc,
        Params $params,
        Settings $settings,
		Datas $datas)
	{
        $searchCond = $settings->search;

    	$pNames = $datas->getParamNames();
        $comId = $params->getComId();
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_ct_for_shikugun = (array)$type_ct;
        $type_id_for_shikugun = Estate\TypeList::getInstance()->getTypeByUrl($type_ct_for_shikugun[0]);
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();;
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        $ensen_nm = $pNames->getEnsenName();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        $eki_cd = $pNames->getEkiCd();
        $eki_nm = $pNames->getEkiName();
        // 検索タイプ：駅の場合は、駅ひとつ指定なので、駅ローマ字から沿線情報を取得
        if ($s_type == $params::SEARCH_TYPE_EKI) {
            $ekiObj = Models\EnsenEki::getObjBySingle($eki_ct);
            $ensen_ct = $ekiObj->getEnsenCt();
            $ensenObj = Services\ServiceUtils::getEnsenObjByConst($ken_cd, $ensen_ct);
            $ensen_cd = $ensenObj->code;
            $ensen_nm = $ensenObj->ensen_nm;
        }

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();
        $locate_nm = $pNames->getLocateName();

		$typeList = Estate\TypeList::getInstance();

        /**
         * 特集から探す
         */
        $noSpecial = false;
        $specialElem = $doc['.element-search-from-item:eq(0)'];
        $specialElem['h4']->text($shumoku_nm . 'の特集から探す');

        // 特集を取得
        $specials = $datas->getSeoSpecials();
        if ($specials) {
        	$ul = $specialElem['ul'];
        	$ul->empty();
        	foreach ($specials as $row) {
        		$li = pq('<li><a></a></li>');
        		$li['a']->text($row->title)
        			->attr('href', "/{$row->filename}/")
        			->attr('target', '_blank');
        		$ul->append($li);
        	}
        }
        else {
            $noSpecial = true;
        }

        // 他の物件種目
        $noOther = false;
        $otherShumokuElem = $doc['.element-search-from-item:eq(1)'];
        $otherShumoks = $searchCond->getShumokuWithoutTypeCt($type_ct);

        if ($otherShumoks && ! is_array($params->getEnsenCt())) {
        	$ul = $otherShumokuElem['ul'];
        	$ul->empty();
            $cnt=0;
        	foreach ($otherShumoks as $type) {
                //都道府県が存在しない場合は作成しない
                $pref_codes = $settings->company->getSearchSettingRowset()->getPref($type);
                if(!in_array($ken_cd, $pref_codes)) continue;

                $otherSettingRow = $settings->search->getSearchSettingRowByTypeId($type);
                if (!$otherSettingRow) {
                    continue;
                }
                $otherSettingObject = $otherSettingRow->toSettingObject();

                /**
                 * CMS設定チェック
                 */
                switch ($s_type) {
                    case $params::SEARCH_TYPE_LINE:
                        // 沿線設定がない場合スキップ
                        if (!isset($otherSettingObject->area_search_filter->area_3[$ken_cd])) {
                            continue 2;
                        }
                        if (!in_array($ensen_cd, $otherSettingObject->area_search_filter->area_3[$ken_cd])) {
                            continue 2;
                        }
                        break;
                    case $params::SEARCH_TYPE_EKI:
                        // 駅設定がない場合スキップ
                        if (!isset($otherSettingObject->area_search_filter->area_4[$ken_cd])) {
                            continue 2;
                        }
                        if (!in_array($eki_cd, $otherSettingObject->area_search_filter->area_4[$ken_cd])) {
                            continue 2;
                        }
                        break;
                    case $params::SEARCH_TYPE_CITY:
                        // 市区群設定がない場合スキップ
                        if (!isset($otherSettingObject->area_search_filter->area_2[$ken_cd])) {
                            continue 2;
                        }
                        if (!in_array($shikugun_cd, $otherSettingObject->area_search_filter->area_2[$ken_cd])) {
                            continue 2;
                        }
                        break;
                    case $params::SEARCH_TYPE_SEIREI:
                        // 市区群設定がない場合スキップ
                        if (!isset($otherSettingObject->area_search_filter->area_2[$ken_cd])) {
                            continue 2;
                        }
                        // 政令指定都市内の市区郡設定があればOK
                        foreach ($pNames->getLocateObj()->shikuguns as $_shikugun) {
                            if (in_array($_shikugun['code'], $otherSettingObject->area_search_filter->area_2[$ken_cd])) {
                                break 2;
                            }
                        }
                        // 政令指定都市内の市区郡設定がなければスキップ
                        continue 2;
                    case $params::SEARCH_TYPE_CHOSON:
                        // 町名検索設定チェック
                        if (!$otherSettingObject->area_search_filter->canChosonSearch()) {
                            continue 2;
                        }
                        $_choson_cds = $pNames->getChosonCd();
                        $_shikugun_cd = array_keys($_choson_cds);
                        $_shikugun_cd = $_shikugun_cd[0];
                        $_choson_cd = array_values($_choson_cds[$_shikugun_cd]);
                        $_choson_cd = $_choson_cd[0];
                        // 市区群チェック
                        if (
                            !isset($otherSettingObject->area_search_filter->area_2[$ken_cd]) ||
                            !in_array($_shikugun_cd, $otherSettingObject->area_search_filter->area_2[$ken_cd])
                        ) {
                            continue 2;
                        }

                        // 町村チェック
                        if (
                            isset($otherSettingObject->area_search_filter->area_5[$ken_cd][$_shikugun_cd]) &&
                            is_array($otherSettingObject->area_search_filter->area_5[$ken_cd][$_shikugun_cd]) &&
                            $otherSettingObject->area_search_filter->area_5[$ken_cd][$_shikugun_cd] &&
                            !in_array($_choson_cd, $otherSettingObject->area_search_filter->area_5[$ken_cd][$_shikugun_cd])
                        ) {
                            continue 2;
                        }
                        break;
                }

        		$li = pq('<li><a></a></li>');
                $url = $this->getResultUrl($s_type, $type, $params);
        		$li['a']->text($typeList->get($type))
	        		->attr('href', $url)
    	    		->attr('target', '_blank');
        		$ul->append($li);
                $cnt++;
        	}

            if($cnt == 0) $noOther = true;
        }
        else {
            $noOther = true;
        }

        // 探し方を変更する
        if ($specialRow = $settings->special->getCurrentPagesSpecialRow()) {
            // 特集の場合
            $baseUrl = $params->getSpecialPath();
            $filterName = $specialRow->getTitle();
            $specialSetting = $specialRow->toSettingObject();
            $area = $specialSetting->area_search_filter->hasAreaSearchType();
            $line = $specialSetting->area_search_filter->hasLineSearchType();
        } else {
            // 通常検索の場合
            $type_ct = $params->getTypeCt();
            if (is_array($type_ct)) {
                $type_ct = $type_ct[0];
            }
            $baseUrl = $type_ct;
            $filterName = $shumoku_nm;
            $searchRow = $settings->company->getSearchSettingRowset()->getRowByUrl($type_ct);
            $area = $searchRow->hasAreaSearchType();
            $line = $searchRow->hasLineSearchType();
        }

        $changeSearchElem = $doc['.element-search-from-item:eq(2)'];
        $i=0;
        // 地域から探す
        if($area){
            $changeSearchElem['ul li:eq('.$i++.') a']->attr('href', "/${baseUrl}/${ken_ct}/");
        }else{
            $changeSearchElem['ul li:eq('.$i.')']->remove();
        }
        // 路線から探す
        if($line){
            $changeSearchElem['ul li:eq('.$i++.') a']->attr('href', "/${baseUrl}/${ken_ct}/line.html");
        }else{
            $changeSearchElem['ul li:eq('.$i.')']->remove();
        }


        // さらに絞り込む
        $moreSearchElem = $doc['.element-search-from-item:eq(3)'];
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $moreSearchElem['h4']->text("{$ensen_nm}の{$filterName}をさらに駅で絞り込む");
                // 沿線の駅一覧
                $ekis = $datas->getSeoAnotherChoiceList();
                $moreSearchElem['ul']->empty();
                foreach ($ekis as $eki)
                {
                    $ekiCode  = $eki['code'];
                    $ekiName  = $eki['eki_nm'].'駅';
                    $ekiRoman  = $eki['eki_roman'];
                    $li = pq('<li><a/></li>');
                    $ekiUrl = "/${baseUrl}/${ken_ct}/result/{$eki['ensen_roman']}-${ekiRoman}-eki.html";
                    $li['a']->attr('href', $ekiUrl)->text($ekiName);
                    $moreSearchElem['ul']->append($li);
                }

                break;
            case $params::SEARCH_TYPE_SEIREI:
                $moreSearchElem['h4']->text("{$ken_nm}{$locate_nm}の{$filterName}をさらにエリアで絞り込む");
                // 政令指定都市の市区郡一覧
                $shikuguns = $datas->getSeoAnotherChoiceList();
                $moreSearchElem['ul']->empty();
                // CMSで設定されている所在地コード
                if ($settings->special->getCurrentPagesSpecialRow()) {
                    $settingShozaichi_cd = $settings->special->getShikugun($ken_cd);
                } else {
                    $settingShozaichi_cd = $settings->search->getShikugun($type_id_for_shikugun, $ken_cd);
                }
                foreach ($shikuguns as $shikugun)
                {
                    $shikugunCode  = $shikugun['code'];
                    if (! in_array($shikugunCode, $settingShozaichi_cd)) continue;
                    $shikugunName  = $shikugun['shikugun_nm'];
                    $shikugunRoman  = $shikugun['shikugun_roman'];
                    $li = pq('<li><a/></li>');
                    $cityUrl = "/${baseUrl}/${ken_ct}/result/${shikugunRoman}-city.html";
                    $li['a']->attr('href', $cityUrl)->text($shikugunName);
                    $moreSearchElem['ul']->append($li);
                }
                break;
            case $params::SEARCH_TYPE_PREF:
                $moreSearchElem['h4']->text("{$ken_nm}の{$filterName}をさらにエリアで絞り込む");
                // 県のCMS市区郡一覧
                $shikuguns = $datas->getSeoAnotherChoiceList();
                $moreSearchElem['ul']->empty();
                foreach ($shikuguns as $shikugun)
                {
                    $shikugunName  = $shikugun['shikugun_nm'];
                    $shikugunRoman  = $shikugun['shikugun_roman'];
                    $li = pq('<li><a/></li>');
                    $cityUrl = "/${baseUrl}/${ken_ct}/result/${shikugunRoman}-city.html";
                    $li['a']->attr('href', $cityUrl)->text($shikugunName);
                    $moreSearchElem['ul']->append($li);
                }
                break;
            case $params::SEARCH_TYPE_CITY:
                if ($chosons = $datas->getSeoAnotherChoiceList()) {
                    $moreSearchElem['h4']->text("{$ken_nm}{$shikugun_nm}の{$filterName}をさらに町名で絞り込む");
                    $moreSearchElem['ul']->empty();
                    foreach ($chosons as $choson)
                    {
                        $li = pq('<li><a/></li>');
                        $cityUrl = "/${baseUrl}/${ken_ct}/result/{$shikugun_ct}-{$choson['code']}.html";
                        $li['a']->attr('href', $cityUrl)->text($choson['choson_nm']);
                        $moreSearchElem['ul']->append($li);
                    }
                } else {
                    $moreSearchElem->remove();
                }
                break;
            case $params::SEARCH_TYPE_EKI:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
            case $params::SEARCH_TYPE_FREEWORD:
                $moreSearchElem->remove();
                break;
            default:
                throw new \Exception('Illegal Argument');
        }

        if ($noSpecial) {
        	$specialElem->remove();
        }
        if ($noOther) {
        	$otherShumokuElem->remove();
        }
    }

    private function getResultUrl($s_type, $type, $params)
    {
        // 種目情報の取得
		$typeList = Estate\TypeList::getInstance();
        $type_ct = $typeList->getUrl($type);
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        // 検索タイプ：駅の場合は、駅ひとつ指定なので、駅ローマ字から沿線情報を取得
        if ($s_type == $params::SEARCH_TYPE_EKI) {
            $ekiObj = Models\EnsenEki::getObjBySingle($eki_ct);
            $ensen_ct = $ekiObj->getEnsenCt();
        }

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数

        $choson_ct = $params->getChosonCt();

        $url = '';
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $url = "/${type_ct}/${ken_ct}/result/${ensen_ct}-line.html";
                break;
            case $params::SEARCH_TYPE_CITY:
                $url = "/${type_ct}/${ken_ct}/result/${shikugun_ct}-city.html";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $url = "/${type_ct}/${ken_ct}/result/${locate_ct}-mcity.html";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $url = "/${type_ct}/${ken_ct}/result/{$choson_ct[0]}.html";
                break;
            case $params::SEARCH_TYPE_EKI:
                $url = "/${type_ct}/${ken_ct}/result/${eki_ct}-eki.html";
                break;
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $url = "/${type_ct}/${ken_ct}/result/";
                break;
            default:
                throw new \Exception('Illegal Argument');
        }
        return $url;
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }
}