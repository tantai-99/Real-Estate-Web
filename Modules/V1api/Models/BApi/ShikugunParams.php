<?php
namespace Modules\V1api\Models\BApi;

use Modules\V1api\Services;

class ShikugunParams extends AbstractParams
{
    const GROUPING_TYPE_LOCATE_CD = 'locate_cd';

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $target = 'engine_rental';
    protected $media = 'pc';
    protected $kaiin_link_no;
    protected $group_id;
    protected $csite_bukken_shumoku_cd;
    protected $ken_cd;
    protected $grouping;
    protected $shozaichi_cd;
    protected $bukken_shozaichi_cd;
    protected $shikugun_cd;

    /**
     * @param $kaiin_link_no
     */
    public function setKaiinLinkNo ($kaiin_link_no)
    {
        $this->kaiin_link_no = $kaiin_link_no;
    }
    
    /**
     * @param $csite_bukken_shumoku_cd 検索用物件種目コードの配列
     */
    public function setCsiteBukkenShumoku ($csite_bukken_shumoku_cd)
    {
        $this->csite_bukken_shumoku_cd = $csite_bukken_shumoku_cd;
    }

    /**
     * @param $ken_cd
     */
    public function setKenCd($ken_cd)
    {
        $this->ken_cd = $ken_cd;
    }
    
    // GROUPING_TYPE_LOCATE_CD
    public function setGrouping($groupingType)
    {
        $this->grouping = $groupingType;
    }
    
    public function setShozaichiCd($shozaichi_cd)
    {
        $this->shozaichi_cd = $shozaichi_cd;
    }

    public function setBukkenShozaichiCd($codes)
    {
        $codes = (array)$codes;
        $code1s = []; // 所在地CD1
        $code2s = []; // 所在地CD1:所在地CD2
        foreach ($codes as $code) {
            if (strpos($code, ':') === false) {
                $code1s[] = $code;
            } else {
                $code2s[] = $code;
            }
        }

        // 所在地コード2を含む指定がない場合、shozaichi_cd(所在地CD1)で検索する
        if (!$code2s) {
            $this->setShozaichiCd($code1s);
            return;
        }

        // 所在地コード1指定の場合は物件APIでエラーになってしまう為、町村コードを補完する
        if ($code1s) {
            $chosonShikuguns = Services\ServiceUtils::getChosonListByShikugunCd($code1s);
            foreach ($chosonShikuguns as $shikugun) {
                foreach ($shikugun['chosons'] as $choson) {
                    $code2s[] = "{$shikugun['shikugun_cd']}:{$choson['code']}";
                }
            }
        }

        $this->bukken_shozaichi_cd = $code2s;
    }

    public function setGroupId($group_id)
    {
        if (isset($this->getConfig()->dummy_bapi_group_id)) {
    		$this->group_id = $this->getConfig()->dummy_bapi_group_id;
    	} else {
    		$this->group_id = $group_id;
    	}
    }
}