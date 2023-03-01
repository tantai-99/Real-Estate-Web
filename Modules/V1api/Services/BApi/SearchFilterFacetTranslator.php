<?php
namespace Modules\V1api\Services\BApi;

use Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract;

class SearchFilterFacetTranslator {

    /**
     * アドバンスパラメータと物件APIパラメータのマップ
     * @var array
     */
    protected $_facetMap = [];
    protected $_facets = [];

    public function __construct($facets = []) {
    	if ($facets) {
        	$this->setFacets($facets);
    	}
        $this->_initFacetMap();
    }

    /**
     * 絞り込み条件を元に物件APIに渡すファセットパラメータを取得する
     */
    public function toFacetParam(SearchFilterAbstract $searchFilter) {
        $params = [];
        // 全てのファセットが必要なので全部渡す
        foreach ($this->_facetMap as $categoryId => $items) {
            foreach ($items as $itemId => $param) {
                $params[ $param[0] ] = true;
            }
        }
        $params = array_keys($params);
        $params = array_filter($params);
        // 例外的にfacetsパラメータに追加設定
		$params[] = 'joho_kokai:E200';
        $params[] = 'csite_kokai_date_within';
        return $params;
    }

    public function setFacets($facets) {

		if (!is_array($facets)){
			return [];
		}

    	$map = [];
    	foreach ($facets as $name => $facet) {
    		// リストはアドバンス用ラベル毎に保持
    		if ($name == 'madori_cd') {
    			$map[$name] = $this->_parseMadriFacet($facet);
    			continue;
    		}
    		elseif ($name == 'joho_kokai:B100') {
    			continue;
    			// $map[$name] = $this->_parseJohoKokaiFacet($facet, isset($map[$name])?$map[$name]:null);
    		}
    	    elseif ($name == 'joho_kokai:E200') {
    			continue;
    	    	// joho_kokai:B100 に物件件数を集約
    			// $map['joho_kokai:B100'] = $this->_parseJohoKokaiFacet($facet, isset($map['joho_kokai:B100'])?$map['joho_kokai:B100']:null);
    	    }
            elseif ($name == 'csite_kokai_date_within') {
    			$map[$name] = $this->_parseJohoKokaiFacet($facet, isset($map[$name])?$map[$name]:null);
    	    }
    		elseif ($name == 'tatemono_kozo_cd') {
    			$map[$name] = $this->_parseTatemonoKozoFacet($facet);
    		}

    		foreach ($facet as $obj) {
    			$code = $obj['value'];
    			if (is_bool($code)) {
    				$code = (int)$code;
    			}
    			$map[$name][$code] = $obj['count'];
    		}
    	}
        $this->_facets = $map;
    }

    /**
	 * 間取りのファセットマップを作成する
	 * [ラベル=>件数]
     */
    protected function _parseMadriFacet($facet) {
    	$madriCode = [
	    	'01' => 'R',
	    	'02' => 'K',
	    	'03' => 'DK',
	    	'04' => 'LK',
	    	'05' => 'LDK',
	    	'06' => 'SK',
	    	'07' => 'SDK',
	    	'08' => 'SLK',
	    	'09' => 'SLDK',
    	];
        $addCode = [
            '06'  => '02'
            ,'04' => '03'
            ,'07' => '03'
            ,'08' => '03'
            ,'09' => '05'
        ];
    	$madriMap = [];
    	$_under1LDK = 0;
    	$_upper4LDK = 0;
    	$_upper5LDK = 0;
    	foreach ($facet as $obj) {
	    	$parts = explode(':', $obj['value']);
	    	$code = $parts[0].$madriCode[ $parts[1]];

	    	// 並び順
	    	// ['01', '02', '06', '03', '04', '07', '08', '05', '09']

	    	// 1LDK以下
	    	if ($parts[0] == 1 && in_array($parts[1], ['01', '02', '06', '03', '04', '07', '08', '05', '09'])) {
		    	$_under1LDK += $obj['count'];
	    	}
	    	// 4LDK以上
	    	if ($parts[0] > 4 || ($parts[0] == 4 && in_array($parts[1], ['05', '09']))) {
		    	$_upper4LDK += $obj['count'];
	    	}
	    	// 5LDK以上
	    	if ($parts[0] > 5 || ($parts[0] == 5 && in_array($parts[1], ['05', '09']))) {
		    	$_upper5LDK += $obj['count'];
	    	}

	    	$madriMap[ $code ] = $obj['count'];

            if (isset($addCode[$parts[1]])) {
                $sameCode = $parts[0].$madriCode[$addCode[$parts[1]]];
                if (isset($madriMap[$sameCode])) {
                    $madriMap[$sameCode] += $obj['count'];
                } else {
                    $madriMap[$sameCode] = $obj['count'];
                }
            }
    	}
    	$madriMap['1LDK以下'] = $_under1LDK;
    	$madriMap['4LDK以上'] = $_upper4LDK;
    	$madriMap['5LDK以上'] = $_upper5LDK;
    	return $madriMap;
    }

    /**
	 * 情報公開のファセットマップを作成する
	 * [ラベル=>件数]
     */
    protected function _parseJohoKokaiFacet($facet, $map) {
    	$_map = [];
        foreach ($facet as $obj) {
    		$_map[$obj['value']] = $obj['count'];
        }
    	if ($map && count($_map) > 0) {
			$map['本日公開'] = $map['本日公開'] + (isset($_map[0]) ? $_map[0] : 0);
    		$map['3日以内に公開'] = $map['3日以内に公開'] + (isset($_map[3]) ? $_map[3] : 0);
    		$map['1週間以内に公開'] = $map['1週間以内に公開'] + (isset($_map[7]) ? $_map[7] : 0);
    	} elseif (is_null($map)) {
    		$map = [
    				'本日公開' => isset($_map[0])?$_map[0]:0,
    				'3日以内に公開' => isset($_map[3])?$_map[3]:0,
    				'1週間以内に公開' => isset($_map[7])?$_map[7]:0,
    		];
    	}
		return $map;
    }

    /**
	 * 建物構造のファセットマップを作成する
	 * グループコードで返却されないのでアドバンス側対応しなければならない
	 * [コード=>件数]
     */
    protected function _parseTatemonoKozoFacet($facet) {
        $map = [];
    	$groupMap = [
    		'04'=>'1100',
    		'05'=>'1100',
    		'06'=>'1100',
    		// '02'=>'1100',

    		'03'=>'1101',
    		'08'=>'1101',
    		'07'=>'1101',
    		'09'=>'1101',

            '02'=>'1102',
            '10'=>'1102',
    	];
    	$map['1100'] = 0;
    	$map['1101'] = 0;
        $map['1102'] = 0;
        foreach ($facet as $obj) {
    		$code = $obj['value'];
    		$map[$code] = $obj['count'];
    		if (isset($groupMap[$code])) {
    			$map[$groupMap[$code]] += $obj['count'];
    		}
    	}
    	return $map;
    }

    protected function _getFacetMap($categoryId, $itemId) {
        return isset($this->_facetMap[$categoryId][$itemId])?$this->_facetMap[$categoryId][$itemId]:null;
    }

    /**
     * 指定されたアドバンスカテゴリID,アイテムIDのファセットを返却する
     * @return int
     */
    public function getFacet($categoryId, $itemId) {
        $data = $this->_getFacetMap($categoryId, $itemId);
        if (!$data) {
            return null;
        }

        $name = $categoryId . '__' . $itemId;
        switch ($name) {
        	// ラジオ、Multiチェックボックスの場合は[ラベル=>件数]マップを返却
        	case 'madori__1':
        	case 'joho_kokai__1':
        		return isset($this->_facets[$data[0]])?$this->_facets[$data[0]]:[];

        	default:
                // 複数項目の合計: xxを除く..
                if(is_array($data[1])) {
                    $fcnt = 0;
                    foreach ($data[1] as $subCode) {
                        if(isset($this->_facets[$data[0]][$subCode]) && is_numeric($this->_facets[$data[0]][$subCode])) {
                            $fcnt = $fcnt + intval($this->_facets[$data[0]][$subCode]);
                        }
                    }
                    return $fcnt;
                }
        		return isset($this->_facets[$data[0]][$data[1]])?$this->_facets[$data[0]][$data[1]]:0;
        }
    }

    protected function _initFacetMap() {
		$params = [
			'shumoku'=>[
				'13'=>['csite_chubunrui_shumoku_cd', '6601'],
				'14'=>['csite_chubunrui_shumoku_cd', '6602'],
				'15'=>['csite_chubunrui_shumoku_cd', '6603'],
				'16'=>['csite_chubunrui_shumoku_cd', '6604'],
				'17'=>['csite_chubunrui_shumoku_cd', '7101'],
				'18'=>['csite_chubunrui_shumoku_cd', '7102'],
				'19'=>['csite_chubunrui_shumoku_cd', '7103'],
				'20'=>['csite_chubunrui_shumoku_cd', '7601'],
				'21'=>['csite_chubunrui_shumoku_cd', '7602'],
				'22'=>['csite_chubunrui_shumoku_cd', '7603'],
				'23'=>['csite_chubunrui_shumoku_cd', '7604'],
				// '39'=>['csite_chubunrui_shumoku_cd', '6201'],
				// '40'=>['csite_chubunrui_shumoku_cd', '6101'],
				'39'=>['joi_shumoku_cd', '02'],
				'40'=>['joi_shumoku_cd', '01'],
			],
			'kakaku'=>[
				'3'=>['kanrihi_kyoekihi_komi', 'kanrihi_kyoekihi_komi'],
				'4'=>['chushajo_ryokin_komi', 'chushajo_ryokin_komi'],
				'5'=>['reikin_nashi_fl', 1],
				'6'=>['shikikin_hoshokin_nashi_fl', 1],
			],
			// 個別処理
			'madori'=>[
				'1'=>['madori_cd'],
			],
			'tatemono_kozo'=>[
				'1'=>['tatemono_kozo_cd', '1100'],
				'2'=>['tatemono_kozo_cd', '1101'],
				'3'=>['tatemono_kozo_cd', '01'],
				'4'=>['tatemono_kozo_cd', '1102'],
			],
			'saiteki_yoto'=>[
				'1'=>['saiteki_yoto_cd', '01'],
                '2'=>['saiteki_yoto_cd', [ '02', '03', '04', '05', '06', '07', '08', '09', '10',
                                     '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '55'] ],
			],
			'reform_renovation'=>[
				'1'=>['reform_renovation_ari_fl', 1],
			],
			'open_room'=>[
				'1'=>['open_house_fl', 1],
			],
			'open_house'=>[
				'1'=>['open_house_fl', 1],
			],
			'genchi_hanbaikai'=>[
				'1'=>['open_house_fl', 1],
			],
			// 個別処理
			'joho_kokai'=>[
				'1'=>['csite_kokai_date_within'],
			],
			'pro_comment'=>[
				'1'=>['ippan_kokai_message_ari_fl', 1],
			],
			'image'=>[
				'1'=>['madorizu_ari_fl', 1],
				'2'=>['madorizu_ari_fl', 1],
				'3'=>['madorizu_ari_fl', 1],
				'4'=>['shashin_ari_fl', 1],
				'5'=>['csite_panorama_kokai_fl', 1],  // パノラマの検索条件を変更(panorama_movie_ari_fl → csite_panorama_kokai_fl)
			],
		];

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
        //$params['bath_toilet']['24']=['setsubi_cd','195']; //追加　多機能トイレ＃facestなし


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
        $params['joken']['43']=['kodawari_joken_cd','08041']; //追加　保証人不要 -> kodawari_joken_code利用(2019/08)


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
        //$params['kyouyu_shisetsu']['26']=['setsubi_cd','196']; //追加：人荷用エレベーター ＃ファセットなし
        //$params['kyouyu_shisetsu']['27']=['setsubi_cd','197']; //追加：施設内喫煙所 ＃ファセットなし


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
		$params['other']['24']=['kodawari_joken_cd','14024'];
		$params['other']['25']=['kodawari_joken_cd','14025']; //フラット35・S適合証明書あり
//        $params['other']['25']=['tokki_cd','106,119']; //フラット35・S適合証明書あり
		$params['other']['26']=['kodawari_joken_cd','14026'];
		$params['other']['27']=['kodawari_joken_cd','14027'];
		$params['other']['28']=['kodawari_joken_cd','14028'];
		$params['other']['29']=['kodawari_joken_cd','14029'];
		$params['other']['30']=['kodawari_joken_cd','14030'];

        $params['other']['31']=['kodawari_joken_cd','14032']; //追加　設計住宅性能評価取得
        $params['other']['32']=['kodawari_joken_cd','08021']; //追加　住宅性能保証付
        $params['other']['33']=['kodawari_joken_cd','14033']; //追加　建築後の完了検査済証あり
        $params['other']['34']=['tokki_cd','116']; //追加　低炭素住宅（省エネ性高い）
        //$params['other']['35']=['kashi_hoken_kokko_sho_fl','true']; //追加　瑕疵保険（国交省指定）による保証
        //$params['other']['36']=['kashi_hosho_fudosan_dokuji_fl','true']; //追加　瑕疵保証(不動産会社独自)
        $params['other']['37']=['tokki_cd','120']; //追加　インスペクション（建物検査）済み
        $params['other']['38']=['tokki_cd','121']; //追加　新築時・増改築時の設計図書あり
        $params['other']['39']=['tokki_cd','122']; //追加　修繕・点検の記録あり
        //$params['other']['40']=['credit_kessai_ari_fl','true']; //追加　クレジットカード決済
        $params['other']['41']=['tokki_cd','130']; //追加　IT重説対応物件
        //$params['other']['42']=['onsen_hikikomi_jokyo_cd','1']; //追加　温泉（引込み済）
        //$params['other']['43']=['onsen_hikikomi_jokyo_cd','2']; //追加　温泉（引込み可）
        //$params['other']['44']=['onsen_hikikomi_jokyo_cd','3']; //追加　温泉（運び湯）
        $params['other']['45']=['tokki_cd','131']; //追加　再建築不可
        $params['other']['46']=['tokki_cd','132']; //追加　建築不可
        $params['other']['47']=['tokki_cd','138']; //追加(2017/09)　耐震構造（新耐震基準）
        //$params['other']['48']=['setsubi_cd','198']; //追加(2017/10)　障がい者等用駐車場 ＃ファセットなし

        $this->_facetMap = $params;
    }
}