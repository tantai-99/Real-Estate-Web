<?php
namespace Modules\V1api\Models;

/**
 * 主要検索条件パラメータ値の日本語名
 * 物件種目、県、所在地、沿線、駅、が
 * 単一の主要検索条件パラメータとして指定された場合、
 * その日本語名はMETA情報、タイトル、パンくずなど
 * 各所で使用されるので、このクラスでまとめて
 * 日本語名を取得する。
 *
 * 複数指定で主要検索条件パラメータが指定された場合、
 * 上記のような使われ方をしないので、このクラスでは
 * 日本語名を取得しない。
 */

use Modules\V1api\Services;
use Library\Custom\Model\Estate;

class ParamNames
{
    
    /**
     * 物件種目
     */
    private $shumoku_cd;
    private $shumoku_nm;

    /**
     * 都道府県
     */
    private $ken_cd;
    private $ken_nm;
    private $ken_nm_without_suffix;

    /**
     * 市区郡
     */
    private $shikugun_cd;
    private $shikugun_nm;
    private $shikuguns; // [市区群コード => [市区群データ, ...], ...]

    private $choson_shikuguns; // [市区群コード => [市区群データ, ...], ...]
    private $choson_cd; // [市区群コード => [町村コード, ...], ...]
    private $choson_nm;

    /**
     * 沿線
     */
    private $ensen_cd;
    private $ensen_nm;

    /**
     * 駅
     */
    private $eki_cd;
    private $eki_nm;

    /**
     * ロケート（政令指定都市）
     */
    private $locate_cd;
    private $locate_nm;
    private $locate;

    public function __construct($params)
    {
        if (! is_null($params->getTypeCt()))
        {
            if (!is_array($params->getTypeCt())) {
                $type_ct = $params->getTypeCt();
                $this->shumoku_cd = Services\ServiceUtils::getShumokuCdByConst($type_ct);
                // 種別タイプIDではなく、すでに種別コードになっている？
                switch ($this->shumoku_cd)
                {
                    case Estate\TypeList::TYPE_CHINTAI:
                        $this->shumoku_cd = '5007';
                        break;
                    case Estate\TypeList::TYPE_KASI_TENPO:
                        $this->shumoku_cd = '5008';
                        break;
                    case Estate\TypeList::TYPE_KASI_OFFICE:
                        $this->shumoku_cd = '5009';
                        break;
                    case Estate\TypeList::TYPE_PARKING:
                        $this->shumoku_cd = '5011';
                        break;
                    case Estate\TypeList::TYPE_KASI_TOCHI:
                        $this->shumoku_cd = '5012';
                        break;
                    case Estate\TypeList::TYPE_KASI_OTHER:
                        $this->shumoku_cd = '5010';
                        break;
                    case Estate\TypeList::TYPE_MANSION:
                        $this->shumoku_cd = '5003';
                        break;
                    case Estate\TypeList::TYPE_KODATE:
                        $this->shumoku_cd = '5002';
                        break;
                    case Estate\TypeList::TYPE_URI_TOCHI:
                        $this->shumoku_cd = '5001';
                        break;
                    case Estate\TypeList::TYPE_URI_TENPO:
                        $this->shumoku_cd = '5004';
                        break;
                    case Estate\TypeList::TYPE_URI_OFFICE:
                        $this->shumoku_cd = '5005';
                        break;
                    case Estate\TypeList::TYPE_URI_OTHER:
                        $this->shumoku_cd = '5006';
                        break;
                }
                $this->shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);
            } else {
                $shumoku_cd = [];
                $shumoku_nm = [];
                foreach ($params->getTypeCt() as $ct) {
                    $shumoku_cd[] = Services\ServiceUtils::getShumokuCdByConst($ct);
                    $shumoku_nm[] = Services\ServiceUtils::getShumokuNameByConst($ct);
                }
                $this->shumoku_cd = $shumoku_cd;
                $this->shumoku_nm = implode(' / ', $shumoku_nm);
            }
        }
        if (! is_null($params->getKenCt()) && ! is_array($params->getKenCt()))
        {
            $ken_ct = $params->getKenCt();
            $this->ken_cd = Services\ServiceUtils::getKenCdByConst($ken_ct);
            $this->ken_nm = Services\ServiceUtils::getKenNameByConst($ken_ct);
            $this->ken_nm_without_suffix = Services\ServiceUtils::getKenNameByConstWithoutSuffix($ken_ct);
        }
        if (! is_null($params->getShikugunCt()) && ! is_array($params->getShikugunCt()))
        {
            $shikugun_ct = $params->getShikugunCt();
            $shikugunObj = Services\ServiceUtils::getShikugunObjByConst($this->ken_cd, $shikugun_ct);
            $this->shikugun_cd = $shikugunObj->code;
            $this->shikugun_nm = $shikugunObj->shikugun_nm;
            $shikuguns = array();
            $shikuguns[ $shikugunObj->code ] = $shikugunObj;
            $this->shikuguns = $shikuguns;
        }
        if (! is_null($params->getShikugunCt()) && is_array($params->getShikugunCt()))
        {
            $shikugun_ct = $params->getShikugunCt();
            $shikugunList = Services\ServiceUtils::getShikugunListByConsts($this->ken_cd, $shikugun_ct);
            $shikuguns = array();
            foreach ($shikugunList as $shikugun) {
                $shikuguns[ $shikugun['code'] ] = (object) $shikugun;
            }
            $this->shikuguns = $shikuguns;
        }

        if ($choson_ct = $params->getChosonCt()) {
            $shikugunCtChosonCds = [];
            $shikugunCts = [];
            foreach ($choson_ct as $code) {
                list($shikugun_cd, $choson_cd) = explode('-', $code);
                $shikugunCts[] = $shikugun_cd;
                $shikugunCtChosonCds[ $shikugun_cd ][ $choson_cd ] = $choson_cd;
            }
            $shikugunObjList = (array)Services\ServiceUtils::getShikugunListByConsts($this->ken_cd, $shikugunCts);
            $shikuguns = [];
            $chosonCds = [];
            foreach ($shikugunObjList as $shikugunObj) {
                $shikuguns[ $shikugunObj['code'] ] = (object) $shikugunObj;
                if (isset($shikugunCtChosonCds[ $shikugunObj['shikugun_roman'] ])) {
                    $chosonCds[ $shikugunObj['code'] ] = $shikugunCtChosonCds[ $shikugunObj['shikugun_roman'] ];
                }
            }
            $this->choson_cd = $chosonCds;

            // 町村データ取得
            if ($chosonCds) {
                $shikugun_cds = array_keys($chosonCds);
                $chosonList = Services\ServiceUtils::getChosonListByShikugunCd($shikugun_cds);
                foreach ($chosonList as $chosonSikugun) {
                    $shikugunObj = $shikuguns[ $chosonSikugun['shikugun_cd'] ];
                    $shikugunObjChosons = [];
                    foreach ($chosonSikugun['chosons'] as $choson) {
                        if (isset($chosonCds[ $chosonSikugun['shikugun_cd'] ][ $choson['code'] ])) {
                            if (count($choson_ct) === 1) {
                                $this->choson_nm = $choson['choson_nm'];
                            }

                            $shikugunObjChosons[ $choson['code'] ] = (object)$choson;
                        }
                    }
                    $shikugunObj->chosons = $shikugunObjChosons;
                }
            }
            $this->choson_shikuguns = $shikuguns;

            // 市区群パラメータがない場合は補完する
            if (!$this->shikuguns) {
                $this->shikuguns = $shikuguns;
                $params->setParam('shikugun_ct', implode(',', $shikugunCts));
                if (count($shikugunObjList) == 1) {
                    $this->shikugun_cd = $shikugunObjList[0]['code'];
                    $this->shikugun_nm = $shikugunObjList[0]['shikugun_nm'];
                }
            }
        }

        if (! is_null($params->getEnsenCt()) && ! is_array($params->getEnsenCt()))
        {
            $ensen_ct = $params->getEnsenCt();
            $ensenObj = Services\ServiceUtils::getEnsenObjByConst($this->ken_cd, $ensen_ct);
            $this->ensen_cd = $ensenObj->code;
            $this->ensen_nm = $ensenObj->ensen_nm;
        }
        if (! is_null($params->getEkiCt()) && ! is_array($params->getEkiCt()))
        {
            $eki_ct = $params->getEkiCt();
            $ekiObj = Services\ServiceUtils::getEkiObjByConst($eki_ct);
            $this->eki_cd = $ekiObj->code;
            $this->eki_nm = $ekiObj->eki_nm;
        }
        if (! is_null($params->getLocateCt()) && ! is_array($params->getLocateCt()))
        {
            $locate_ct = $params->getLocateCt();
            $locateObj = Services\ServiceUtils::getLocateObjByConst($this->ken_cd, $locate_ct);
            $this->locate_cd = $locateObj->locate_cd;
            $this->locate_nm = $locateObj->locate_nm;
            $this->locate = $locateObj;
        }
    }

    public function getShumokuName()
    {
        return isset($this->shumoku_nm)?
                $this->shumoku_nm:
                null;
    }

    public function getShumokuCd()
    {
        return isset($this->shumoku_cd)?
                $this->shumoku_cd:
                null;
    }

    public function getKenName()
    {
        return isset($this->ken_nm)?
                $this->ken_nm:
                null;
    }

    public function getKenNameWithoutSuffix()
    {
        return isset($this->ken_nm_without_suffix)?
            $this->ken_nm_without_suffix:
            null;
    }


    public function getKenCd()
    {
        return isset($this->ken_cd)?
                $this->ken_cd:
                null;
    }
        
    public function getShikugunName()
    {
        return isset($this->shikugun_nm)?
                $this->shikugun_nm:
                null;
    }
        
    public function getShikugunCd()
    {
        return isset($this->shikugun_cd)?
                $this->shikugun_cd:
                null;
    }

    public function getShikuguns()
    {
        return $this->shikuguns;
    }

    public function getChosonCd() {
        return $this->choson_cd;
    }

    public function getChosonName() {
        return $this->choson_nm;
    }

    /**
     * 町村コードパラメータで指定された市区群オブジェクト配列を取得する
     * @return array
     */
    public function getChosonShikuguns() {
        return $this->choson_shikuguns;
    }
    
    public function getEnsenName()
    {
        return isset($this->ensen_nm)?
                $this->ensen_nm:
                null;
    }
    
    public function getEnsenCd()
    {
        return isset($this->ensen_cd)?
                $this->ensen_cd:
                null;
    }

    public function getEkiName()
    {
        return isset($this->eki_nm)?
                $this->eki_nm:
                null;
    }

    public function getEkiCd()
    {
        return isset($this->eki_cd)?
                $this->eki_cd:
                null;
    }

    public function getLocateName()
    {
        return isset($this->locate_nm)?
                $this->locate_nm:
                null;
    }

    public function getLocateCd()
    {
        return isset($this->locate_cd)?
                $this->locate_cd:
                null;
    }

    public function getLocateObj() {
        return $this->locate;
    }
}