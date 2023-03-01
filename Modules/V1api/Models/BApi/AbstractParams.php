<?php
namespace Modules\V1api\Models\BApi;

use Modules\V1api\Services;
use Modules\V1api\Services\BApi;
use Library\Custom\Model\Estate;
use Library\Custom\Estate\Setting\SearchFilter;

abstract class AbstractParams
{
    // 物件区分
    protected $jishatasha_cd;
    protected $kensaku_engine_rental_nomi_kokai;
    protected $niji_kokoku_jido_kokai_fl;
    protected $end_muke_chukai_tesuryo_fuyo_fl;
    protected $niji_kokoku_fl;
    protected $or = [];

    // 注意：publicおよびprotectedクラスは、BAPパラメータに自動変換される。
    private $logger;
    private $_config;

    public function __construct()
    {
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    protected function getConfig() {
    	return $this->_config;
    }

    /**
     * オブジェクトに定義されているprotected変数を
     * 変数名をkeyに、変数値をvalueにした、
     * 物件API用のパラメータを生成して返します。
     * 変数値が配列の場合、","で連結した文字列に変換します。
     *
     * @param $instance Paramsのインスタンス
     * @return 物件API用のQuery
     */
    public function buildQuery ($instance)
    {
    	$this->validate();

    	$query = '?';
        $ref = new \ReflectionObject($instance);
        $isFirst = true;
        // ATHOME_HP_DEV-4802 ミドルウェアのバージョンアップの事前調査を実施する
        $propertyTypes = array(
            \ReflectionProperty::IS_PROTECTED,
            \ReflectionProperty::IS_PUBLIC
        );
        foreach($propertyTypes as $type) {
            $props = $ref->getProperties($type);
            foreach ($props as $prop)
            {
                $prop->setAccessible(true);
                $val = $prop->getValue($instance);
                if (! isset($val))
                {
                    continue;
                }
                if($prop->getname()=='ensen_roman')
                {
                    if($val==null || $val[0]==null)
                    {
                        continue;
                    }
                }
                $value = $val;
                if (is_array($val))
                {
                    $vals = array();
                    if (($name = $prop->getName()) == 'or') {
                        $value = '';
                        foreach($val as $v) {
                            $value = $value.($value != ''? '&' : '').$name.'[]='.$v;
                        }
                    } else {
                        $value= implode(",", $val);
                    }
                }
                
                if ($isFirst)
                {
                    $isFirst = false;
                }
                else
                {
                    $query = $query . '&';
                }
                if ($prop->getName() == 'or') {
                    $query = $query . $value;
                } else {
                    $query = $query . $prop->getName() . '=' . $value;
                }
            }
        }
        /* // 未定義のpublicプロパティ
        $publics = call_user_func('get_object_vars', $instance);
        if (!$publics) {
            $publics = [];
        }
        foreach ($publics as $name => $val) {
            if (is_null($val))
            {
                continue;
            }
            if (is_array($val))
            {
                $val = implode(",", $val);
            }

            if ($isFirst)
            {
                $isFirst = false;
            }
            else
            {
                $query = $query . '&';
            }
            $query = $query . $name . '=' . $val;
        }*/
        return $query;
    }

    public function setOnlyEREnabled($bool) {
    	$this->kokaisaki_cd = $bool ? 'E200' : null;
    }

    /**
     * ２次広告自動公開制御
     * falseしか指定できません。
     * @param string $bool
     */
    public function setNijiKokokuJidoKokaiFl($bool)
    {
    	if (! $bool) $this->niji_kokoku_jido_kokai_fl = 'false';
    }

    /**
     * エンド向け仲介手数料不要の物件だけ表示する
     * @param $bool
     */
    public function setEndMukeEnabled($bool) {
    	if ($bool) $this->end_muke_chukai_tesuryo_fuyo_fl = 'true';
    }

    /**
     * ２次広告物件（他社物件）のみ抽出
     * @param $bool
     */
    public function setOnlySecond($bool) {
    	if ($bool) $this->jishatasha_cd = '2';
    }

    /**
     * ２次広告物件除いて（自社物件）抽出
     * @param $bool
     */
    public function setExcludeSecond($bool) {
        if ($bool) $this->jishatasha_cd = '1';
    }

    /**
     * 自社物件/二次広告物件/二次広告物件自動公開 設定
     * @param $jisha_bukken
     * @param $niji_kokoku
     * @param $niji_kokoku_jido_kokai
     */
    public function setKokaiType($jisha_bukken, $niji_kokoku, $niji_kokoku_jido_kokai) {
        if ($niji_kokoku_jido_kokai === '') {
            $niji_kokoku_jido_kokai = '0';
        }
        $ptn = sprintf("%s-%s-%s", $jisha_bukken, $niji_kokoku, $niji_kokoku_jido_kokai);

        switch($ptn) {
            case '0-0-0':
                // dead-route
                break;
            case '1-0-0':
                $this->jishatasha_cd = '1';
                $this->niji_kokoku_jido_kokai_fl = 'false';
                break;
            case '0-1-0':
                $this->jishatasha_cd = '2';
                // $this->niji_kokoku_fl = 'true';
                $this->niji_kokoku_jido_kokai_fl = 'false';
                break;
            case '0-0-1':
                // $this->niji_kokoku_fl = 'false';
                $this->niji_kokoku_jido_kokai_fl = 'true';
                break;
            case '1-1-0':
                // $this->niji_kokoku_fl = 'true';
                $this->niji_kokoku_jido_kokai_fl = 'false';
                break;
            case '1-0-1':
                $this->jishatasha_cd = '1';
                //$this->niji_kokoku_jido_kokai_fl = 'true';
                break;
            case '0-1-1':
                // $this->jishatasha_cd = '2';
                $this->niji_kokoku_fl = 'true';
                // $this->niji_kokoku_jido_kokai_fl = 'true';
                break;
            case '1-1-1':
                // $this->niji_kokoku_fl = 'true';
                // $this->niji_kokoku_jido_kokai_fl = 'true';
                break;
        }
    }

    /**
     * 手数料あり
     * @param $arinomi
     * @param $wakarekomi
     */
    public function setSetTesuryo($arinomi, $wakarekomi) {
        if($arinomi) {
            // ありのみ
            $this->tesuryo_cd_not_in = '01,02';
        } else if($wakarekomi) {
            // 分かれも含む
            $this->tesuryo_cd_not_in = '02';
        }
    }

    /**
     * 広告費あり
     * @param $bool
     */
    public function setKokokuhiJokenAri($bool) {
        if ($bool) $this->kokokuhi_sodan_ari_fl = 'true';
    }

    /**
     * 地図検索（不可）フラグ
     * @param $bool
     */
    public function setChizuKensakuFukaFl($bool) {
    	if ($bool) {
            $this->chizu_kensaku_fuka_fl = 'false';
            $this->chizu_hyoji_fuka_kaiin_fl = 'false';
        }
    }

    /**
     * 地図検索表示可能なもののみ
     * @param $bool
     */
    public function setChizuHyojiKaFl($bool){
        if ($bool) {
            $this->chizu_hyoji_ka_fl = 'true';
        }else{
            $this->chizu_hyoji_ka_fl = 'false';
        }
    }

    /**
     * オーナーチェンジフラグ
     * @param $owner_change
     */
    public function setOwnerChangeFl($owner_change) {
		if($owner_change == 1) {
			// オーナーチェンジのみ
			$this->owner_change_fl = true;
		} else if($owner_change == 2) {
			// オーナーチェンジを除く
			$this->owner_change_fl = false;
		}
    }

    /**
     * パラメータの検証を行う。
     */
    public function validate() {
    	// NHP-2307
    	// 「２次広告のみ(jishatasha_cd = '2')」で「２次広告自動公開物件を含める(niji_kokoku_jido_kokai_fl = 'false')」場合に、
    	// niji_kokoku_fl=trueを指定して、「jishatasha_cd=2」はクエリから除外する。
        if ($this->jishatasha_cd && $this->jishatasha_cd == '2' &&
    			! $this->niji_kokoku_jido_kokai_fl) {
            $this->jishatasha_cd = null;
            $this->niji_kokoku_fl = 'true';
        }
    }

    /**
     *
     * @param Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract $searchFilter
     * @param Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract $facetSearchFilter
     * @param boolean $withFacet
     */
    public function setSearchFilter( $searchFilter, $facetSearchFilter = null, $isSpecial=false,$isSpecialShumokuSort = false) {

    	$parser = new BApi\SearchFilterTranslator($isSpecial);

        if($isSpecial) {

            // 中分類種目が渡されているかをチェックする
            $shumoku_category = null;
            foreach ($searchFilter->pickDesiredCategories() as $category) {
                if($category->category_id == 'shumoku') {
                    $shumoku_category = $category;
                    break;
                }
            }
            if(!is_null($shumoku_category)) {
                $cnt = count($shumoku_category->items);
                $selectedItemIds = [];
                for($sno = 0; $sno < $cnt; $sno++) {
                    $item = $shumoku_category->items[ $sno ];

                    if($item->item_value == 1) {
                        $selectedItemIds[] = $item->item_id;
                    }
                }

                foreach($selectedItemIds as $id) {
                    $aliasIds = $parser->getShumokuAliasMap( $id );
                    if(!is_null($aliasIds)) {
                        $cnt = count($shumoku_category->items);
                        for($sno = 0; $sno < $cnt; $sno++) {
                            $item = $category->items[ $sno ];
                            if(in_array($item->item_id, $aliasIds)) {
                                $item->item_value = $item->getParsedValue();
                            }
                        }
                    }
                }
            }
            
			$bukken_shumoku_cd_array = [];
			if(isset($this->{'csite_bukken_shumoku_cd'})) {
				if(is_array($this->{'csite_bukken_shumoku_cd'})) {
					$bukken_shumoku_cd_array = $this->{'csite_bukken_shumoku_cd'};
				} else {
					$bukken_shumoku_cd_array[] = $this->{'csite_bukken_shumoku_cd'};
				}

				for($bno = count($bukken_shumoku_cd_array) - 1; $bno >= 0; $bno--) {
					$scode =  Estate\TypeList::getInstance()->getByShumokuCode($bukken_shumoku_cd_array[ $bno ]);
					$sFilter = new SearchFilter\Special();
					$sFilter->loadEnables($scode);
					$sFilter->asMaster();
					if(isset($sFilter->categories[0]->category_id) && $sFilter->categories[0]->category_id == 'shumoku') {
						// 中分類を持つ場合
						unset($bukken_shumoku_cd_array[ $bno ]);
					}
				}
				$bukken_shumoku_cd_array = array_values($bukken_shumoku_cd_array);
			}
            foreach ($parser->toSearchParam($searchFilter, $isSpecial && $isSpecialShumokuSort) as $name => $value) {
                if ($name == 'or') {
                    $value  = array_merge($this->{$name}, $value);
                }
                $this->{$name} = $value;
            }
			if(!empty($bukken_shumoku_cd_array)) {

				if(is_array($this->{'csite_bukken_shumoku_cd'})) {
					$this->{'csite_bukken_shumoku_cd'} = array_merge($this->{'csite_bukken_shumoku_cd'}, $bukken_shumoku_cd_array);
				} else {
					$bukken_shumoku_cd_array[] = $this->{'csite_bukken_shumoku_cd'};
					$this->{'csite_bukken_shumoku_cd'} = $bukken_shumoku_cd_array;
				}
				$this->{'csite_bukken_shumoku_cd'} = array_values(array_unique($this->{'csite_bukken_shumoku_cd'}));
			}
            if ($isSpecialShumokuSort) {
                $parser = new BApi\SearchFilterTranslator();
                foreach ($parser->toSearchParam($searchFilter) as $name => $value) {
                    if ($name != 'csite_chubunrui_shumoku_cd' && $name != 'joi_shumoku_cd') continue;
                    $this->{$name} = array_unique($value);
                }

            }
        } else {
            foreach ($parser->toSearchParam($searchFilter) as $name => $value) {
                $this->{$name} = $value;
            }
        }

        if ($facetSearchFilter) {
        	$facet = new BApi\SearchFilterFacetTranslator();
            $this->facets = $facet->toFacetParam($facetSearchFilter);
        }
    }

    public function setId($bukken_id)
    {
        $this->id = $bukken_id;
    }

    public function setOsusumeKokaiFl($osusume_kokai_fl)
    {
        $this->osusume_kokai_fl = $osusume_kokai_fl;
    }

    public function get($name) {
        return $this->{$name};
    }
}
