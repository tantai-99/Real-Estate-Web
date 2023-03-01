<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;

class SearchCondSettings
{
    private $company;

    private $bukkenShumoku;

    public function __construct($company) {
        $this->company = $company;
        $this->bukkenShumoku = $this->company->getSearchSettingRowset()->getEstateTypes();
    }

    public function getShumoku()
    {
        return  $this->bukkenShumoku;
    }

    /**
     *
     * @param string $type_ct 物件種目URL
     */
    public function getShumokuWithoutTypeCt($type_ct) {
        $type_ct = (array) $type_ct;
        $typeList = Estate\TypeList::getInstance();
        $estateTypes = [];
        foreach ($type_ct as $ct) {
            $estateTypes[] = (int)$typeList->getTypeByUrl($ct);
        }
        $currentClass = $typeList->getClassByType($estateTypes[0]);
        $rentFlag = $typeList->isRent($currentClass);

        $result = [];

        foreach ($this->bukkenShumoku as $shumoku) {
            $class = $typeList->getClassByType($shumoku);
            if ($typeList->isRent($class) != $rentFlag) {
                continue;
            }
            if (in_array((int)$shumoku, $estateTypes, true)) {
                continue;
            }
            $result[] = $shumoku;
        }
        return $result;
    }

    public function containBothShumoku()
    {
        return ($this->containRentShumoku() && $this->containPurchaseShumoku());
    }

    public function containRentShumoku()
    {
        return ! $this->isPurchaseShumokuOnly();
    }

    public function containPurchaseShumoku()
    {
        return ! $this->isRentShumokuOnly();
    }

    public function isRentShumokuOnly()
    {
        $typeList = Estate\TypeList::getInstance();
        foreach ($this->bukkenShumoku as $cd) {
            // 種目情報
            $class = $typeList->getClassByType($cd);
            if ($class == Estate\ClassList::CLASS_BAIBAI_JIGYO ||
                $class == Estate\ClassList::CLASS_BAIBAI_KYOJU)
            {
                return false;
            }
        }
        return true;
    }

    public function isPurchaseShumokuOnly()
    {
        $typeList = Estate\TypeList::getInstance();
        foreach ($this->bukkenShumoku as $cd) {
            // 種目情報
            $class = $typeList->getClassByType($cd);
            if ($class == Estate\ClassList::CLASS_CHINTAI_KYOJU ||
                $class == Estate\ClassList::CLASS_CHINTAI_JIGYO)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $typeCt
     * @return null|App\Models\EstateClassSearch
     */
    public function getSearchSettingRowByTypeCt($typeCt) {
        return $this->company->getSearchSettingRowset()->getRowByUrl($typeCt);
    }

    /**
     * @param $typeCt
     * @return null|App\Models\EstateClassSearch
     */
    public function getSearchSettingRowPublishByTypeCt($typeCt) {
        if ($this->company->getSearchSettingRowsetPublish())
            return $this->company->getSearchSettingRowsetPublish()->getRowByUrl($typeCt);
        return null;
    }

    /**
     * @param $typeCt
     * @return null|App\Models\EstateClassSearch
     */
    public function getSearchSettingRowByTypeId($typeId) {
        return $this->company->getSearchSettingRowset()->getRowByTypeId($typeId);
    }

    /*
     * エリア検索が可能か
     */
    public function canAreaSearch($typeCt)
    {
        $row = $this->company->getSearchSettingRowset()->getRowByUrl($typeCt);
        if (!$row){
            return false;
        }
        return $row->hasAreaSearchType();
    }

    /*
     * 沿線検索が可能か
     */
    public function canLineSearch($typeCt)
    {
        $row = $this->company->getSearchSettingRowset()->getRowByUrl($typeCt);
        if (!$row){
            return false;
        }
        return $row->hasLineSearchType();
    }

    /*
     * 地図検索が可能か
     */
    public function canSpatialSearch( $typeCt )
    {
        $result = true  ;
        if ( is_array( $typeCt ) ) {
            foreach ( $typeCt as $ct )
            {   // 種目跨ぎが配列で来る想定で、全てOKならOK
                $result = $this->_canSpatialSearch( $ct ) ;
                if ( $result == false )
                {
                    break ;
                }
            }
        } else {
            $result = $this->_canSpatialSearch( $typeCt ) ;            
        }
        return $result ;
    }

    private function _canSpatialSearch( $typeCt )
    {
        $row = $this->company->getSearchSettingRowset()->getRowByUrl( $typeCt ) ;
        if ( !$row )
        {
            return false ;
        }
        return $row->hasSpatialSearchType() ;       // 真理値を返す
    }
    
    /*
     * 現在地からの地図検索有効判定
     */
    public function canMapSearchHere($typeCt)
    {
        $row = $this->company->getSearchSettingRowset()->getRowByUrl($typeCt);
        if (!$row){
            return false;
        }
        return $row->hasMapSearchHere();
    }

    /**
     * 県設定の取得
     * @param $type_id 種目のID
     * @return 種目に対応する県設定（県コードの配列）
     */
    public function getPref($type_id)
    {
        return $this->company->getSearchSettingRowset()->getPref($type_id);
    }

    /**
     * 市区郡設定の取得
     * @param $type_id 種目のID
     * @param $ken_cd 県コード
     * @return 種目・県コードに対応する市区郡設定（市区郡コードの配列）
     */
    public function getShikugun($type_id, $ken_cd)
    {
        return $this->company->getSearchSettingRowset()->getShikugun($type_id, $ken_cd);
    }
    /**
     * 沿線設定の取得
     * @param $type_id 種目のID
     * @param $ken_cd 県コード
     * @return 種目・県コードに対応する沿線設定（沿線コードの配列）
     */
    public function getEnsen($type_id, $ken_cd)
    {
        return $this->company->getSearchSettingRowset()->getEnsen($type_id, $ken_cd);
    }

    /**
     * 駅設定の取得
     * @param $type_id 種目のID
     * @param $ken_cd 県コード
     * @param $ensenCdList 沿線コードの配列
     * @return 種目・県コード・沿線コードに対応する駅設定（駅コードの配列）
     */
    public function getEkiByKenEnsen($type_id, $ken_cd, $ensenCdList)
    {
        return $this->company->getSearchSettingRowset()->getEki($type_id, $ken_cd, $ensenCdList);
    }

    public function getEkiByEnsen($type, $ensenCdList) {
    	$kenEkiList = $this->company->getSearchSettingRowset()->getKenEki($type);
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

    public function getEkiByEnsenWithoutKen($type, $ken_cd, $ensenCdList) {
    	$kenEkiList = $this->company->getSearchSettingRowset()->getKenEki($type);
    	if (is_null($kenEkiList)) return null;

    	$result = array();
    	foreach ($kenEkiList as $ken => $ekiList) {
    		if ($ken == $ken_cd) continue;
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
}