<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;

class SpecialSettings
{

    /**
     * @var App\Models\SpecialEstate
     */
    private $currentPagesSpecial;
    
    /**
     * 
     * @var Modules\V1api\Models\Company
     */
    private $company;
    
    public function __construct($company) {
        $this->company = $company;
    }
    
    public function getSpecialRowset() {
        return $this->company->getSpecialRowset();
    }
    
    public function getSpecialRowsetRandom() {
        return $this->company->getSpecialRowsetRandom();
    }
    
    public function findByUrl($url) {
        return $this->getSpecialRowset()->findByUrl($url);
    }
    
    /**
     * 対象特集ページの特集レコードオブジェクトをセットする
     * @param App\Models\SpecialEstate $row
     */
    public function setCurrentPagesSpecialRow($row) {
        $this->currentPagesSpecial = $row;
    }
    
    /**
     * 対象特集ページの特集レコードオブジェクトを取得する
     * @return App\Models\SpecialEstate
     */
    public function getCurrentPagesSpecialRow() {
        return $this->currentPagesSpecial;
    }
    
    /**
     * 指定された種目の特集を取得する
     * @param {string|array} $estateTypes 物件種目
     * @param {int} $max 最大件数
     */
    public function pickSpecialRowsByEstateType($estateTypes, $max = 10) {
    	$estateTypes = (array)$estateTypes;
    	foreach ($estateTypes as $k => $v) {
    	    $estateTypes[$k] = (int) $v;
        }
    	$ignore = $this->getCurrentPagesSpecialRow();
    	$result = [];
    	foreach ($this->getSpecialRowsetRandom() as $row) {
    		if ($ignore && $ignore->id == $row->id) {
    			continue;
    		}

    		$typesOfSpecial = explode(',', $row->enabled_estate_type);
    		foreach ($typesOfSpecial as $type) {
                if (in_array((int)$type, $estateTypes, true)) {
                    $result[] = $row;
                    break;
                }
            }

    		if (count($result) == $max) {
    			break;
    		}
    	}
    	return $result;
    }
    
    /**
     * 指定された種目の特集を取得する
     * @param {string|array} $type_ct 物件種目URL
     * @param {int} $max 最大件数
     */
    public function pickSpecialRowsByTypeCt($type_ct, $max = 10) {
        $type_ct = (array) $type_ct;
        $estateType = [];
        foreach ($type_ct as $ct) {
            $estateType[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
        }
    	return $this->pickSpecialRowsByEstateType($estateType, $max);
    }

    public function getEkiByEnsen($ensenCdList) {
        $row = $this->getCurrentPagesSpecialRow();
        if (!$row) {
            return null;
        }

        $settingObject = $row->toSettingObject();
        $kenEkiList = $settingObject->area_search_filter->area_4;
        if (is_null($kenEkiList)) return null;
        $result = array();
        foreach ($kenEkiList as $ken => $ekiList) {
            if (empty($ekiList)) continue;
            foreach ($ekiList as $eki) {
                $ensenCd = substr($eki, 0, 4);
                if (in_array((string)$ensenCd, $ensenCdList)) {
                    array_push($result, $eki);
                }
            }
        }
        if (count($result) == 0) $result = null;
        return $result;
    }

    public function getShikugun($prefCode) {
        $row = $this->getCurrentPagesSpecialRow();
        if (!$row) {
            return null;
        }

        $settingObject = $row->toSettingObject();
        return (array) $settingObject->area_search_filter->area_2->getDataByPref($prefCode);
    }
}