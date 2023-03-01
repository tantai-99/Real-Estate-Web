<?php
namespace Modules\V1api\Models;
class Datas
{
	/**
     * パラメータ拡張
     * @var Modules\V1api\Models\ParamNames
     */
	private $paramNames;
	/* こだわり検索条件 */
	private $searchFilter;
	/* 表示用こだわり検索条件 */
	private $frontSearchFilter;
	/* 物件一覧リスト */
	private $bukkenList;
	/* 物件詳細 */
	private $bukken;
	/* 駅選択画面用リスト */
	private $ekiList;
	private $ekiSettingOfKen;
	/* 沿線選択画面用リスト */
	private $lineList;
	private $lineCountList;
	/* エリア選択画面用リスト */
	private $cityList;
	/* 都道府県設定 */
	private $prefs;
	/* こだわり検索条件ファセット（件数のみ） */
	private $facetJson;
	/* SEOリンク用データ */
	private $seoSpecials;
	private $seoSpecialsRent;
	private $seoSpecialsPurchase;
	private $recommendList;
	private $seoAnotherChoiceList;
	private $historyKoma;
	private $codeList;
	/* 町名選択画面用リスト */
	private $chosonList;

	/* 地図検索 */
	private $spatialEstate;

    private $count;

    private $suggestList;

	public function setLineCountList($lineCountList) {
		$this->lineCountList = $lineCountList;
	}
	public function getLineCountList() {
		return $this->lineCountList;
	}

	public function setEkiSettingOfKen($ekiSettingOfKen) {
		$this->ekiSettingOfKen = $ekiSettingOfKen;
	}
	public function getEkiSettingOfKen() {
		return $this->ekiSettingOfKen;
	}

	public function setFrontSearchFilter($frontSearchFilter) {
		$this->frontSearchFilter = $frontSearchFilter;
	}
	public function getFrontSearchFilter() {
		return $this->frontSearchFilter;
	}

	public function setFacetJson($facetJson) {
		$this->facetJson = $facetJson;
	}
	public function setCodeList($codeList) {
		$this->codeList = $codeList;
	}
	public function getFacetJson() {
		return $this->facetJson;
	}

	public function setHistoryKoma($historyKoma) {
		$this->historyKoma = $historyKoma;
	}
	public function getHistoryKoma() {
		return $this->historyKoma;
	}

	public function setSeoAnotherChoiceList($seoAnotherChoiceList) {
		$this->seoAnotherChoiceList = $seoAnotherChoiceList;
	}
	public function getSeoAnotherChoiceList() {
		return $this->seoAnotherChoiceList;
	}

	public function setSearchFilter($searchFilter) {
		$this->searchFilter = $searchFilter;
	}
	public function getSearchFilter() {
		return $this->searchFilter;
	}

	public function setBukkenList($bukkenList) {
		$this->bukkenList = $bukkenList;
	}
	public function getBukkenList() {
		return $this->bukkenList;
	}

	public function setBukken($bukken) {
		$this->bukken = $bukken;
	}
	public function getBukken() {
		return $this->bukken;
	}

	public function setEkiList($ekiList) {
		$this->ekiList = $ekiList;
	}
	public function getEkiList() {
		return $this->ekiList;
	}

	public function setChosonList($chosonList) {
	    $this->chosonList = $chosonList;
    }
    public function getChosonList() {
	    return $this->chosonList;
    }

	public function setLineList($lineList) {
		$this->lineList = $lineList;
	}
	public function getLineList() {
		return $this->lineList;
	}

	public function setCityList($cityList) {
		$this->cityList = $cityList;
	}
	public function getCityList() {
		return $this->cityList;
	}

	public function setPrefSetting($prefs) {
		$this->prefs = $prefs;
	}
	public function getPrefSetting() {
		return $this->prefs;
	}

	public function setSeoSpecials($seoSpecials) {
		$this->seoSpecials = $seoSpecials;
	}
	public function getSeoSpecials() {
		return $this->seoSpecials;
	}

	public function setSeoSpecialsPurchase($seoSpecialsPurchase) {
		$this->seoSpecialsPurchase = $seoSpecialsPurchase;
	}
	public function getSeoSpecialsPurchase() {
		return $this->seoSpecialsPurchase;
	}

	public function setSeoSpecialsRent($seoSpecialsRent) {
		$this->seoSpecialsRent = $seoSpecialsRent;
	}
	public function getSeoSpecialsRent() {
		return $this->seoSpecialsRent;
	}

	public function setParamNames($paramNames) {
		$this->paramNames = $paramNames;
	}
	public function getParamNames() {
		return $this->paramNames;
	}

	public function setRecommendList($recommendList) {
		$this->recommendList = $recommendList;
	}
	public function getRecommendList() {
		return $this->recommendList;
	}
	public function getCodeList() {
		return $this->codeList;
	}
	public function setSpatialEstate($spatialEstate) {
		$this->spatialEstate = $spatialEstate;
	}
	public function getSpatialEstate() {
		return $this->spatialEstate;
	}
    public function setCount($count) {
        $this->count = $count;
    }
    public function getCount() {
        return $this->count;
    }
    public function setSuggestList($suggestList) {
        return $this->suggestList = $suggestList;
    }
    public function getSuggestList() {
        return $this->suggestList;
    }
}