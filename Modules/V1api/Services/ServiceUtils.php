<?php
namespace Modules\V1api\Services;

use Modules\V1api\Models;
use Library\Custom\Model\Estate;
use Exception;
class ServiceUtils
{
    protected static $selectRequest;

    /**
     * 物件種目ローマ字文字列に対応した種目名を返します。
     *
     * @param $type_ct
     * @return string
     */
    public static function getShumokuNameByConst($type_ct)
    {
        $typeList = Estate\TypeList::getInstance();
        $shumoku    = $typeList->getTypeByUrl($type_ct);
        return $typeList->get($shumoku);
    }

    /**
     * 物件種目ローマ字文字列に対応した種目CDを返します。
     *
     * @param $type_ct
     * @return string
     */
    public static function getShumokuCdByConst($type_ct)
    {
        return static::$_codeList[$type_ct];
    }

    /**
     * 種目CDに対応した物件種目ローマ字文字列を返します。
     *
     * @param $type_cd
     * @return string
     */
    public static function getShumokuCtByCd($type_cd)
    {
        foreach (static::$_codeList as $key => $value) {
            if ($value == $type_cd) return $key;
        }
    }

    /**
     * 種目CDに対応した物件種目文字を返します。
     *
     * @param $type_cd
     * @return string
     */
    public static function getShumokuNameByCd($type_cd)
    {
        return static::$_list[$type_cd];
    }

    public static function isChintai($shumoku_cd)
    {
        if ($shumoku_cd == self::TYPE_CHINTAI ||
            $shumoku_cd == self::TYPE_KASI_TENPO || 
            $shumoku_cd == self::TYPE_KASI_OFFICE || 
            $shumoku_cd == self::TYPE_PARKING || 
            $shumoku_cd == self::TYPE_KASI_TOCHI || 
            $shumoku_cd == self::TYPE_KASI_OTHER
            )
        {
            return true;        
        }
        return false;
    }

    public static function isBaibai($shumoku_cd)
    {
        return (! self::isChintai($shumoku_cd));
    }

    
    /**
     * 都道府県ローマ字文字列に対応した都道府県名を返します。
     *
     * @param $ken_ct
     * @return string
     */
    public static function getKenNameByConst($ken_ct)
    {
        $withSuffix=true;
        $prefModel = Estate\PrefCodeList::getInstance();
        return $prefModel->getNameByUrl($ken_ct, $withSuffix);
    }

    /**
     * 都道府県ローマ字文字列に対応した都道府県名を返します。
     *
     * @param $ken_ct
     * @return string
     */
    public static function getKenNameByConstWithoutSuffix($ken_ct)
    {
        $withSuffix=false;
        $prefModel = Estate\PrefCodeList::getInstance();
        return $prefModel->getNameByUrl($ken_ct, $withSuffix);
    }

    /**
     * 都道府県ローマ字文字列に対応した都道府県コードを返します。
     *
     * @param $ken_ct
     * @return string
     */
    public static function getKenCdByConst($ken_ct)
    {
        $prefModel = Estate\PrefCodeList::getInstance();
        return $prefModel->getCodeByUrl($ken_ct);
    }

    /**
     * 都道府県コードに対応した都道府県ローマ字文字列を返します。
     *
     * @param $ken_cd
     * @return string
     */
    public static function getKenCtByCd($ken_cd)
    {
        $prefModel = Estate\PrefCodeList::getInstance();
        return $prefModel->getUrl($ken_cd);
    }
    
    /**
     * 市区郡ローマ字文字列に対応した市区郡OBJを返します。
     * 物件APIを呼び出すため、複数の名称を取得する処理では
     * 使用しないでください。
     *
     * @param $ken_cd
     * @param $shikugun_ct
     * @return stdclass
     *  {
     *    code: "13103",
     *    shikugun_nm: "港区",
     *    shikugun_kana: "ﾐﾅﾄｸ",
     *    shikugun_roman: "minato"
     *  }
     */
    public static function getShikugunObjByConst($ken_cd, $shikugun_ct)
    {        
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\ShikugunListParams();
        $apiParam->setKenCd($ken_cd);
        
        // 配列できた場合は、最初の要素を使用する。
        if (is_array($shikugun_ct)) {
            $shikugun_ct = $shikugun_ct[0];
        }
        
        $apiParam->setShikugunRoman($shikugun_ct);

        // 物件API：市区町村一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\ShikugunList();
        $shikugun = $apiObj->getShikugun($apiParam, 'CITY_INFO');
        return (object) $shikugun['shikuguns'][0]['shikuguns'][0];
    }

    public static function getShikugunCdApiList($chosonCodes, $shikugunObjList)
    {    
        $shikugun_cd_api_choson = array();
        foreach ($chosonCodes as $choson) {
            $chosonExplode = explode('-', $choson);
            foreach ($shikugunObjList as $shikugunObj) {
              if ($shikugunObj['shikugun_roman'] == $chosonExplode[0]) {
                array_push($shikugun_cd_api_choson, $shikugunObj['code'] . ':' . trim($chosonExplode[1]));
              }
            }
        }
        return $shikugun_cd_api_choson;
    }
    
    /**
     * 市区郡名複数取得用
     *
     */
    public static function getShikugunListByConsts($ken_cd, $shikugunList)
    {        
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\ShikugunListParams();
        $apiParam->setKenCd($ken_cd);
        
        if (! is_array($shikugunList)) {
            $shikugunList = array($shikugunList);
        }
        
        $apiParam->setShikugunRoman($shikugunList);
        
        // 物件API：市区町村一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\ShikugunList();
        $shikugun = $apiObj->getShikugun($apiParam, 'CITY_INFO');
        return (object) $shikugun['shikuguns'][0]['shikuguns'];
    }

    /**
     * 所在地CDに対応した市区郡OBJを返します。
     * 物件APIを呼び出すため、複数の名称を取得する処理では
     * 使用しないでください。
     *
     * @param $ken_cd
     * @param $shozaichi_cd
     * @return stdclass
     *  {
     *    code: "13103",
     *    shikugun_nm: "港区",
     *    shikugun_kana: "ﾐﾅﾄｸ"
     *  }
     */
    public static function getShikugunObjByCd($ken_cd, $shozaichi_cd)
    {        
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\ShikugunListParams();
        $apiParam->setKenCd($ken_cd);
        $apiParam->setShozaichiCd($shozaichi_cd);

        // 物件API：市区町村一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\ShikugunList();
        $shikugun = $apiObj->getShikugun($apiParam, 'CITY_INFO');
        return (object) $shikugun['shikuguns'][0]['shikuguns'][0];
    }

    public static function getChosonListByShikugunCd($shikugun_cd) {
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\ChosonListParams();
        $apiParam->setShikugunCd($shikugun_cd);

        // 物件API：市区町村一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\Choson();
        $result = $apiObj->getChosonList($apiParam, 'CHOSON_INFO');
        return $result['shikuguns'];
    }

    /**
     * 沿線ローマ字文字列に対応した沿線OBJを返します。
     * 物件APIを呼び出すため、複数の名称を取得する処理では
     * 使用しないでください。
     *
     * @param $ensen_ct
     * @return stdclass
     * {
     *    "code": "4001",
     *    "ensen_nm": "ＪＲ東海道新幹線",
     *    "ensen_kana": "JRﾄｳｶｲﾄﾞｳｼﾝｶﾝｾﾝ",
     *    ensen_roman: "tohokushinkansen",
     *  }
     */
    public static function getEnsenObjByConst($ken_cd, $ensen_ct)
    {
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\EnsenListParams();
        $apiParam->setKenCd($ken_cd);
        
        // 配列できた場合は、最初の要素を使用する。
        if (is_array($ensen_ct)) {
            $ensen_ct = $ensen_ct[0];
        }
        
        $apiParam->setEnsenRoman($ensen_ct);

        // 物件API：沿線一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\EnsenList();
        $ensen = $apiObj->getEnsen($apiParam, 'LINE_INFO');
        return (object) $ensen['ensens'][0]['ensens'][0];
    }
    
    public static function getEnsenCtByCd($ken_cd, $ensen_cd) {
    	// BApi用パラメータ作成
    	$apiParam = new Models\BApi\EnsenListParams();
    	$apiParam->setKenCd($ken_cd);
    	$apiParam->setEnsenCd($ensen_cd);
    	
    	// 物件API：一覧リストにアクセスし情報を取得
    	// 結果JSONを元に要素を作成。
    	$apiObj = new Models\BApi\EnsenList();
    	$ensen = $apiObj->getEnsen($apiParam, 'LINE_INFO');
    	$ensen_ct = $ensen['ensens'][0]['ensens'][0]['ensen_roman'];
    	return $ensen_ct;
    }
    
    /**
     * 沿線名複数取得用
     *
     */
    public static function getEnsenListByConsts($ken_cd, $ensenList)
    {        
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\EnsenListParams();
        $apiParam->setKenCd($ken_cd);
        
        if (! is_array($ensenList)) {
            $ensenList = array($ensenList);
        }
        
        $apiParam->setEnsenRoman($ensenList);
        
        // 物件API：一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\EnsenList();
        $ensen = $apiObj->getEnsen($apiParam, 'LINE_INFO');
        return (object) $ensen['ensens'][0]['ensens'];
    }

    /**
     * 駅ローマ字文字列に対応した駅OBJを返します。
     * 物件APIを呼び出すため、複数の名称を取得する処理では
     * 使用しないでください。
     *
     * @param $eki_ct
     * @return stdclass
     * {
     *    "code": "6654:060",
     *    "eki_nm": "御陵",
     *    eki_roman: "tokyo",
     *  }
     */
    public static function getEkiObjByConst($eki_ct)
    {
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\EkiListParams();
        
        // 配列できた場合は、最初の要素を使用する。
        if (is_array($eki_ct)) {
            $eki_ct = $eki_ct[0];
        }
        $apiParam->setEnsenEkiRoman($eki_ct);
        
        $ekiObj = Models\EnsenEki::getObjBySingle($eki_ct);
        $ensen_ct = $ekiObj->getEnsenCt();
        $apiParam->setEnsenRoman($ensen_ct);

        // 物件API：覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\EkiList();
        $eki = $apiObj->getEki($apiParam, 'EKI_INFO');
        return (object) $eki['ensens'][0]['ekis'][0];
    }
    
    /**
     * 駅名複数取得用
     *
     */
    public static function getEkiListByConsts($ekiList)
    {
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\EkiListParams();
        
        if (! is_array($ekiList)) {
            $ekiList = array($ekiList);
        }
        $apiParam->setEnsenEkiRoman($ekiList);
        
        $ensenCtList = array();
        foreach ($ekiList as $eki) {
            $ekiObj = Models\EnsenEki::getObjBySingle($eki);
            array_push($ensenCtList, $ekiObj->getEnsenCt());
        }
        $apiParam->setEnsenRoman($ensenCtList);
        
        // 物件API：一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\EkiList();
        $ensenEkiList = $apiObj->getEki($apiParam, 'EKI_INFO');
        // 沿線情報を削除して駅リストを作成
        $result = array();
        foreach ($ensenEkiList['ensens'] as $ensen) {
            foreach ($ensen['ekis'] as $eki) {
                array_push($result, $eki);
            }
        }
       
        return $result;
    }

    /**
     * ロケートローマ字文字列に対応したロケートOBJを返します。
     * 現在ロケート名は政令指定都市名のみを対象としています。
     * 物件APIを呼び出すため、複数の名称を取得する処理では
     * 使用しないでください。
     *
     * @param $eki_ct
     * @return stdclass
     * {
     *    "locate_cd": "00100",
     *    "locate_nm": "東京23区",
     *    "seirei_fl": false,
     *  }
     */
    public static function getLocateObjByConst($ken_cd, $locate_ct)
    {
        // BApi用パラメータ作成
        $apiParam = new Models\BApi\ShikugunListParams();
        $apiParam->setKenCd($ken_cd);
        $apiParam->setLocateRoman($locate_ct);
        $apiParam->setGrouping(
                Models\BApi\ShikugunListParams::GROUPING_TYPE_LOCATE_CD);

        // 物件API：市区町村一覧リストにアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new Models\BApi\ShikugunList();
        $shikugun = $apiObj->getShikugun($apiParam, 'SEIREI_CITY_INFO');
        return (object) $shikugun['shikuguns'][0]['locate_groups'][0];
    }
    
    /**
     * $display_model->csite_images_madori_last から代表画像を取得します。（PC用）
     * 代表画像に該当するものがない場合は、Nullが返ります。
     */
    public static function getMainImageForPC( $display_model, $params )
    {
        $thumbnail	= null ;
        $madori		= null ;
        if ( isset( $display_model->csite_images_madori_last ) && count( $display_model->csite_images_madori_last ) > 0 )
        {
        	$images = $display_model->csite_images_madori_last ;
        	// シリアル番号で並び替え
			usort( $images, function( $a, $b )
				{
				    if ($a['serial_no'] == $b['serial_no'])
				    {
				    	return 0;
				    }
				    else if ($a['serial_no'] < $b['serial_no'])
    				{
				        return -1;
				    }
 				   else {
				        return 1;
    				}
			});
            foreach ( $images as $elem )
            {
                $image = (object) $elem ;
                if ( $image->serial_no == 1 && $image->status == 2 )
                {
                    if ( isset( $image->url ) )
                	{
                        $madori = $image ;
                    }
                }

                if ($params->isPicBukken()) {
                    // 外観
                    if ( $image->serial_no >= 2 && $image->serial_no <= 16 && $image->status == 2 )
                    {
                	    if ( isset( $image->url ) )
                	    {
                		    $thumbnail = $image ;
                		    break ;
                	    }
                    }
                } else {
                    // 間取り
                    if ( isset( $image->url ) )
                    {
                        $thumbnail = $image ;
                        break ;
                    }
                }
            }
            if ( $thumbnail === null )
            {
            	$thumbnail = $madori	;
            }
        }
        return $thumbnail;
    }

    /**
     * $display_model->csite_images_gaikan_first から代表画像を取得します。（SP用）
     * 代表画像に該当するものがない場合は、Nullが返ります。
     */
    public static function getMainImageForSP( $display_model, $params )
    {
        $thumbnail	= null ;
        if ( isset( $display_model->csite_images_gaikan_first ) && count( $display_model->csite_images_gaikan_first ) > 0 )
        {
        	$images = $display_model->csite_images_gaikan_first ;
        	// シリアル番号で並び替え
			usort( $images, function( $a, $b )
				{
				    if ($a['serial_no'] == $b['serial_no'])
				    {
				    	return 0;
				    }
				    else if ($a['serial_no'] < $b['serial_no'])
    				{
				        return -1;
				    }
 				   else {
				        return 1;
    				}
			});
            foreach ( $images as $elem )
            {
                $image = (object) $elem ;
                if ( $image->serial_no == 1 && $image->status == 2 && count($images) > 1 )
                {
                    $gaikanImage = (object) $images[1];
                    if ($gaikanImage->serial_no == 2 && $gaikanImage->status == 2) {
                        if ( isset($gaikanImage->url) ) {
                            $thumbnail = $gaikanImage;
                        }
                    } else {
                        if ( isset($image->url) ) {
                            $thumbnail = $image;
                        }
                    }
                    break;
                }
                if ($image->serial_no <= 16 && $image->status == 2) {
                    if ( isset($image->url) ) {
                        $thumbnail = $image;
                        break;
                    }
                }
            }
        }
        return $thumbnail;
    }

    /**
     * $dataModel->images から代表画像を取得します。
     * 代表画像に該当するものがない場合は、Nullが返ります。
     */
    public static function getMainImage($dataModel, $params)
    {
        $thumbnail = null;
        if (isset($dataModel->images) && count($dataModel->images) > 0)
        {
        	$images = $dataModel->images;
        	// シリアル番号で並び替え
			usort($images, function($a, $b)
				{
				    if ($a['serial_no'] == $b['serial_no'])
				    {
				    	return 0;
				    }
				    else if ($a['serial_no'] < $b['serial_no'])
    				{
				        return -1;
				    }
 				   else {
				        return 1;
    				}
			});
            foreach ($images as $elem)
            {
                $image = (object) $elem;
                if ($params->isPicBukken()) {
                    // 外観
                    if ($image->serial_no > 1 && $image->serial_no < 17
                             && $image->status == 2) {
                        $thumbnail = $image;
                        break;
                    }
                } else {
                    // 間取り
                    if ($image->serial_no == 1 && $image->status == 2) {
                        $thumbnail = $image;
                        break;
                    }
                }
            }
        }
        return $thumbnail;
    }

    /**
     * $dataModel->images か物件コマ用の代表画像を取得します。
     * 物件コマの場合、外観画像：２が存在しない場合、間取り画像：１の表示を試みます。
     * 代表画像に該当するものがない場合は、Nullが返ります。
     */
    public static function getMainImageKoma($dataModel, $params)
    {
    	$thumbnail = null;
    	if (isset($dataModel->images) && count($dataModel->images) > 0)
    	{
    		$images = $dataModel->images;
    		// シリアル番号で並び替え
    		usort($images, function($a, $b)
    		{
    			if ($a['serial_no'] == $b['serial_no'])
    			{
    				return 0;
    			}
    			else if ($a['serial_no'] < $b['serial_no'])
    			{
    				return -1;
    			}
    			else {
    				return 1;
    			}
    		});
    		foreach ($images as $elem)
    		{
    			$image = (object) $elem;
    			if ($image->serial_no == 1 && $image->status == 2 && count($dataModel->images) >1) {
    				// 外観画像チェック
    				$gaikanImage = (object) $images[1];
    				// 外観画像が存在するなら外観画像を返す。異なるなら間取り画像を返す。
    			    if ($gaikanImage->serial_no == 2 && $gaikanImage->status == 2) {
    					$thumbnail = $gaikanImage;
    				} else {
    					$thumbnail = $image;
    				}
    				break;
    			}
    			
    			if ($image->serial_no < 17
    					&& $image->status == 2) {
    				$thumbnail = $image;
    				break; 			
    			}
    		}
    	}
    	return $thumbnail;
    }



    /** 地図検索が有効かどうかを取得する
     * @param Models\Params $params
     * @param Models\Settings $settings
     * @param Models\Datas $datas
     */
    public static function canSpatialSearch(
        Models\Params $params,
        Models\Settings $settings,
        Models\Datas $datas)
    {
        // 種目情報の取得
        $type_ct    = $params->getTypeCt();
        $searchCond = $settings->search;
        $companyRow = $settings->company->getRow();

        if( $companyRow->isMapOptionAvailable() && $searchCond->canSpatialSearch($type_ct)){
            return true;
        }
        return false;
    }


    /** 特集の地図検索が有効かどうかを取得する
     * @param Models\Params $params
     * @param Models\Settings $settings
     * @param Models\Datas $datas
     */
    public static function canSplSpatialSearch(
        Models\Params $params,
        Models\Settings $settings,
        Models\Datas $datas)
    {
        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $companyRow = $settings->company->getRow();

        if( $companyRow->isMapOptionAvailable() && $specialSetting->area_search_filter->hasSpatialSearchType()){
            return true;
        }
        return false;
    }


    /**
     * 地図を表示できるかどうかを取得する
     * 　下記の場合に表示できないと判定する
     * 　・物件情報に、「地図検索不可フラグ(data_model.chizu_kensaku_fuka_fl=true)」が含まれる場合
     * 　・物件情報を所有する会員の会員情報に、「地図情報利用不可(isNotUsedMapDisplay=true)」が含まれる場合
     *     NHP-5208: isNotUsedMapDisplay 廃止
     */
    public static function canDisplayMap( $pageInitialSettings, $bukken, $shumoku)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        //物件の「chizu_kensaku_fuka_fl=true」なら地図は表示しない
        $chizu_kensaku_fuka_fl = self::getVal('chizu_kensaku_fuka_fl', $dataModel, true);
        if ( $chizu_kensaku_fuka_fl === true ){
            return false;
        }

        // 会員APIより、契約会員と物件会員の会員情報を取得する
        //     ・自社物件の会員
        //     ・グループ会社物件を所持する会員
        //     ・２次広告自動公開物件の元付け会員
        $keiyakuKaiinNo = $pageInitialSettings->getMemberNo();
        $bukkenKaiinNo  = self::getVal('csite_muke_kaiin_no', $dispModel, true);

        $kaiinNoList[] = $keiyakuKaiinNo;
        if ( $keiyakuKaiinNo != $bukkenKaiinNo ){
            $kaiinNoList[] = $bukkenKaiinNo;
        }
        $apiParam = new Models\KApi\KaiinListParams();
        $apiParam->setKaiinNos($kaiinNoList);

        // 結果JSONを元に要素を作成。
        $apiObj = new Models\KApi\KaiinList();
        $kaiinList = $apiObj->get($apiParam, '会員リスト取得');

        // 会員情報がとれない場合は地図表示なしにしておく。
        if( !$kaiinList ){
            return false;
        }

        // 契約会員または物件会員の会員情報のプロパティが下記の場合、地図表示なしとする
        //   isNotUsedMapSearch=true
        foreach ($kaiinList as $kaiinInfo) {
            $kaiinInfo = (object)$kaiinInfo;
            if(!$kaiinInfo || !isset($kaiinInfo->isNotUsedMapSearch) || $kaiinInfo->isNotUsedMapSearch === true){
                return false;
            }
        }

        // 4127: Don't display map if matching level different E and 8
        $matchingLevel = self::getVal('matching_level_cd', $dispModel);
        if (!($matchingLevel == "E") && !($matchingLevel == "8")) {
            return false;
        }
        return true;
    }

    /**
     * パノラマ物件かどうか（旧パノラマ）
     * 　・
     */
    public static function isPanorama($bukken)
    {
        return false;

        $dispModel = (object)$bukken['display_model'];
        $dataModel = (object)$bukken['data_model'];

        $csite_panorama_kokai_fl = self::getVal('csite_panorama_kokai_fl', $dispModel,true);

        //  display_modelのcsite_panorama_kokai_flはture。新パノラマも旧パノラマも同様。
        if( !$csite_panorama_kokai_fl ){
            return false;
        }

        // data_modelのパノラマURLが設定されている。新パノラマも旧パノラマも同様。
        if( !isset($dataModel->panorama) || !isset($dataModel->panorama['url']) || !isset($dataModel->panorama_contents_id) ){
            return false;
        }

        // パノラマコンテンツIDの先頭文字が、アンダースコア（_）、もしくはハイフン（-）で始まる場合、新パノラマ。
        // それ以外の場合は旧パノラマ。
        $first = substr($dataModel->panorama_contents_id, 0, 1);
        if( $first == "_" || $first =="-"  ) {
            return false;
        }

        // 旧パノラマはパノラマのサムネイルLが設定されている。
        if( !isset($dispModel->panorama) ||  !isset($dispModel->panorama['thumbnails']) ){
            return false;
        }

        //エラーメッセージがある場合は、パノラマ表示しない
        if(isset($dispModel->panorama['message'])){
            return false;
        }

        return true;
    }

    /**
     * VRパノラマ物件かどうか（新パノラマ）
     * 　・
     */
    public static function isVrPanorama($bukken)
    {
        $dispModel = (object) $bukken['display_model'];
        $dataModel = (object) $bukken['data_model'];


        $csite_panorama_kokai_fl = self::getVal('csite_panorama_kokai_fl', $dispModel,true);

        //  display_modelのcsite_panorama_kokai_flはture。新パノラマも旧パノラマも同様。
        if( !$csite_panorama_kokai_fl ){
            return false;
        }

        // data_modelのパノラマURLが設定されている。新パノラマも旧パノラマも同様。
        if( !isset($dataModel->panorama) || !isset($dataModel->panorama['url']) || !isset($dataModel->panorama_contents_id) ){
            return false;
        }

        // パノラマコンテンツIDの先頭文字が、アンダースコア（_）、もしくはハイフン（-）で始まる場合、新パノラマ。
        // それ以外の場合は旧パノラマ。
        $first = substr($dataModel->panorama_contents_id, 0, 1);
        if( $first != "_" && $first != "-"  ) {
            return false;
        }

        return true;
    }

    const PANORAMA_TYPE_NONE  = '0';
    const PANORAMA_TYPE_MOVIE = '1';
    const PANORAMA_TYPE_PHOTO = '2';
    const PANORAMA_TYPE_VR    = '3';

    /**
     * 各物件のdisplay_modelを参照し、その物件が提供するパノラマ種別を返す
     * 　・
     */
    public static function getPanoramaType($dispModel)
    {
        $csite_panorama_kokai_fl = self::getVal('csite_panorama_kokai_fl', $dispModel, true);

        //  display_modelのcsite_panorama_kokai_flはture。新パノラマも旧パノラマも同様。
        if( !$csite_panorama_kokai_fl ){
            return self::PANORAMA_TYPE_NONE;
        }

        $panoramaContentsCode = self::getVal('panorama_contents_cd', $dispModel, true);

        if(is_null($panoramaContentsCode)) {
            return self::PANORAMA_TYPE_NONE;
        }
        switch($panoramaContentsCode) {
            case '1':
                $panoramaWebvrFl = self::getVal('panorama_webvr_fl', $dispModel, true);
                if(!is_null($panoramaWebvrFl) && $panoramaWebvrFl) {
                    return self::PANORAMA_TYPE_VR;
                } else {
                    return self::PANORAMA_TYPE_MOVIE;
                }
                break;
            case '2':
                return self::PANORAMA_TYPE_PHOTO;
                break;
            default:
                break;
        }
        return self::PANORAMA_TYPE_NONE;
    }

    /*
     * （NHP-2822）
     *  ■詳細画面：使用部分面積を取得する（売店舗・売事務所・売その他）
     *   仕様
     *    ・物件の上位種目が「売事業用（一括）(05)」の場合　　：display_model.tatemono_nobe_ms を表示する
     *    ・物件の上位種目が「売事業用（一括）(05)」以外の場合：display_model.tatemono_ms を表示する
     *
     */
    public static function getTatemonoMsVal($shumoku, $dataModel, $dispModel){

        // 売店舗・売事務所・売その他、以外はこのメソッドを使用しない想定。
        //  「tatemono_ms」を参照にしておく。
        if( !self::TYPE_URI_TENPO &&
            !self::TYPE_URI_OFFICE &&
            !self::TYPE_URI_OTHER){
            return self::getVal('tatemono_ms', $dispModel);
        }

        $key = (self::getVal('joi_shumoku_cd', $dispModel) == '05') ? 'tatemono_nobe_ms' : 'tatemono_ms';
        return self::getVal($key, $dispModel);
    }



    /**
     * ＜要注意＞このメソッドは廃止予定です。
     * $valueが、Null、もしくは空文字の場合
     * - (ハイフン)を返す。
     * それ以外は、実の値を返す。
     * @return 値 or - (ハイフン)
     * @deprecated
     */
    public static function getValue($value)
    {
        return (is_null($value) || empty($value)) ?
            '-' : $value;
    }
         
    /**
     * 
     * @param string $varName
     * @param stdObject $stdObj display_model, data_model
     * @param boolean trueで値が入っていない場合はnullを返す。falseはハイフンを返す。
     */
    public static function getVal($varName, $stdObj, $null = false)
    {
        $value = null;

        if (isset($stdObj->{$varName}))
        {
            $stdArray = (array) $stdObj;
            $value = $stdArray[$varName];
        }
        
        // 値が入っていない場合、nullかハイフンを返す
        if (is_null($value) || empty($value)) 
        {
            $value = $null ? null : '-';
//         } else if (is_array($value)) {
//         	$result = null;
//         	foreach ($value as $entry) {
//         		$result .= $entry . ' ';
//         	}
//         	$value = is_null($result) ? '-' : $result;
        }
        return $value;
    }

    public static function getInquiryURL($type_cd)
    {
            $inquiryUrl = null;

            // 特集複数種目対応
            if (is_array($type_cd) && count($type_cd) === 1) {
                $type_cd = $type_cd[0];
            }
            if (is_array($type_cd)) {
                $compositeType = Estate\TypeList::getInstance()->getComopsiteTypeByShumokuCd($type_cd);
                switch ($compositeType) {
                    case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_1:
                    case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_2:
                    case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3:
                        // 貸事業
                        $type_cd = self::TYPE_KASI_TENPO;
                        break;
                    case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1:
                    case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2:
                        // 売り居住
                        $type_cd = self::TYPE_MANSION;
                        break;
                    case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1:
                    case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2:
                        // 売り事業
                        $type_cd = self::TYPE_URI_TENPO;
                        break;
                }
            }

            switch ($type_cd)
            {
                case self::TYPE_CHINTAI:
                    $inquiryUrl = '/inquiry/kasi-kyojuu/edit/';
                    break;
                case self::TYPE_KASI_TENPO:
                case self::TYPE_KASI_OFFICE:
                case self::TYPE_PARKING:
                case self::TYPE_KASI_TOCHI:
                case self::TYPE_KASI_OTHER:
                    $inquiryUrl = '/inquiry/kasi-jigyou/edit/';
                    break;
                case self::TYPE_MANSION:
                case self::TYPE_KODATE:
                case self::TYPE_URI_TOCHI:
                    $inquiryUrl = '/inquiry/uri-kyojuu/edit/';
                    break;
                case self::TYPE_URI_TENPO:
                case self::TYPE_URI_OFFICE:
                case self::TYPE_URI_OTHER:
                    $inquiryUrl = '/inquiry/uri-jigyou/edit/';
                    break;
                default:
                    throw new Exception('Illegal Argument.');
                    break;
            }
        return $inquiryUrl;
    }

    // ATHOME_HP_DEV-4841 : 現在利用中の物件種目一覧を引数追加
    public static function getShumokuFromBukkenModel($dispModel, $dataModel, $searchSetting=null) {
        if(is_null($searchSetting)) {
            return $dispModel->csite_bukken_shumoku_cd[0];
        }

        // 物件詳細の種目一覧(dispModel->csite_bukken_shumoku_cd)を、現在利用中の物件種目でフィルタリング
        $bothShumokuCd = array_intersect($dispModel->csite_bukken_shumoku_cd, $searchSetting);
        if(count($bothShumokuCd) > 0) {
            $bothShumokuCd = array_values($bothShumokuCd);
            sort($bothShumokuCd);
            $shumoku = array_shift($bothShumokuCd);
            return $shumoku;
        } else {
            return $dispModel->csite_bukken_shumoku_cd[0];
        }
    }

    // ATHOME_HP_DEV-4841 : 現在利用中の物件種目一覧を引数追加
    public static function getDetailURL($dispModel, $dataModel, $searchSetting=null) {
        $shumoku = self::getShumokuFromBukkenModel($dispModel, $dataModel, $searchSetting);
        $type_ct = self::getShumokuCtByCd($shumoku);
        return "/${type_ct}/detail-" . $dispModel->id . "/";
    }
    
    // @TODO 後で入れ替え	
	const TYPE_CHINTAI = '5007';
	const TYPE_KASI_TENPO = '5008';
	const TYPE_KASI_OFFICE = '5009';
	const TYPE_PARKING = '5011';
	const TYPE_KASI_TOCHI = '5012';
	const TYPE_KASI_OTHER = '5010';
	const TYPE_MANSION = '5003';
	const TYPE_KODATE = '5002';
	const TYPE_URI_TOCHI = '5001';
	
	const TYPE_URI_TENPO = '5004';
	const TYPE_URI_OFFICE = '5005';
	const TYPE_URI_OTHER = '5006';
	
	protected static $_codeList = [
	   'chintai' => '5007',
	   'kasi-tenpo' => '5008',
	   'kasi-office' => '5009',
	   'parking' => '5011',
	   'kasi-tochi' => '5012',
	   'kasi-other' => '5010',
       'mansion' => '5003',
	   'kodate' => '5002',
       'uri-tochi' => '5001',
	   'uri-tenpo' => '5004',
	   'uri-office' => '5005',
	   'uri-other' => '5006'
    ];
	protected static $_list = [
		self::TYPE_CHINTAI		=>'貸アパート・マンション・一戸建て',
		self::TYPE_KASI_TENPO	=>'貸店舗',
		self::TYPE_KASI_OFFICE	=>'貸事務所',
		self::TYPE_PARKING		=>'貸駐車場',
		self::TYPE_KASI_TOCHI	=>'貸土地',
		self::TYPE_KASI_OTHER	=>'貸ビル・貸倉庫・その他',
		self::TYPE_MANSION		=>'売マンション',
		self::TYPE_KODATE		=>'売一戸建て',
		self::TYPE_URI_TOCHI	=>'売土地',
		self::TYPE_URI_TENPO	=>'売店舗',
		self::TYPE_URI_OFFICE	=>'売事務所',
		self::TYPE_URI_OTHER	=>'売ビル・一括マンション・その他',
	];

    public static function canDisplayFdp($type, $fdp) {
        if(in_array($type,$fdp->fdp_type)) {
            return true;
        }
        return false;
    }

    public static function isFDP($pageInitialSettings) {
        $company = $pageInitialSettings->getCompany();
        if (!Estate\FdpType::getInstance()->isFrontFDP($company) || $company->cms_plan <= config('constants.cms_plan.CMS_PLAN_LITE')) {
            return false;
        }
        return true;
    }
	
    public static function getShuhenMapAnnotation($matchingLevel)
    {
        $mapAnnotation = '';
        if ($matchingLevel == "E") {
            $mapAnnotation = '※地図上に表示されるピンのアイコンは指定した位置に表示しております。<br>　実際の物件所在地とは異なる場合がございますので詳しくは当社までお問い合わせください。';
        }
        if ($matchingLevel == "8") {
            $mapAnnotation = '※地図上に表示されるピンのアイコンは入力した情報を基にジオコーダーで緯度経度に変換し表示しております。<br>　実際の物件所在地とは異なる場合がございますので詳しくは当社までお問い合わせください。';
        }
        return $mapAnnotation;
    }

    public static function checkLatLon($bukken) {
        $dataModel = (object) $bukken['data_model'];
        return (isset($dataModel->ido) && isset($dataModel->keido) && $dataModel->ido && $dataModel->keido);
    }

    public static function checkKaiin($pageInitialSettings) {
        $kaiinNoList = array($pageInitialSettings->getMemberNo());
        $apiParam = new Models\KApi\KaiinListParams();
        $apiParam->setKaiinNos($kaiinNoList);
        $apiObj = new Models\KApi\KaiinList();
        return $apiObj->get($apiParam, '会員リスト取得');
    }

    public static function timeMantains(){
        return [
            'dateTime' =>[
                [
                    'start' => '2020-01-02 12:00:00',
                    'end' => '2020-01-02 17:00:00',
                ],
                [
                    'start' => '2021-01-02 09:00:00',
                    'end' => '2021-01-02 13:00:00',
                ],
                [
                    'start' => '2021-01-18 09:00:00',
                    'end' => '2021-02-10 18:00:00',
                ],
            ],
            'tpl' => [
                'maintain20200102',
                'maintain20210102',
                'maintain20210118',
            ]
        ];
    }

    public static function checkDateMaitain() {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $today  = new \DateTime(date("Y-m-d H:i:s"), $timezone);
        $timeMantains = self::timeMantains();
        foreach ($timeMantains['dateTime'] as $index => $dateTime) {
            $start  = new \DateTime($dateTime['start'], $timezone);
            $end  = new \DateTime($dateTime['end'], $timezone);
            if (($today->getTimestamp() >= $start->getTimestamp()) && ($today->getTimestamp() <= $end->getTimestamp())) {
                return $timeMantains['tpl'][$index];
            }
        }
        return 'maintain';
    }

    public static function setBukkenIdPublish($houseIds, $prefs = null)
    {
        $result = [];
        if (!is_array($houseIds)) {
            $houseIds = explode(',', $houseIds);
        }
        foreach($houseIds as $id) {
            $ids = explode(':', $id);
            if (!isset($ids[1]) || is_null($prefs)) {
                $result[] = $ids[0];
            } else {
                if (!in_array($ids[1], $prefs)) continue;
                $result[] = $ids[0];
            }
        }
        return  implode(',', $result);
    }

    public static function setPrefInId($houseIds, $prefs) 
    {
        $result = [];
        if (!is_array($houseIds)) {
            $houseIds = explode(',', $houseIds);
        }
        foreach($houseIds as $id) {
            $ids = explode(':', $id);
            if(isset($ids[1])) {
                if (!in_array($ids[1], $prefs) || in_array($ids[1], $result)) continue;
                $result[] = $ids[1];
            }
        }
        return  array_unique($result);
    }

    public static function getConditionSearch($settingRow, $settings, $params, $isShumoku = false, $isSpecial = false) {
        
        $type_ct = array();
        foreach ($settingRow->enabled_estate_type as $type) {
            $type_ct[] = Estate\TypeList::getInstance()->getUrl($type);
        }
        $params->type_ct = $type_ct;
        $params = new Models\Params($params);
        $pNames = new Models\ParamNames($params);
        $shumoku    = $pNames->getShumokuCd();
        if (is_array($shumoku)) {
            $shumoku = implode(',', $shumoku);
        }
        $condition = array();
        if ($isShumoku) {
            $condShumoku = 'csite_bukken_shumoku_cd:'.$shumoku;
        }
        if ($isSpecial) {
            if (!$settingRow->area_search_filter->has_search_page) {
                $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
            }
        }
        if ($settingRow->area_search_filter->hasAreaSearchType() ||
            $settingRow->area_search_filter->hasSpatialSearchType()) {
            if (!empty($shozaichiCodes = $settingRow->area_search_filter->getShozaichiCodes())) {
                $condArea = 'shozaichi_cd:'.implode(",", $shozaichiCodes);
                if ($isShumoku) {
                    $condition[] = 'AN'.'D('.implode('%3b', array($condShumoku, $condArea)).')';
                } else {
                    $condition[] = $condArea;
                }
            }
        }

        if ($settingRow->area_search_filter->hasLineSearchType()){
            if (!empty($lineCodes = $settingRow->area_search_filter->area_4->getAll())) {
                $condLine = 'ensen_eki_cd:'.implode(",", $lineCodes);
                if ($isShumoku) {
                    $condition[] = 'AN'.'D('.implode('%3b', array($condShumoku, $condLine)).')';
                } else {
                    $condition[] = $condLine;
                }
            }
        }

        return implode('%3b', $condition);
    }

    public static function getConditionAllSearch($params, $settings) {
        $estateSettngRows = $settings->company->getHpEstateSettingRow()->getSearchSettingAll();
        $condition = array();
        foreach($estateSettngRows as $estateSettngRow){
            $condition[] = self::getConditionSearch($estateSettngRow->toSettingObject(), $settings, $params, true);
            
        }
        return implode('%3b', $condition);
    }

    // public function checkPublishHouse($bukken, $settings, $params) {
    //     $dataModel = (object) $bukken['data_model'];
    //     $dispModel = (object) $bukken['display_model'];
    //     $check = false;
    //     $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
    //     $kenCd = $this->getVal('ken_cd', $dispModel);
        
        
    //     $searchFilter = $settingRow->area_search_filter;
    //     if ($searchFilter->area_1) {
    //         $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
    //         if (!in_array($kenCd, $searchFilter->area_1)) {
    //             $check = true;
    //         } elseif (!in_array($shikugun_cd, $searchFilter->area_2[$kenCd])) {
    //             $check = true;
    //         }
    //     }
    //     if ($searchFilter->area_3) {
    //         $kotus = $this->getVal('kotsus', $dataModel, true);
    //         $ensen_cd = array();
    //         foreach($kotus as $k) {
    //             if (isset($k['ensen_cd']) && isset($k['eki_cd'])) {
    //                 $ensen_cd[] = implode(':', [$k['ensen_cd'],$k['eki_cd']]);
    //             }
    //         }
    //         if (!count( array_intersect($searchFilter->area_4[$kenCd], $ensen_cd))) {
    //             $check = true;
    //         }
    //     }
    // }

    /**
     * 文字列の先頭と末尾のみ置換する（ハイライト用）
     *
     */
    public static function replaceStringHighlight($item, $search, $replace) {
        $searchLen = mb_strlen($search);
        $pos = mb_strpos($item, $search);
        if ($pos !== false) {
            if ($pos == 0) {
                $item = $replace.mb_substr($item, $searchLen);
            }
            $endOffset = mb_strlen($item) - $searchLen;
            $pos = mb_strpos($item, $search, $endOffset);
            if ($pos !== false) {
                $item = mb_substr($item, 0, $pos).$replace;
            }
        }
        return $item;
    }

    public static function getShumokuDispModel($dispModel) {
        $shumoku_nm = self::getVal('shumoku_nm', $dispModel);
        if (strip_tags($shumoku_nm) == '一括売マンション') {
            $shumoku_nm = str_replace(array('<em>一括</em>', '一括'), '一棟', $shumoku_nm);
            
        }
        if (strip_tags($shumoku_nm) == '売アパート') {
            $shumoku_nm = '一棟'.$shumoku_nm;
        }
        return $shumoku_nm;
    }

    public static function replaceSsiteBukkenTitle($title) {
        if (strpos($title, '売アパート') !== false) {
            return str_replace('売アパート', '一棟売アパート', $title);
        }
        if (strpos($title, '一括売マンション') !== false) {
            return str_replace('一括売マンション', '一棟売マンション', $title);
        }
        return $title;
    }

    public static function selectRequest($url_func,$data_url)
    {
        if(isset(self::$selectRequest[$url_func.$data_url]))
        {
            return self::$selectRequest[$url_func.$data_url];
        }
        return null;
    }

    public static function insertRequest($url_func, $data_url,$res)
    { 
        self::$selectRequest[$url_func.$data_url]=$res;
    }

    public static function resetRequsest()
    {
        self::$selectRequest=[];
    }

}
