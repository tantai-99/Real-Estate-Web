<?php
namespace Modules\V1api\Services\BApi;

use Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract;

class SearchFilterTranslator
{
    const VALUE_TRUE = 1;
    const VALUE_FALSE = 0;

    /**
     * @var array
     */
    protected $_desiredMap;

    /**
     * @var array
     */
    protected $_particularMap;

    protected $_shumokuAliasMap;

    /**
     *
     * @var SearchFilterAbstract $searchFilter
     */
    protected $_searchFilter;

    protected $_shumukuSortFlg = false;
    /**
     * 物件API検索パラメータに変換する
     */
    public function __construct($isSpecial = false) {
        $this->_initDesiredMap($isSpecial);
        $this->_initParticularMap();

        if($isSpecial) {
            $this->_initShumokuAliasMap();
        }
    }

    /**
     *
     * @param SearchFilterAbstract $searchFilter
     */
    public function toSearchParam(SearchFilterAbstract $searchFilter, $flag = false) {
        $this->_searchFilter = $searchFilter;
        $this->_shumukuSortFlg = $flag;

        $params = [];

        // 希望条件
        foreach ($searchFilter->pickDesiredCategories() as $category) {
            $methodName = '_toSearchParam' . pascalize($category->category_id);
            // 個別処理
            if (method_exists($this, $methodName)) {
                $_params = $this->{$methodName}($category);
            }
            // 共通処理
            else {
                $_params = $this->_toSearchParamDefaultDesired($category);
            }
            if ($_params) {
                if(isset($params['or']) && isset($_params['or'])) {
                    $params['or'] = array_merge($params['or'], $_params['or']);
                    unset($_params['or']); 
                }
                $params = array_merge($params, $_params);
            }
        }

        // こだわり条件
        foreach ($searchFilter->pickParticularCategories() as $category) {
            foreach ($category->items as $item) {
                if (!$item->item_value) {
                    continue;
                }

                $name = $category->category_id.'__'.$item->item_id;
                $searchParams = $this->_getParticularParam($category->category_id, $item->item_id);

                $params[ $searchParams[0] ][] = $searchParams[1];
            }
        }
//error_log(print_r($params,1));
        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamKakaku($category) {
        // 価格
        $priceItems = [];
        foreach ([1, 2] as $itemId) {
            if ($item = $category->getItem($itemId)) {
                $priceItems[] = $item;
            }
        }
        $params = $this->_toListDesiredParam($category, $priceItems, function ($searchParams, $itemValue, $displayValue) {
            return $this->_kakakuToSearchValue($displayValue);
        });

        // その他チェックアイテム
        $items = [];
        foreach ($category->items as $item) {
            if ($item->item_id == 1 || $item->item_id == 2) {
                continue;
            }
            $items[] = $item;
        }

        if ($items) {
            $params = array_merge($params, $this->_toDesiredParamKakaku($category, $items));
        }

        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamKeiyakuJoken($category) {
        return $this->_toListDesiredParam($category, $category->items, function ($searchParams, $itemValue, $displayValue) {
            switch ($itemValue) {
                case 10:
                    if ($displayValue == '短期貸し物件') {
                        return ['tanki_kashi_bukken_fl', static::VALUE_TRUE];
                    } else {
                        return ['teiki_shakka_fl', static::VALUE_FALSE];
                    }
                case 30:
                    return ['teiki_shakka_fl', static::VALUE_TRUE];
                case 40:
                    return ['tanki_kashi_bukken_fl', static::VALUE_TRUE];
                default:
                    return;
            }
        });
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamMadori($category) {
        $item = $category->getItem(1);
        if (!$item) {
            return;
        }

        $optionsModel = $item->getOptionModel();

        // CMS設定されている場合（特集）且つなにも選択されていない場合は
        // CMSで設定された項目全て選択された状態とする
        if ($this->_searchFilter->isParsed() && !$item->item_value) {
            $itemValues = $item->getParsedValue();
        }
        else {
            $itemValues = $item->item_value;
        }

        $searchParams = $this->_getDesiredParam($category->category_id, $item->item_id);
        $params = [];
        foreach ($itemValues as $itemValue) {
            if ($displayValue = $optionsModel->get($itemValue)) {
                $params[ $searchParams[0] ][] = $this->_madoriToSearchValue($displayValue);
            }
        }
        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamMenseki($category) {
        return $this->_toListDesiredParam($category, $category->items, function ($searchParams, $itemValue, $displayValue) {
            return $this->_stringToSearchValue($displayValue);
        });
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamEkiTohoFun($category) {
        return $this->_toListDesiredParam($category, $category->items, function ($searchParams, $itemValue, $displayValue) {
            return 'lte:'.$this->_stringToSearchValue($displayValue);
        });
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamRimawari($category) {
        return $this->_toListDesiredParam($category, $category->items, function ($searchParams, $itemValue, $displayValue) {
            return $this->_stringToSearchValue($displayValue);
        });
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamChikunensu($category) {
        $itemCnt = count($category->items);
        $isSpecialSearch = $this->_searchFilter->isParsed();

        $params = [];
        for($ino = 0; $ino < $itemCnt; $ino++) {
            $chikunensuItem = $category->items[$ino];
            if (!$chikunensuItem) {
                continue;
            }
            $searchParam = $this->_getDesiredParam($category->category_id, $chikunensuItem->item_id)[0];
            switch($chikunensuItem->item_id) {
                case 1:
					$specialCmsValue = [];
                    if($isSpecialSearch) {
						$specialCmsValue = array_merge($specialCmsValue, $chikunensuItem->getParsedValue());
                    }
                    $specialCmsValue = array_merge($specialCmsValue, $specialCmsValue = $chikunensuItem->item_value);
					$options = $chikunensuItem->getOptions();
					if(count($specialCmsValue)) {
						foreach($specialCmsValue as $val) {
							$displayValue = $options[ $val ];
                            switch ($displayValue) {
                                case '指定なし':
									break;
                                case '新築':
                                    $params[ $searchParam[0] ] = '1';
                                    break;
                                case '新築を除く':
                                    $params[ $searchParam[0] ] = '2';
                                    break;
                                case '築後未入居':
                                    $params[ $searchParam[1] ] = '1';
                                    break;
                                default:
                                    $params[ $searchParam[2] ] = $this->_stringToSearchValue($displayValue);
                                    break;
                            }
						}
					}
                    break;
                case 2: // 築年数(以上): 特集のみ
                    if($isSpecialSearch) {
                        $specialCmsValue = $chikunensuItem->getParsedValue();
                        $params[ $searchParam[0] ] = $specialCmsValue;
                    } else {
                        $specialCmsValue = $chikunensuItem->item_value;
                        if($specialCmsValue != '0' && !empty($specialCmsValue)) {
                            $params[ $searchParam[0] ] = $specialCmsValue;
                        }
                    }
                    break;
            }
        }

		if(isset($params[ $searchParam[0] ]) && $params[ $searchParam[0] ] == '1' && isset($searchParam[2]) && isset($params[$searchParam[2]])) {
            unset($params[ $searchParam[2] ]);
        }
		// NHP-4992 
		if( isset($params[ $searchParam[0] ]) && $params[ $searchParam[0] ] == '1'
		 && isset($searchParam[1]) && isset($params[ $searchParam[1] ]) && $params[ $searchParam[1] ] == '1') {
			// or=shinchiku_chuko_cd:1;chikugo_minyukyo_fl:true 
			unset($params[ $searchParam[0] ]);
			unset($params[ $searchParam[1] ]);
			$params['or'][] = urlencode('shinchiku_chuko_cd:1;chikugo_minyukyo_fl:true');
		}
        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamJohoKokai($category) {
        return $this->_toListDesiredParam($category, $category->items, function ($searchParams, $itemValue, $displayValue) {
            switch ($itemValue) {
                case '10':
//                     return ['or', 'B100:0'];
//                  return ['or', 'AND(joho_kokai:B100:0)%3bAND(joho_kokai:E200:0)'];
                    return ['csite_kokai_date_within', '0'];
                case '20':
//                     return ['or', 'B100:3'];
//                  return ['or', 'AND(joho_kokai:B100:3)%3bAND(joho_kokai:E200:3)'];
                    return ['csite_kokai_date_within', '3'];
                case '30':
//                     return 'B100:7';
//                  return ['or', 'AND(joho_kokai:B100:7)%3bAND(joho_kokai:E200:7)'];
                    return ['csite_kokai_date_within', '7'];
                default:
                    return;
            }
        });
    }

    /** リフォームリノベーションの検索条件を作成する（かなり特殊な対応）
     *  リフォームリノベーションの選択値
     *   　・フロント(flg)
     * 　　　　 指定なし
     * 　　　　 リフォーム・リノベーション
     *　　　・特集設定(radio)
     * 　　　　　指定なし
     * 　　　　　リフォーム・リノベーション
     * 　　　　　リフォームのみ
     * 　　　　　リノベーションのみ
     *
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamReformRenovation($category) {

        // 「リフォームリノベーション」アイテム取得。アイテムは１つだけ。
        $refoRinoItem = $category->getItem(1);
        if (!$refoRinoItem) {
            return;
        }

        // 特集検索かどうか
        $isSpecialSearch = $this->_searchFilter->isParsed();

        // リフォームリノベーションのフロントの選択値
        $fromtItemValue = $refoRinoItem->item_value;

        // リフォームリノベーションのCMS設定値
        $specialCmsValue    = $refoRinoItem->getParsedValue();

        // リフォームリノベーションの物件APIパラメータ群
        $searchParams = $this->_getDesiredParam($category->category_id, $refoRinoItem->item_id);

        $itemValue = 0;

        // 特集検索で、かつ、特集CMS設定「リフォーム・リノベーション」で何かしら条件設定している場合、特集の設定値を優先させる
        if( $isSpecialSearch && $specialCmsValue ){
            $itemValue = $specialCmsValue;

        // 通常検索の場合、または、特集検索で特集CMS設定で「指定なし」が選択されている場合、フロントの選択値を使う
        }else{
            $itemValue = $fromtItemValue;
        }

        $params=[];
        switch ($itemValue) {
            // リフォーム・リノベーション
            case '1':
                $params[$searchParams[0][0]] = $searchParams[0][1];
                break;
            //リフォームのみ
            case '10':
                $params[$searchParams[1][0]] = $searchParams[1][1];
                break;
            // リノベーションのみ
            case '20':
                $params[$searchParams[2][0]] = $searchParams[2][1];
                break;
            // 指定なし
            default:
                break;
        }

        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     */
    protected function _toSearchParamDefaultDesired($category) {
        return $this->_toDesiredParam($category, $category->items);
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param array $items
     * @param function $paramFunc
     */
    protected function _toListDesiredParam($category, $items, $paramFunc) {
        $params = [];
        // 特集ページでユーザーが検索条件を選択していない場合はCMS特集設定の設定値で検索する
        // $useParsedValue = ($this->_searchFilter->isParsed() && $this->_searchFilter->isValueEmpty());
        foreach ($items as $item) {
            // if ($useParsedValue) {
            //     $itemValue = $item->getParsedValue();
            // }
            // else {
            //     $itemValue = $item->item_value;
            // }
            // クライアントから送られて来たデータを優先しなければcmsのデータを設定
            $itemValue = $item->item_value;
            if (empty($itemValue)) {
                $itemValue = $item->getParsedValue();
            }

            if (!$itemValue) {
                continue;
            }

            if ($displayValue = $item->getOptionModel()->get($itemValue)) {
                $searchParams = $this->_getDesiredParam($category->category_id, $item->item_id);
                $param = $paramFunc($searchParams, $itemValue, $displayValue);
                if (!$param) {

                }
                elseif (is_array($param)) {
                    $params[$param[0]] = $param[1];
                }
                else {
                    $params[$searchParams[0]] = $param;
                }
            }
        }
        return $params;
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param array $items
     */
    protected function _toDesiredParamKakaku($category, $items) {
        $params = [];

        // 特集の場合、CMSで設定されたアイテムのみ使用する
        //   ↑仕様変更（2016年9月）
        if ($this->_searchFilter->isParsed()) {
            // 特集の場合、カテゴリ未選択の場合は、
            // CMSで設定された全てのアイテムを選択したものとする
            $selectAllItems = true;
            $finalItems = [];
            foreach ($items as $item) {
                //if (!$item->isLoaded()) {
                $finalItems[] = $item;
                if ($item->item_value) {
                    $selectAllItems = false;
                }
                //}
            }
        }
        else {
            $selectAllItems = false;
            $finalItems = $items;
        }

        foreach ($finalItems as $item) {
            if (!$selectAllItems && !$item->item_value && !$item->getParsedValue() ) {
                continue;
            }

            foreach ($this->_getDesiredParam($category->category_id, $item->item_id) as $searchParam) {
                if (isset($searchParam[2]) && $searchParam[2] == 'array') {
                    $params[ $searchParam[0] ][] = $searchParam[1];
                }
                else {
                    $params[ $searchParam[0] ] = $searchParam[1];
                }
            }
        }
        return $params;
    }


    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param array $items
     */
    protected function _toDesiredParam($category, $items) {
        $params = [];

        // 特集の場合、CMSで設定されたアイテムのみ使用する
        //   ↑仕様変更（2016年9月）
        if ($this->_searchFilter->isParsed()) {
            // 特集の場合、カテゴリ未選択の場合は、
            // CMSで設定された全てのアイテムを選択したものとする
            $selectAllItems = true;
            $finalItems = [];
            foreach ($items as $item) {
                //if (!$item->isLoaded()) {
                $finalItems[] = $item;
                if ($item->item_value) {
                    $selectAllItems = false;
                }
                if ($this->_shumukuSortFlg && $category->category_id == 'shumoku') {
                    $selectAllItems = true;
                }
                //}
            }
        }
        else {
            $selectAllItems = false;
            $finalItems = $items;
        }

        foreach ($finalItems as $item) {
            if (!$selectAllItems && !$item->item_value || $item->item_flg) {
                continue;
            }


            foreach ($this->_getDesiredParam($category->category_id, $item->item_id) as $searchParam) {
                if (isset($searchParam[2]) && $searchParam[2] == 'array') {
                    $params[ $searchParam[0] ][] = $searchParam[1];
                }
                else {
                    $params[ $searchParam[0] ] = $searchParam[1];
                }
            }
        }
        return $params;
    }


    /**
     * 価格を物件API検索パラメータ値に変換する
     * @param string $value
     * @return number
     */
    protected function _kakakuToSearchValue($value) {
        // 数値を取得
        preg_match('/(\d+\.)?\d+/', $value, $matched);
        $num = (float)$matched[0];
        if (preg_match('/万/u', $value)) {
            $num *= 10000;
        }
        return $num;
    }

    /**
     * 間取りを物件APIパラメータ値に変換する
     * @param string $value
     * @return string
     */
    protected function _madoriToSearchValue($value) {
        $madriMap = [
            'R' => '01',
            'K' => '02',
            'DK'=> '03',
            'LK'=> '04',
            'LDK'=> '05',
            'SK'=> '06',
            'SDK'=> '07',
            'SLK'=> '08',
            'SLDK'=> '09',
        ];
        preg_match('/(\d+)([a-zA-Z]+)(以上|以下)?/u', $value, $matched);

        $result = $matched[1].':'.$madriMap[ $matched[2] ];
        $match3 = '';
        if (isset($matched[3])) {
            if ($matched[3] == '以上') {
                $result = 'gte:' . $result;
                $match3 = 'gte:';
            }
            else {
                $result = 'lte:' . $result;
                $match3 = 'lte:';
            }
        }
        //検索条件をまとめる
        $sameType = [
            'K' => ['SK']
            ,'DK' => ['LK','SDK','SLK']
            ,'LDK' => ['SLDK']
        ];
        if (isset($sameType[$matched[2]])) {
            foreach($sameType[$matched[2]] as $value){
                $result .= sprintf(',%s%s:%s',$match3, $matched[1], $madriMap[$value]);
            }
        }
        return $result;
    }

    /**
     * 面積を物件API検索パラメータ値に変換する
     * @param string $value
     * @return number
     */
    protected function _stringToSearchValue($value) {
        // 数値を取得
        preg_match('/(\d+\.)?\d+/', $value, $matched);
        $num = (float)$matched[0];
        return $num;
    }

    protected function _initDesiredMap($isSpecial) {

        if($isSpecial == true) {
            $params['shumoku']['13'][]=['csite_bukken_shumoku_cd', '0508', 'array'];    // 売その他 アパート一括
            $params['shumoku']['14'][]=['csite_bukken_shumoku_cd', '0506', 'array'];    // 売その他 マンション一括
            $params['shumoku']['15'][]=['csite_bukken_shumoku_cd', '0504', 'array'];    // 売その他 ビル一括
            $params['shumoku']['16'][]=['csite_bukken_shumoku_cd', '0599', 'array'];    // 売その他 その他

            $params['shumoku']['17'][]=['csite_bukken_shumoku_cd', '0602', 'array'];    // 賃貸 アパート
            $params['shumoku']['18'][]=['csite_bukken_shumoku_cd', '0601', 'array'];    // 賃貸 マンション
            $params['shumoku']['19'][]=['csite_bukken_shumoku_cd', '5101', 'array'];    // 賃貸 一戸建て

            $params['shumoku']['20'][]=['csite_bukken_shumoku_cd', '0913', 'array'];    // 貸その他 ビル
            $params['shumoku']['21'][]=['csite_bukken_shumoku_cd', '0905', 'array'];    // 貸その他 倉庫
            $params['shumoku']['22'][]=['csite_bukken_shumoku_cd', '0904', 'array'];    // 貸その他 工場
            $params['shumoku']['23'][]=['csite_bukken_shumoku_cd', '0899', 'array'];    // 貸その他 その他

            // 賃貸用種目詳細(NHP-4586追加分)
            $params['shumoku']['24'][]=['csite_bukken_shumoku_cd', '0703', 'array'];    // 賃貸 テラスハウス
            $params['shumoku']['25'][]=['csite_bukken_shumoku_cd', '0701', 'array'];    // 賃貸 タウンハウス

            // 貸ビル・貸倉庫・その他 用種目詳細(NHP-4586追加分)
            $params['shumoku']['26'][]=['csite_bukken_shumoku_cd', '0921', 'array']; // 貸その他 作業所
            $params['shumoku']['27'][]=['csite_bukken_shumoku_cd', '0907', 'array']; // 貸その他 一括貸マンション
            $params['shumoku']['28'][]=['csite_bukken_shumoku_cd', '0912', 'array']; // 貸その他 一括貸アパート
            $params['shumoku']['29'][]=['csite_bukken_shumoku_cd', '0909', 'array']; // 貸その他 寮
            $params['shumoku']['30'][]=['csite_bukken_shumoku_cd', '0908', 'array']; // 貸その他 旅館
            $params['shumoku']['31'][]=['csite_bukken_shumoku_cd', '0910', 'array']; // 貸その他 別荘
            $params['shumoku']['32'][]=['csite_bukken_shumoku_cd', '0914', 'array']; // 貸その他 ホテル
            $params['shumoku']['33'][]=['csite_bukken_shumoku_cd', '0915', 'array']; // 貸その他 モーテル
            $params['shumoku']['34'][]=['csite_bukken_shumoku_cd', '0916', 'array']; // 貸その他 医院
            $params['shumoku']['35'][]=['csite_bukken_shumoku_cd', '0917', 'array']; // 貸その他 ガソリンスタンド
            $params['shumoku']['36'][]=['csite_bukken_shumoku_cd', '0918', 'array']; // 貸その他 特殊浴場
            $params['shumoku']['37'][]=['csite_bukken_shumoku_cd', '0919', 'array']; // 貸その他 サウナ
            $params['shumoku']['38'][]=['csite_bukken_shumoku_cd', '0920', 'array']; // 貸その他 保養所
            $params['shumoku']['61'][]=['csite_bukken_shumoku_cd', '0906', 'array']; // 貸その他 貸家

            // 戸建て用 種目詳細(NHP-4586追加分)
            $params['shumoku']['39'][]=['csite_bukken_shumoku_cd', '5102', 'array']; // 売戸建て 一戸建て
            $params['shumoku']['40'][]=['csite_bukken_shumoku_cd', '5103', 'array']; // 売戸建て 建築条件付き土地

            // 売ビル・売倉庫・売工場・その他 用種目詳細(NHP-4586追加分)
            $params['shumoku']['41'][]=['csite_bukken_shumoku_cd', '5104', 'array']; // 売その他 戸建住宅（オーナーチェンジのみ）
            $params['shumoku']['42'][]=['csite_bukken_shumoku_cd', '5105', 'array']; // 売その他 テラスハウス（オーナーチェンジのみ）
            $params['shumoku']['43'][]=['csite_bukken_shumoku_cd', '5106', 'array']; // 売その他 マンション（オーナーチェンジのみ）
            $params['shumoku']['44'][]=['csite_bukken_shumoku_cd', '5107', 'array']; // 売その他 公団（オーナーチェンジのみ）
            $params['shumoku']['45'][]=['csite_bukken_shumoku_cd', '5108', 'array']; // 売その他 公社（オーナーチェンジのみ）
            $params['shumoku']['46'][]=['csite_bukken_shumoku_cd', '5109', 'array']; // 売その他 タウンハウス（オーナーチェンジのみ）
            $params['shumoku']['47'][]=['csite_bukken_shumoku_cd', '0505', 'array']; // 売その他 工場
            $params['shumoku']['48'][]=['csite_bukken_shumoku_cd', '0507', 'array']; // 売その他 倉庫
            $params['shumoku']['49'][]=['csite_bukken_shumoku_cd', '0509', 'array']; // 売その他 寮
            $params['shumoku']['50'][]=['csite_bukken_shumoku_cd', '0510', 'array']; // 売その他 旅館
            $params['shumoku']['51'][]=['csite_bukken_shumoku_cd', '0511', 'array']; // 売その他 ホテル
            $params['shumoku']['52'][]=['csite_bukken_shumoku_cd', '0512', 'array']; // 売その他 別荘
            $params['shumoku']['53'][]=['csite_bukken_shumoku_cd', '0515', 'array']; // 売その他 モーテル
            $params['shumoku']['54'][]=['csite_bukken_shumoku_cd', '0516', 'array']; // 売その他 医院
            $params['shumoku']['55'][]=['csite_bukken_shumoku_cd', '0517', 'array']; // 売その他 ガソリンスタンド
            $params['shumoku']['56'][]=['csite_bukken_shumoku_cd', '0518', 'array']; // 売その他 特殊浴場
            $params['shumoku']['57'][]=['csite_bukken_shumoku_cd', '0519', 'array']; // 売その他 サウナ
            $params['shumoku']['58'][]=['csite_bukken_shumoku_cd', '0520', 'array']; // 売その他 保養所
            $params['shumoku']['59'][]=['csite_bukken_shumoku_cd', '0521', 'array']; // 売その他 作業所
            $params['shumoku']['60'][]=['csite_bukken_shumoku_cd', '0522', 'array']; // 売その他 駐車場
        } else {
            $params['shumoku']['13'][]=['csite_chubunrui_shumoku_cd', '6601', 'array'];
            $params['shumoku']['14'][]=['csite_chubunrui_shumoku_cd', '6602', 'array'];
            $params['shumoku']['15'][]=['csite_chubunrui_shumoku_cd', '6603', 'array'];
            $params['shumoku']['16'][]=['csite_chubunrui_shumoku_cd', '6604', 'array'];
            $params['shumoku']['17'][]=['csite_chubunrui_shumoku_cd', '7101', 'array'];
            $params['shumoku']['18'][]=['csite_chubunrui_shumoku_cd', '7102', 'array'];
            $params['shumoku']['19'][]=['csite_chubunrui_shumoku_cd', '7103', 'array'];
            $params['shumoku']['20'][]=['csite_chubunrui_shumoku_cd', '7601', 'array'];
            $params['shumoku']['21'][]=['csite_chubunrui_shumoku_cd', '7602', 'array'];
            $params['shumoku']['22'][]=['csite_chubunrui_shumoku_cd', '7603', 'array'];
            $params['shumoku']['23'][]=['csite_chubunrui_shumoku_cd', '7604', 'array'];
            // $params['shumoku']['39'][]=['csite_chubunrui_shumoku_cd', '6201', 'array'];
            // $params['shumoku']['40'][]=['csite_chubunrui_shumoku_cd', '6101', 'array'];
            $params['shumoku']['39'][]=['joi_shumoku_cd', '02', 'array'];
            $params['shumoku']['40'][]=['joi_shumoku_cd', '01', 'array'];

            $params['shumoku']['26'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 作業所
            $params['shumoku']['27'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 一括貸マンション
            $params['shumoku']['28'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 一括貸アパート
            $params['shumoku']['29'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 寮
            $params['shumoku']['30'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 旅館
            $params['shumoku']['31'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 別荘
            $params['shumoku']['32'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 ホテル
            $params['shumoku']['33'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 モーテル
            $params['shumoku']['34'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 医院
            $params['shumoku']['35'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 ガソリンスタンド
            $params['shumoku']['36'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 特殊浴場
            $params['shumoku']['37'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 サウナ
            $params['shumoku']['38'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 保養所
            $params['shumoku']['61'][]=['csite_chubunrui_shumoku_cd', '7604', 'array']; // 貸その他 貸家

            $params['shumoku']['41'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 戸建住宅（オーナーチェンジのみ）
            $params['shumoku']['42'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 テラスハウス（オーナーチェンジのみ）
            $params['shumoku']['43'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 マンション（オーナーチェンジのみ）
            $params['shumoku']['44'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 公団（オーナーチェンジのみ）
            $params['shumoku']['45'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 公社（オーナーチェンジのみ）
            $params['shumoku']['46'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 タウンハウス（オーナーチェンジのみ）
            $params['shumoku']['47'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 工場
            $params['shumoku']['48'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 倉庫
            $params['shumoku']['49'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 寮
            $params['shumoku']['50'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 旅館
            $params['shumoku']['51'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 ホテル
            $params['shumoku']['52'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 別荘
            $params['shumoku']['53'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 モーテル
            $params['shumoku']['54'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 医院
            $params['shumoku']['55'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 ガソリンスタンド
            $params['shumoku']['56'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 特殊浴場
            $params['shumoku']['57'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 サウナ
            $params['shumoku']['58'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 保養所
            $params['shumoku']['59'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 作業所
            $params['shumoku']['60'][]=['csite_chubunrui_shumoku_cd', '6604', 'array']; // 売その他 駐車場
        }


        // 個別処理
        $params['kakaku']['1'][]='kakaku_from';
        $params['kakaku']['2'][]='kakaku_to';

        $params['kakaku']['3'][]=['kanrihi_komi_fl', self::VALUE_TRUE];
        $params['kakaku']['3'][]=['zappi_komi_fl', self::VALUE_TRUE];
        $params['kakaku']['3'][]=['kyoekihi_komi_fl', self::VALUE_TRUE];
        $params['kakaku']['4'][]=['chushajo_dai_komi_fl', self::VALUE_TRUE];
        $params['kakaku']['5'][]=['reikin_nashi_fl', self::VALUE_TRUE] ;
        $params['kakaku']['6'][]=['shikikin_nashi_fl', self::VALUE_TRUE];
        $params['kakaku']['6'][]=['hoshokin_nashi_fl', self::VALUE_TRUE];

        // 個別処理
        $params['keiyaku_joken']['1'][]='備考参照';

        // 個別処理
        $params['rimawari']['1'][]='sotei_rimawari_from';
        $params['rimawari']['2'][]='sotei_rimawari_to';

        // 個別処理
        $params['torihiki_taiyo']['1'][]='torihiki_taiyo_cd';

        // 個別処理
        $params['madori']['1'][]='madori_cd';

        // 個別処理
        $params['menseki']['1'][]='tatemono_ms_from';
        $params['menseki']['2'][]='tochi_ms_from';
        $params['menseki']['3'][]='tatemono_ms_to';
        $params['menseki']['4'][]='tochi_ms_to';

        $params['tatemono_kozo']['1'][]=['tatemono_kozo_cd', '1100', 'array'];
        $params['tatemono_kozo']['2'][]=['tatemono_kozo_cd', '1101', 'array'];
        $params['tatemono_kozo']['3'][]=['tatemono_kozo_cd', '01', 'array'];
        $params['tatemono_kozo']['4'][]=['tatemono_kozo_cd', '1102', 'array'];

        $params['saiteki_yoto']['1'][]=['saiteki_yoto_cd', '01'];
        $params['saiteki_yoto']['2'][]=['saiteki_yoto_cd_not_in', '01'];

        // 個別処理
        $params['eki_toho_fun']['1'][]='eki_toho_fun';

        // 個別処理
        $params['chikunensu']['1'][]=['shinchiku_chuko_cd', 'chikugo_minyukyo_fl', 'chikunensu_to'];
        $params['chikunensu']['2'][]=['chikunensu_from'];

        // リフォーム・リノベーションに、reform_flとrenovation_flを追加
        $params['reform_renovation']['1'][]=['reform_renovation_ari_fl', self::VALUE_TRUE];
        $params['reform_renovation']['1'][]=['reform_fl', self::VALUE_TRUE];
        $params['reform_renovation']['1'][]=['renovation_fl', self::VALUE_TRUE];

        // 個別処理
        $params['reformable_parts']['1'][]=['reform_renovation_mizumawari_cd', 'reform_renovation_mizumawari_sonota_ari_fl'];
        $params['reformable_parts']['2'][]=['reform_renovation_naiso_cd', 'reform_renovation_naiso_sonota_ari_fl'];
        $params['reformable_parts']['3'][]=['dummy', 'reform_renovation_sonota_ari_fl'];

        $params['open_room']['1'][]=['open_house_fl', self::VALUE_TRUE];
        $params['open_house']['1'][]=['open_house_fl', self::VALUE_TRUE];
        $params['genchi_hanbaikai']['1'][]=['open_house_fl', self::VALUE_TRUE];

        // 個別処理
        $params['joho_kokai']['1'][]='joho_kokai';

        $params['pro_comment']['1'][]=['ippan_kokai_message_ari_fl', self::VALUE_TRUE];
        $params['pro_comment']['2'][]=['ippan_message_shosai_ari_fl', self::VALUE_TRUE];

        $params['image']['1'][]=['madorizu_ari_fl', self::VALUE_TRUE];
        $params['image']['2'][]=['madorizu_ari_fl', self::VALUE_TRUE];
        $params['image']['3'][]=['madorizu_ari_fl', self::VALUE_TRUE];
        $params['image']['4'][]=['shashin_ari_fl', self::VALUE_TRUE];
        $params['image']['5'][]=['csite_panorama_kokai_fl', self::VALUE_TRUE]; // パノラマの検索条件を変更(panorama_movie_ari_fl → csite_panorama_kokai_fl)
        $this->_desiredMap = $params;
    }

    // こだわり条件コード版
    protected function _initParticularMap() {
        $params['kitchen']['1']=['kodawari_joken_cd','01001'];
        $params['kitchen']['2']=['kodawari_joken_cd','01002'];
        $params['kitchen']['3']=['kodawari_joken_cd','01003'];
        $params['kitchen']['4']=['kodawari_joken_cd','01004'];
        $params['kitchen']['5']=['kodawari_joken_cd','01005'];
        $params['kitchen']['6']=['kodawari_joken_cd','01006'];
        $params['kitchen']['7']=['kodawari_joken_cd','01007'];

        $params['kitchen']['8']=['kodawari_joken_cd','01008'];
        $params['kitchen']['9']=['kodawari_joken_cd','01009'];
        $params['kitchen']['10']=['kodawari_joken_cd','01010'];
        $params['kitchen']['11']=['kodawari_joken_cd','01011'];
        $params['kitchen']['12']=['kodawari_joken_cd','01012'];

        $params['kitchen']['13']=['kodawari_joken_cd','01013'];
        $params['kitchen']['14']=['kodawari_joken_cd','01014'];

        $params['kitchen']['16']=['kodawari_joken_cd','01015'];
        $params['kitchen']['17']=['kodawari_joken_cd','01016'];
        $params['kitchen']['18']=['kodawari_joken_cd','01017'];

        $params['kitchen']['19']=['kodawari_joken_cd','01018'];
        $params['kitchen']['20']=['kodawari_joken_cd','01019'];
        $params['kitchen']['21']=['kodawari_joken_cd','01020'];


        $params['bath_toilet']['1']=['kodawari_joken_cd','02001'];
        $params['bath_toilet']['2']=['kodawari_joken_cd','02002'];
        $params['bath_toilet']['3']=['kodawari_joken_cd','02003'];

        $params['bath_toilet']['4']=['kodawari_joken_cd','02004'];
        $params['bath_toilet']['5']=['kodawari_joken_cd','02005'];
        $params['bath_toilet']['6']=['kodawari_joken_cd','02006'];
        $params['bath_toilet']['7']=['kodawari_joken_cd','02007'];
        $params['bath_toilet']['8']=['kodawari_joken_cd','02008'];
        $params['bath_toilet']['9']=['kodawari_joken_cd','02009'];
        $params['bath_toilet']['10']=['kodawari_joken_cd','02010'];

        $params['bath_toilet']['11']=['kodawari_joken_cd','02011'];
        $params['bath_toilet']['12']=['kodawari_joken_cd','02012'];
        $params['bath_toilet']['13']=['kodawari_joken_cd','02013'];

        $params['bath_toilet']['14']=['kodawari_joken_cd','02014'];
        $params['bath_toilet']['15']=['kodawari_joken_cd','02015'];
        $params['bath_toilet']['16']=['kodawari_joken_cd','02016'];
        $params['bath_toilet']['17']=['kodawari_joken_cd','02017'];
        $params['bath_toilet']['18']=['kodawari_joken_cd','02018'];
        $params['bath_toilet']['19']=['kodawari_joken_cd','02019'];
        $params['bath_toilet']['20']=['kodawari_joken_cd','02020'];
        $params['bath_toilet']['21']=['kodawari_joken_cd','02021'];
        $params['bath_toilet']['22']=['setsubi_cd','193']; //追加　シャワールーム
        $params['bath_toilet']['23']=['setsubi_cd','192']; //追加　高温差湯式
        $params['bath_toilet']['24']=['setsubi_cd','195']; //追加　多機能トイレ
        $params['bath_toilet']['25']=['setsubi_cd','199']; //追加 バス（共同）
        $params['bath_toilet']['26']=['setsubi_cd','200']; //追加 シャワールーム（共同）
        $params['bath_toilet']['27']=['setsubi_cd','201']; //追加 トイレ（共同）


        $params['reidanbo']['1']=['kodawari_joken_cd','03001'];

        $params['reidanbo']['2']=['kodawari_joken_cd','03002'];
        $params['reidanbo']['3']=['kodawari_joken_cd','03003'];
        $params['reidanbo']['4']=['kodawari_joken_cd','03004']; //暖房
        $params['reidanbo']['5']=['kodawari_joken_cd','03005'];
        $params['reidanbo']['6']=['kodawari_joken_cd','03006'];
        $params['reidanbo']['7']=['kodawari_joken_cd','03007'];
        $params['reidanbo']['8']=['kodawari_joken_cd','03008'];
        $params['reidanbo']['9']=['kodawari_joken_cd','03009'];
        $params['reidanbo']['10']=['kodawari_joken_cd','03010'];
        $params['reidanbo']['11']=['kodawari_joken_cd','03011'];
        $params['shuno']['1']=['kodawari_joken_cd','04001'];
        $params['shuno']['2']=['kodawari_joken_cd','04002'];

        $params['shuno']['3']=['kodawari_joken_cd','04003'];

        $params['shuno']['4']=['kodawari_joken_cd','04004'];
        $params['shuno']['5']=['kodawari_joken_cd','04005'];
        $params['shuno']['6']=['kodawari_joken_cd','04006'];

        $params['shuno']['7']=['kodawari_joken_cd','04007'];
        $params['shuno']['8']=['kodawari_joken_cd','04008'];
        $params['shuno']['9']=['kodawari_joken_cd','04009'];
        $params['tv_tsusin']['1']=['kodawari_joken_cd','05001'];
        $params['tv_tsusin']['2']=['kodawari_joken_cd','05002'];
        $params['tv_tsusin']['3']=['kodawari_joken_cd','05003'];
        $params['tv_tsusin']['4']=['kodawari_joken_cd','14031']; //インターネット対応
        $params['tv_tsusin']['10']=['kodawari_joken_cd','05004']; //光ファイバー
        $params['tv_tsusin']['5']=['kodawari_joken_cd','05005'];
        $params['tv_tsusin']['6']=['kodawari_joken_cd','05006'];
        $params['tv_tsusin']['7']=['kodawari_joken_cd','05007'];
        $params['tv_tsusin']['8']=['kodawari_joken_cd','05008'];
        $params['tv_tsusin']['9']=['setsubi_cd','190']; //インターネット使用料無料

        $params['security']['1']=['kodawari_joken_cd','06001'];

        $params['security']['2']=['kodawari_joken_cd','06002'];

        $params['security']['3']=['kodawari_joken_cd','06003'];
        $params['security']['4']=['kodawari_joken_cd','06004'];
        $params['security']['5']=['kodawari_joken_cd','06005'];
        $params['security']['6']=['kodawari_joken_cd','06006'];

        $params['security']['7']=['kodawari_joken_cd','06007'];
        $params['security']['8']=['kodawari_joken_cd','06008'];
        $params['security']['9']=['kodawari_joken_cd','06009'];
        $params['security']['10']=['kodawari_joken_cd','06010'];
        $params['security']['11']=['kodawari_joken_cd','06011'];
        $params['security']['12']=['kodawari_joken_cd','06012'];
        $params['security']['13']=['kodawari_joken_cd','06013'];
        $params['ichi']['1']=['kodawari_joken_cd','07001'];
        $params['ichi']['2']=['kodawari_joken_cd','07002'];
        $params['ichi']['3']=['kodawari_joken_cd','07003'];
        $params['ichi']['4']=['kodawari_joken_cd','07004'];
        $params['ichi']['5']=['kodawari_joken_cd','07005'];
        $params['ichi']['6']=['kodawari_joken_cd','07006'];
        $params['ichi']['7']=['kodawari_joken_cd','07007'];
        $params['ichi']['8']=['kodawari_joken_cd','07008'];
        $params['joken']['1']=['kodawari_joken_cd','08001'];
        $params['joken']['2']=['kodawari_joken_cd','08001'];
        $params['joken']['3']=['kodawari_joken_cd','08003'];
        $params['joken']['4']=['kodawari_joken_cd','08004'];
        $params['joken']['5']=['kodawari_joken_cd','08005'];
        $params['joken']['6']=['kodawari_joken_cd','08006'];
        $params['joken']['7']=['kodawari_joken_cd','08007'];
        $params['joken']['8']=['kodawari_joken_cd','08008'];
        $params['joken']['9']=['kodawari_joken_cd','08009'];
        $params['joken']['10']=['kodawari_joken_cd','08010'];
        $params['joken']['11']=['kodawari_joken_cd','08011'];
        $params['joken']['12']=['kodawari_joken_cd','08012'];
        $params['joken']['13']=['kodawari_joken_cd','08013'];
        $params['joken']['14']=['kodawari_joken_cd','08014'];
        $params['joken']['15']=['kodawari_joken_cd','08015'];
        $params['joken']['16']=['kodawari_joken_cd','08016'];
        $params['joken']['17']=['kodawari_joken_cd','08017'];
        $params['joken']['18']=['kodawari_joken_cd','08018'];
        $params['joken']['19']=['kodawari_joken_cd','08019'];
        $params['joken']['20']=['kodawari_joken_cd','08020'];
        $params['joken']['21']=['kodawari_joken_cd','08021'];
        $params['joken']['22']=['kodawari_joken_cd','08022'];
        $params['joken']['23']=['kodawari_joken_cd','08023'];
        $params['joken']['24']=['kodawari_joken_cd','08024'];
        $params['joken']['25']=['kodawari_joken_cd','08025'];
        $params['joken']['26']=['kodawari_joken_cd','08026'];
        $params['joken']['27']=['kodawari_joken_cd','08027'];
        $params['joken']['28']=['kodawari_joken_cd','08028'];
        $params['joken']['29']=['kodawari_joken_cd','08029'];
        $params['joken']['30']=['kodawari_joken_cd','08030'];
        $params['joken']['31']=['kodawari_joken_cd','08031'];
        $params['joken']['32']=['kodawari_joken_cd','08032'];
        $params['joken']['33']=['kodawari_joken_cd','08033'];
        $params['joken']['34']=['kodawari_joken_cd','08034'];
        $params['joken']['35']=['kodawari_joken_cd','08035'];
        $params['joken']['36']=['kodawari_joken_cd','08036']; //追加　単身者限定 -> kodawari_joken_code利用(2019/08)
        $params['joken']['37']=['kodawari_joken_cd','08037']; //追加　非喫煙者限定 -> kodawari_joken_code利用(2019/08)
        $params['joken']['38']=['tokki_cd','113']; //追加　シェアハウス
        $params['joken']['39']=['kodawari_joken_cd','08038']; //追加　DIY可 -> kodawari_joken_code利用(2019/08)

        $params['joken']['40']=['tokki_cd','135'];  //追加（2017/09）：１フロア１テナント
        $params['joken']['41']=['tokki_cd','136'];  //追加（2017/09）：土曜日利用可
        $params['joken']['42']=['tokki_cd','137'];  //追加（2017/09）：土日・祝日利用可
        $params['joken']['43']=['kodawari_joken_cd','08041'];  //追加（2018/09）：保証人不要 -> kodawari_joken_code利用(2019/08)




        $params['genkyo']['1']=['kodawari_joken_cd','09001'];
        $params['kyouyu_shisetsu']['1']=['kodawari_joken_cd','10001'];

        $params['kyouyu_shisetsu']['2']=['kodawari_joken_cd','10002'];
        $params['kyouyu_shisetsu']['3']=['kodawari_joken_cd','10003'];
        $params['kyouyu_shisetsu']['4']=['kodawari_joken_cd','10004'];
        $params['kyouyu_shisetsu']['5']=['kodawari_joken_cd','10005'];
        $params['kyouyu_shisetsu']['6']=['kodawari_joken_cd','10006'];
        $params['kyouyu_shisetsu']['7']=['kodawari_joken_cd','10007'];
        $params['kyouyu_shisetsu']['8']=['kodawari_joken_cd','10008'];
        $params['kyouyu_shisetsu']['9']=['kodawari_joken_cd','10009'];
        $params['kyouyu_shisetsu']['10']=['kodawari_joken_cd','10010'];
        $params['kyouyu_shisetsu']['11']=['kodawari_joken_cd','10011'];
        $params['kyouyu_shisetsu']['12']=['kodawari_joken_cd','10012'];
        $params['kyouyu_shisetsu']['13']=['kodawari_joken_cd','10013'];
        $params['kyouyu_shisetsu']['14']=['kodawari_joken_cd','10014'];
        $params['kyouyu_shisetsu']['15']=['kodawari_joken_cd','10015'];
        $params['kyouyu_shisetsu']['16']=['kodawari_joken_cd','10016'];
        $params['kyouyu_shisetsu']['17']=['kodawari_joken_cd','10017'];
        $params['kyouyu_shisetsu']['18']=['kodawari_joken_cd','10018'];
        $params['kyouyu_shisetsu']['19']=['kodawari_joken_cd','10019'];
        $params['kyouyu_shisetsu']['20']=['kodawari_joken_cd','10020'];
        $params['kyouyu_shisetsu']['21']=['kodawari_joken_cd','10021'];
        $params['kyouyu_shisetsu']['22']=['kodawari_joken_cd','10022'];
        $params['kyouyu_shisetsu']['23']=['kodawari_joken_cd','10023'];
        $params['kyouyu_shisetsu']['24']=['kodawari_joken_cd','10024'];
        $params['kyouyu_shisetsu']['25']=['kodawari_joken_cd','10025'];
        $params['kyouyu_shisetsu']['26']=['setsubi_cd','196']; //追加：人荷用エレベーター
        $params['kyouyu_shisetsu']['27']=['setsubi_cd','197']; //追加：施設内喫煙所

        $params['setsubi_kinou']['1']=['kodawari_joken_cd','11001'];
        $params['setsubi_kinou']['2']=['kodawari_joken_cd','11002'];
        $params['setsubi_kinou']['3']=['kodawari_joken_cd','11003'];
        $params['setsubi_kinou']['4']=['kodawari_joken_cd','11004'];

        $params['setsubi_kinou']['5']=['kodawari_joken_cd','11005'];

        $params['setsubi_kinou']['6']=['kodawari_joken_cd','11006'];
        $params['setsubi_kinou']['7']=['kodawari_joken_cd','11007']; //給湯

        $params['setsubi_kinou']['8']=['kodawari_joken_cd','11008'];
        $params['setsubi_kinou']['9']=['kodawari_joken_cd','11009'];
        $params['setsubi_kinou']['10']=['kodawari_joken_cd','11010'];
        $params['setsubi_kinou']['11']=['kodawari_joken_cd','11011'];
        $params['setsubi_kinou']['12']=['kodawari_joken_cd','11012'];
        $params['setsubi_kinou']['13']=['kodawari_joken_cd','11013'];
        $params['setsubi_kinou']['14']=['kodawari_joken_cd','11014'];
        $params['setsubi_kinou']['15']=['kodawari_joken_cd','11015'];
        $params['setsubi_kinou']['16']=['kodawari_joken_cd','11016'];
        $params['setsubi_kinou']['17']=['kodawari_joken_cd','11017'];
        $params['setsubi_kinou']['18']=['kodawari_joken_cd','11018'];
        $params['setsubi_kinou']['19']=['kodawari_joken_cd','11019'];
        $params['setsubi_kinou']['20']=['kodawari_joken_cd','11020'];
        $params['setsubi_kinou']['21']=['kodawari_joken_cd','11021'];
        $params['setsubi_kinou']['22']=['kodawari_joken_cd','11022'];
        $params['setsubi_kinou']['23']=['kodawari_joken_cd','11023'];
        $params['tokucho']['1']=['kodawari_joken_cd','12001'];


        $params['tokucho']['2']=['kodawari_joken_cd','12002'];
        $params['tokucho']['3']=['kodawari_joken_cd','12003'];
        $params['tokucho']['4']=['kodawari_joken_cd','12004'];
        $params['tokucho']['5']=['kodawari_joken_cd','12005'];
        $params['tokucho']['6']=['kodawari_joken_cd','12006'];
        $params['tokucho']['7']=['kodawari_joken_cd','12007'];
        $params['tokucho']['8']=['kodawari_joken_cd','12008'];
        $params['tokucho']['9']=['kodawari_joken_cd','12009'];
        $params['tokucho']['10']=['kodawari_joken_cd','12010'];
        $params['tokucho']['11']=['kodawari_joken_cd','12011'];
        $params['tokucho']['12']=['kodawari_joken_cd','12012'];

        $params['tokucho']['13']=['kodawari_joken_cd','12013'];
        $params['tokucho']['14']=['kodawari_joken_cd','12014'];
        $params['tokucho']['15']=['kodawari_joken_cd','12015'];
        $params['tokucho']['16']=['kodawari_joken_cd','12016'];
        $params['tokucho']['17']=['kodawari_joken_cd','12017'];

        $params['tokucho']['18']=['shuyo_saikomen_cd','05']; //追加　南向き
        $params['tokucho']['19']=['setsubi_cd','194']; //追加　家電付き

        $params['koho_kozo']['1']=['kodawari_joken_cd','13001'];
        $params['koho_kozo']['2']=['kodawari_joken_cd','13002'];
        $params['koho_kozo']['3']=['kodawari_joken_cd','13003'];
        $params['koho_kozo']['4']=['kodawari_joken_cd','13004'];
        $params['koho_kozo']['5']=['kodawari_joken_cd','13005'];

        $params['koho_kozo']['6']=['kodawari_joken_cd','13006'];
        $params['koho_kozo']['7']=['kodawari_joken_cd','13007'];
        $params['koho_kozo']['8']=['kodawari_joken_cd','13008'];
        $params['koho_kozo']['9']=['kodawari_joken_cd','13009'];
        $params['koho_kozo']['10']=['kodawari_joken_cd','13010'];
        $params['koho_kozo']['11']=['kodawari_joken_cd','13011'];
        $params['koho_kozo']['12']=['kodawari_joken_cd','13012'];
        $params['koho_kozo']['13']=['kodawari_joken_cd','13013'];
        $params['koho_kozo']['14']=['kodawari_joken_cd','13014'];
        $params['koho_kozo']['15']=['kodawari_joken_cd','13015'];
        $params['koho_kozo']['16']=['kodawari_joken_cd','13016'];
        $params['koho_kozo']['17']=['kodawari_joken_cd','13017'];
        $params['koho_kozo']['18']=['kodawari_joken_cd','13018'];
        $params['koho_kozo']['19']=['kodawari_joken_cd','13019'];
        $params['koho_kozo']['20']=['kodawari_joken_cd','13020'];
        $params['koho_kozo']['21']=['kodawari_joken_cd','13021'];

        $params['other']['1']=['kodawari_joken_cd','14001'];
        $params['other']['2']=['kodawari_joken_cd','14002'];
        $params['other']['3']=['kodawari_joken_cd','14003'];
        $params['other']['4']=['kodawari_joken_cd','14004'];
        $params['other']['5']=['kodawari_joken_cd','14005'];
        $params['other']['6']=['kodawari_joken_cd','14006'];
        $params['other']['7']=['kodawari_joken_cd','14007'];
        $params['other']['8']=['kodawari_joken_cd','14008'];
        $params['other']['9']=['kodawari_joken_cd','14009'];
        $params['other']['10']=['kodawari_joken_cd','14010'];
        $params['other']['11']=['kodawari_joken_cd','14011'];
        $params['other']['12']=['kodawari_joken_cd','14012'];
        $params['other']['13']=['kodawari_joken_cd','14013'];
        $params['other']['14']=['kodawari_joken_cd','14014'];
        $params['other']['15']=['kodawari_joken_cd','14015'];
        $params['other']['16']=['kodawari_joken_cd','14016'];
        $params['other']['17']=['kodawari_joken_cd','14017'];
        $params['other']['18']=['kodawari_joken_cd','14018'];
        $params['other']['19']=['kodawari_joken_cd','14019'];
        $params['other']['20']=['kodawari_joken_cd','14020'];
        $params['other']['21']=['kodawari_joken_cd','14021'];
        $params['other']['22']=['kodawari_joken_cd','14022'];
        $params['other']['23']=['kodawari_joken_cd','14023'];
        $params['other']['24']=['kodawari_joken_cd','14024'];//長期優良住宅（耐震、省エネ性等高い）
        $params['other']['25']=['kodawari_joken_cd','14025']; //フラット35・S適合証明書あり
//        $params['other']['25']=['tokki_cd','106,119']; //フラット35・S適合証明書あり
        $params['other']['26']=['kodawari_joken_cd','14026'];//耐震基準適合証明書あり
        $params['other']['27']=['kodawari_joken_cd','14027'];
        $params['other']['28']=['kodawari_joken_cd','14028'];
        $params['other']['29']=['kodawari_joken_cd','14029'];
        $params['other']['30']=['kodawari_joken_cd','14030'];
        $params['other']['31']=['kodawari_joken_cd','14032']; //追加　設計住宅性能評価取得
        $params['other']['32']=['kodawari_joken_cd','08021']; //追加　住宅性能保証付
        $params['other']['33']=['kodawari_joken_cd','14033']; //追加　建築後の完了検査済証あり
        $params['other']['34']=['tokki_cd','116']; //追加　低炭素住宅（省エネ性高い）
        $params['other']['35']=['kashi_hoken_kokko_sho_fl','true']; //追加　瑕疵保険（国交省指定）による保証
        $params['other']['36']=['kashi_hosho_fudosan_dokuji_fl','true']; //追加　瑕疵保証(不動産会社独自)
        $params['other']['37']=['kenchikushi_inspection_fl','true']; //追加　インスペクション（建物検査）済み
        $params['other']['38']=['tokki_cd','121']; //追加　新築時・増改築時の設計図書あり
        $params['other']['39']=['tokki_cd','122']; //追加　修繕・点検の記録あり
        $params['other']['40']=['credit_kessai_ari_fl','true']; //追加　クレジットカード決済
        $params['other']['41']=['tokki_cd','130']; //追加　IT重説対応物件
        $params['other']['42']=['onsen_hikikomi_jokyo_cd','1']; //追加　温泉（引込み済）
        $params['other']['43']=['onsen_hikikomi_jokyo_cd','2']; //追加　温泉（引込み可）
        $params['other']['44']=['onsen_hikikomi_jokyo_cd','3']; //追加　温泉（運び湯）
        $params['other']['45']=['tokki_cd','131']; //追加　再建築不可
        $params['other']['46']=['tokki_cd','132']; //追加　建築不可
        $params['other']['47']=['tokki_cd','138']; //追加(2017/09)　耐震構造（新耐震基準）
        $params['other']['48']=['setsubi_cd','198']; //追加(2017/10)　障がい者等用駐車場
        $params['other']['49']=['tokki_cd','139'];  //追加（2018/06）：安心Ｒ住宅


        $this->_particularMap = $params;
    }

    protected function _initShumokuAliasMap() {
		$this->_shumokuAliasMap = $this->_defineShumokuAliasMap();
    }

    protected static function _defineShumokuAliasMap() {
        // 事業用売買-売ビル・売倉庫・売工場・その他-その他(16) : 
        $params['16'] = [ '16', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60' ];
        // 居住用賃貸-賃貸(アパート・マンション・一戸建て)-マンション(18) : 
        $params['18'] = [ '18', '25' ];
        // 居住用賃貸-賃貸(アパート・マンション・一戸建て)-一戸建て(19) : 
        $params['19'] = [ '19', '24' ];
        // 事業用賃貸-貸ビル・貸倉庫・その他-その他(23) : 
        $params['23'] = [ '23', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '61' ];
        // 事業用売買-その他(39) : 
        $params['39'] = [ '39' ];
        return $params;
    }


    protected function _getDesiredParam($categoryId, $itemId) {
        return isset($this->_desiredMap[$categoryId][$itemId])?$this->_desiredMap[$categoryId][$itemId]:[];
    }

    protected function _getParticularParam($categoryId, $itemId) {
        return isset($this->_particularMap[$categoryId][$itemId])?$this->_particularMap[$categoryId][$itemId]:null;
    }

    public function getShumokuAliasMap($itemId=null) {
        if(is_null($itemId)) {
            return $this->_shumokuAliasMap;
        }
        if(isset($this->_shumokuAliasMap[ $itemId ])) {
            return $this->_shumokuAliasMap[ $itemId ];
        }
        return null;
    }

    static function getShumokuAliasMapStatic($itemId=null) {
        $params = self::_defineShumokuAliasMap();
        if(isset($params[ $itemId ])) {
            return $params[ $itemId ];
        }
        return null;
    }

    protected function _toSearchParamTorihikiTaiyo($category) {
        $torihikiTaiyoItem = $category->getItem(1);

        if (!$torihikiTaiyoItem) {
            return;
        }

        // リフォームリノベーションのCMS設定値
        $specialCmsValue    = $torihikiTaiyoItem->getParsedValue();

        // リフォームリノベーションの物件APIパラメータ群
        $searchParams = $this->_getDesiredParam($category->category_id, $torihikiTaiyoItem->item_id);

        $params[$searchParams[0]] = $specialCmsValue;

        return $params;
    }

    protected function _toSearchParamReformableParts($category) {
        $itemCnt = count($category->items);

        $params_buff = [];
        for($ino = 0; $ino < $itemCnt; $ino++) {
            $reformablePartsItem = $category->items[$ino];
            if (!$reformablePartsItem) {
                continue;
            }
            $searchParams = $this->_getDesiredParam($category->category_id, $reformablePartsItem->item_id)[0];
            $specialCmsValue = $reformablePartsItem->getParsedValue();

            foreach($specialCmsValue as $val) {
                if($val != 999) {
                    $params_buff[ $searchParams[0] ][] = $val;
                } else {
                    $params_buff[ $searchParams[1] ] = true;
                }
            }
        }

        $conditions = [];
        foreach($params_buff as $key => $val) {
            switch($key) {
                case "reform_renovation_mizumawari_cd":
                case "reform_renovation_naiso_cd":
                    $conditions[] = sprintf("AND(%s:%s)", $key, implode(",", $val));
                    break;
                case "reform_renovation_mizumawari_sonota_ari_fl":
                case "reform_renovation_naiso_sonota_ari_fl":
                case "reform_renovation_sonota_ari_fl":
                    if(!empty($val) && $val == true) {
                        $conditions[] = sprintf("AND(%s:true)", $key);
                    }
                    break;
                default:
                    break;
            }
        }
        $params['or'][] = implode("%3B", $conditions);
        return $params;
    }
}