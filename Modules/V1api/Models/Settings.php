<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;
use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\SearchCondSettings;
use Modules\V1api\Models\SpecialSettings;
/**
 * 各種設定 
 */
class Settings
{
    // 加盟店情報
    public $company;
    // サイト初期設定の読み込み
    public $page;
    // 検索設定の読み込み
    public $search;
    // 特集設定の読み込み
    public $special;

    public function __construct($params)
	{
		// 会社情報の読み込み
		$this->company = new Company($params);
		// サイト初期設定の読み込み
		$this->page = new PageInitialSettings($this->company);
		// 検索設定の読み込み
		$this->search = new SearchCondSettings($this->company);
		// 特集設定の読み込み
		$this->special = new SpecialSettings($this->company);

        // 対象特集の存在チェック
        $currentPagesSpecialRow = $this->special->findByUrl($params->getSpecialPath());
        if (!$params->isCmsSpecial() && $params->getSpecialPath()) {
            $specialSetting = $currentPagesSpecialRow->toSettingObject();
            // 種目を設定
            $types = explode(',', $currentPagesSpecialRow->enabled_estate_type);
            $typeCts = [];
            foreach ($types as $type) {
                $typeCts[] = Estate\TypeList::getInstance()->getUrl($type);
            }
            if (count($typeCts) === 1) {
                $typeCts = $typeCts[0];
            }
            if (getActionName() !== 'detail') {
                $params->setParam('type_ct', $typeCts);
            }
            $methodSetting = Estate\SpecialMethodSetting::getInstance();
            if (!$methodSetting->hasDefaultMethod($specialSetting->method_setting)) {
                $areaSearchFilter = $specialSetting->area_search_filter;
                $type_ct  = Estate\TypeList::getInstance()->getUrl($specialSetting->enabled_estate_type[0]);;
                $searchSetting = $this->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
                if(!$areaSearchFilter->has_search_page) {
                    $searchType = array();
                    if ($searchSetting->area_search_filter->hasAreaSearchType() ||
				        $searchSetting->area_search_filter->hasSpatialSearchType()) {
                        $searchType[] = 1;
                    }
                    if ($searchSetting->area_search_filter->hasLineSearchType()) {
                        $searchType[] = 2;
                    }
                    $areaSearchFilter->choson_search_enabled = $searchSetting->area_search_filter->choson_search_enabled;
                    $areaSearchFilter->search_type = $searchType;
                }
                if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
                    $areaSearchFilter->area_1 = Services\ServiceUtils::setPrefInId($specialSetting->houses_id, $searchSetting->area_search_filter->area_1);
                    if (count($areaSearchFilter->area_1) == 0) {
                        $areaSearchFilter->area_1 = $searchSetting->area_search_filter->area_1;
                    }
                }
                if ($methodSetting->hasRecommenedMethod($specialSetting->method_setting)) {
                    $pNames = new ParamNames($params);
                    if ($areaSearchFilter->hasAreaSearchType() || $areaSearchFilter->hasSpatialSearchType()) {
                        $citier = new Logic\City();
                        $prefsList = $citier->getPrefRecommendByshikugun($params, $this, $pNames, $specialSetting->search_filter, $currentPagesSpecialRow, $searchSetting);
                    } else {
                        $liner = new Logic\LineEki();
                        $prefsList = $liner->getPrefRecommendByLine($params, $this, $pNames, $specialSetting->search_filter, $currentPagesSpecialRow, $searchSetting);
                    }
                    if (count($prefsList) > 0) {
                        $areaSearchFilter->area_1 = $prefsList;
                    } else {
                        $areaSearchFilter->area_1 = $searchSetting->area_search_filter->area_1;
                    }
                }
                if ($areaSearchFilter->hasAreaSearchType() || $areaSearchFilter->hasSpatialSearchType()) {
                    $areaSearchFilter->area_2 = $this->getDataByPref($searchSetting->area_search_filter->area_2, $areaSearchFilter->area_1);
                    $areaSearchFilter->area_5 = $this->getDataByPref($searchSetting->area_search_filter->area_5, $areaSearchFilter->area_1);
                    $areaSearchFilter->area_6 = $this->getDataByPref($searchSetting->area_search_filter->area_6, $areaSearchFilter->area_1);
                }
                if ($areaSearchFilter->hasLineSearchType()) {
                    $areaSearchFilter->area_3 = $this->getDataByPref($searchSetting->area_search_filter->area_3, $areaSearchFilter->area_1);
                    $areaSearchFilter->area_4 = $this->getDataByPref($searchSetting->area_search_filter->area_4, $areaSearchFilter->area_1);
                }

                $currentPagesSpecialRow['area_search_filter'] = json_encode($areaSearchFilter);
            }
        }
        $this->special->setCurrentPagesSpecialRow($currentPagesSpecialRow);
    }

    private function getDataByPref($data, $prefs) {
        $keys = array();
        foreach($data as $pref => $values) {
            if (!in_array($pref, $prefs)) {
                $keys[] = $pref;
            }
        }
        foreach($keys as $key) {
            unset($data[$key]);
        }
        return $data;
    }
}