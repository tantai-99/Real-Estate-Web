<?php
namespace Modules\V1api\Models\BApi;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Model\Estate;
use Library\Custom\Estate\Setting\SearchFilter\Front;

class Map extends AbstractBApi
{
    private $center_sort = true;

    /**
     * @param MapSearchParams
     * @return JSON
     */
    public function search(
        MapSearchParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_BUKKEN_SPATIAL_SEARCH, $params, $procName);
    }

    /**
     * 地図の中心点を返す
     * BukkenSearch->getBukkenListを参考
     */
    public function getCenter(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
    		Front $searchFilter)
    {
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        $shumoku    = $pNames->getShumokuCd();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        $eki_cd = $pNames->getEkiCd();
        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();

        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new MapSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setCoordinatesGroupingCd("01");
        $apiParam->setChizuHyojiKaFl(true);
        $apiParam->setChizuKensakuFukaFl(true);

        if ($ken_cd) {
        	// cmsで設定されている所在地を全設定
        	$areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt( $type_ct )->toSettingObject()->area_search_filter;
        	$shozaichi_cds = $areaSearchFilter->getShozaichiCodes();
        	$search_cds = array() ;
        	foreach ( $shozaichi_cds as& $shozaichi_cd ) {
        		if ( strpos( $shozaichi_cd, $shikugun_cd ) !== false )
        		{
        			$search_cds[] = $shozaichi_cd	;
        		}
        	}
        	$apiParam->setShozaichiCd( $search_cds );
        }

        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $searchFilter);

        // 一件目の座標のみ取得
        if ($this->center_sort) {
            $apiParam->setCenterCondition($params);
            $apiParam->setPerPage(null);
        } else {
            $apiParam->setNoBukkens(1);
            $apiParam->setmaxCoordinates(1);
            $apiParam->disableFacets();
        }

        $datas = $this->search($apiParam, 'MAPCENTER');
        if ($this->center_sort) {
            if (!empty($datas['bukkens'][0])) {
                if (!property_exists($apiParam, 'joi_shumoku_cd') || (property_exists($apiParam, 'joi_shumoku_cd') && in_array('02', explode($apiParam->joi_shumoku_cd)))) {
                    $bukkens = array_filter($datas['bukkens'], function( $value) {
                        return $value['display_model']['joi_shumoku_cd'] == '02';
                    });
                    if (!empty($bukkens = array_values($bukkens))) {
                        return [
                            'lat' => $bukkens[0]['data_model']['ido']
                            ,'lng' => $bukkens[0]['data_model']['keido']
                        ];
                    }
                }
                return [
                    'lat' => $datas['bukkens'][0]['data_model']['ido']
                    ,'lng' => $datas['bukkens'][0]['data_model']['keido']
                ];
            }
        } else {
            if (!empty($datas['coordinates'][0])) {
                return $datas['coordinates'][0];
            }
        }
        throw new \Exception('物件がないため中心点が出せません', 404);
    }

    /**
     * 物件APIに接続して物件一覧を取得します。（地図内の一覧とモーダルの一覧を併用）
     *BukkenSearch->getBukkenListを参考
     */
    public function getBukkenList(
            Params $params,
            Settings $settings,
            ParamNames $pNames,
            Front $searchFilter,
            $isModal)
    {
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();

        // cmsで設定されている所在地を全設定
        $areaSearchFilter = $settings->search->getSearchSettingRowByTypeCt($type_ct)->toSettingObject()->area_search_filter;
        $shozaichi_cd = $areaSearchFilter->getShozaichiCodes();

        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new MapSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        $apiParam->setChizuHyojiKaFl(true);

        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort());
        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $searchFilter);

        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setDislayModel();
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
            }
        }

        $apiParam->setPerPage($params->getPerPage());
        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort());

        $apiParam->setShozaichiCd($shozaichi_cd);
        $apiParam->setCoordinatesGroupingCd("01");
        $apiParam->setChizuKensakuFukaFl(true);

        if ($isModal) {
            $apiParam->setNoCoordinates(1);
        } else {
            $apiParam->setNoBukkens(1);
        }
        // 緯度経度
        $nansei = $params->getIdoKeidoNansei();
        $hokutou = $params->getIdoKeidoHokuto();
        if (empty($nansei) || empty($hokutou)) {
            throw new \Exception('緯度経度のパラメータは必須です', 404);
        }
        $apiParam->setIdoKeidoNansei($nansei);
        $apiParam->setIdoKeidoHokuto($hokutou);

        return $this->search($apiParam, 'MAPESTATE');
    }
    /**
     * 物件APIに接続して物件一覧を取得します。（右カラム）
     */
    public function getBukkenListForIds(
            Params $params,
            Settings $settings,
            ParamNames $pNames,
            Front $searchFilter,
            $isModal)
    {
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        $shumoku    = $pNames->getShumokuCd();
        // BApi用パラメータ作成
        $apiParam = new MapSearchParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);

        $apiParam->setPerPage($params->getPerPage());
        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort());

        $apiParam->setId($params->getBukkenId());

        $apiParam->setNoCoordinates(1);
        if ($params->getFulltext() != null) {
                $apiParam->setFulltext(urlencode($params->getFulltext()));
                // $apiParam->setDislayModel();
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
        }

        return $this->search($apiParam, 'MAPESTATELIST');
    }

    /**
     * 地図の中心点を返す
     * BukkenSearch->getBukkenListSplを参考
     */
    public function getCenterSpl(
            Params $params,
            Settings $settings,
            ParamNames $pNames,
            $searchFilter,
            $frontSearchFilter)
    {
        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

        // ATHOME_HP_DEV-5001
        // 特集 & choson_search_enabled = 0 の場合は検索設定の area_5, area_6をくっつける
        $spChosonFlg = false;
        if($specialSetting->area_search_filter->choson_search_enabled == 0) {
            // 特集指定の物件種別の『物件検索設定』地域設定を取得する
            $estateClassArea = $settings->search->getSearchSettingRowByTypeId($specialSetting->enabled_estate_type[0])->toSettingObject()->area_search_filter;

            // 物件検索設定に『地域から探す(1)』かつ『町名まで検索させる』を指定している場合 area_5,area_6を特集にコピーしておく
            if(in_array((string)Estate\SearchTypeList::TYPE_AREA, $estateClassArea->search_type) && $estateClassArea->choson_search_enabled == 1) {
                if(!empty($estateClassArea->area_5->getAll())) {
                    $specialSetting->area_search_filter->area_5 = $estateClassArea->area_5;
                    $spChosonFlg = true;

                    if(!empty($estateClassArea->area_6->getAll())) {
                        $specialSetting->area_search_filter->area_6 = $estateClassArea->area_6;
                    }
                }
            }
        }

        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_id = $specialSetting->enabled_estate_type;
        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();

        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new MapSearchParams();
        $comId = $params->getComId();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());
        $apiParam->setCoordinatesGroupingCd("01");
        $apiParam->setChizuKensakuFukaFl(true);

        if ($ken_cd) {
            // ATHOME_HP_DEV-5001
            // 物件検索設定の町村もくっつける
            if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 1;

        	// cmsで設定されている所在地を全設定
        	$shozaichi_cds = $areaSearchFilter->getShozaichiCodes()	;
        	$search_cds = array() ;
        	foreach ( $shozaichi_cds as& $shozaichi_cd ) {
        		if ( strpos( $shozaichi_cd, $shikugun_cd ) !== false )
        		{
        			$search_cds[] = $shozaichi_cd	;
        		}
        	}
        	$apiParam->setShozaichiCd( $search_cds )	;

            // ATHOME_HP_DEV-5001 - 解除 -
            if($spChosonFlg) $specialSetting->area_search_filter->choson_search_enabled = 0;

        }

        $apiParam->setPerPage($params->getPerPage());
        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort());
        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true);
        // 「2次広告自動公開物件」
        // カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
        // $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
        // 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
        // $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
        // $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id));
        } else {
            // 「オーナーチェンジ」
            $apiParam->setOwnerChangeFl($specialSetting->owner_change);
            // 「自社物件」「2次広告物件」「2次広告自動公開物件」
            $apiParam->setKokaiType($specialSetting->jisha_bukken, $specialSetting->niji_kokoku, $specialSetting->niji_kokoku_jido_kokai);
            // 検索エンジンレンタルのみ公開の物件だけを表示する
            $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
            if ($methodSetting->hasRecommenedMethod($specialSetting->method_setting)) {
                $apiParam->setOsusumeKokaiFl('true');
            } else {
                // 「エンド向け仲介手数料不要の物件」
                $apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
                // 「手数料」
                $apiParam->setSetTesuryo($specialSetting->tesuryo_ari_nomi, $specialSetting->tesuryo_wakare_komi);
                // 「広告費」
                $apiParam->setKokokuhiJokenAri($specialSetting->kokokuhi_joken_ari);
            }
        }

        // 一件目の座標のみ取得
        if ($this->center_sort) {
            $apiParam->setCenterCondition($params);
            $apiParam->setPerPage(null);
        } else {
            $apiParam->setNoBukkens(1);
            $apiParam->setmaxCoordinates(1);
            $apiParam->disableFacets();
        }

        $datas = $this->search($apiParam, 'SPL_MAPCENTER');
        if ($this->center_sort) {
            if (!empty($datas['bukkens'][0])) {
                if (in_array('5102', $apiParam->get('csite_bukken_shumoku_cd'))) {
                    $bukkens = array_filter($datas['bukkens'], function( $value) {
                        return $value['display_model']['joi_shumoku_cd'] == '02';
                    });
                    if (!empty($bukkens = array_values($bukkens))) {
                        return [
                            'lat' => $bukkens[0]['data_model']['ido']
                            ,'lng' => $bukkens[0]['data_model']['keido']
                        ];
                    }
                }
                return [
                    'lat' => $datas['bukkens'][0]['data_model']['ido']
                    ,'lng' => $datas['bukkens'][0]['data_model']['keido']
                ];
            }
        } else {
            if (!empty($datas['coordinates'][0])) {
                return $datas['coordinates'][0];
            }
        }
        throw new \Exception('物件がないため中心点が出せません', 404);
    }

    /**
     * 物件APIに接続して物件一覧を取得します。（地図内の一覧とモーダルの一覧を併用）
     */
    public function getBukkenListSpl(
            Params $params,
            Settings $settings,
            ParamNames $pNames,
            $searchFilter,
            $frontSearchFilter,
            $isModal)
    {
        $specialSetting = $settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;

        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得

        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // BApi用パラメータ作成
        $apiParam = new MapSearchParams();
        $comId = $params->getComId();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($pNames->getShumokuCd());

        // cmsで設定されている所在地を全設定
        $shozaichi_cd = $areaSearchFilter->getShozaichiCodes();
        $apiParam->setShozaichiCd($shozaichi_cd);
        $apiParam->setChizuKensakuFukaFl(true);

        $apiParam->setPerPage($params->getPerPage());
        $apiParam->setPage($params->getPage());
        $apiParam->setOrderBy($params->getSort());
        // こだわり条件
        $apiParam->setSearchFilter($searchFilter, $frontSearchFilter, true,$specialSetting->isSpecialShumokuSort());

        if (!empty($params->getSearchFilter())) {
            if (!empty($params->getSearchFilter()["fulltext_fields"])) {
                $apiParam->setFulltext(urlencode($params->getSearchFilter()["fulltext_fields"]));
                $apiParam->setDislayModel();
                $apiParam->setFulltextFields();
                $apiParam->setDataModelFulltextFields();
                $apiParam->fieldHighlightLenght();
            }
        }
        
        // 「2次広告自動公開物件」
        // カラム変更(second_estate_enabled -> niji_kokoku_jido_kokai) に伴い削除
        // $apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
        // 「２次広告物件（他社物件）のみ抽出」
        // 新カラムjisha_bukken, niji_kokoku の組み合わせで制御するため削除
        // $apiParam->setOnlySecond($specialSetting->only_second);
        // 「２次広告物件除いて（自社物件）抽出」
        // $apiParam->setExcludeSecond($specialSetting->exclude_second);
        
        if ($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
            $apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id));
        } else {
            // 「オーナーチェンジ」
            $apiParam->setOwnerChangeFl($specialSetting->owner_change);
            // 「自社物件」「2次広告物件」「2次広告自動公開物件」
            $apiParam->setKokaiType($specialSetting->jisha_bukken, $specialSetting->niji_kokoku, $specialSetting->niji_kokoku_jido_kokai);
            // 検索エンジンレンタルのみ公開の物件だけを表示する
            $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
            if ($methodSetting->hasRecommenedMethod($specialSetting->method_setting)) {
                $apiParam->setOsusumeKokaiFl('true');
            } else {
                // 「エンド向け仲介手数料不要の物件」
                $apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
                // 「手数料」
                $apiParam->setSetTesuryo($specialSetting->tesuryo_ari_nomi, $specialSetting->tesuryo_wakare_komi);
                // 「広告費」
                $apiParam->setKokokuhiJokenAri($specialSetting->kokokuhi_joken_ari);
            }
        }

        if ($isModal) {
            $apiParam->setNoCoordinates(1);
        } else {
            $apiParam->setNoBukkens(1);
        }
        // 緯度経度
        $nansei = $params->getIdoKeidoNansei();
        $hokutou = $params->getIdoKeidoHokuto();
        $apiParam->setCoordinatesGroupingCd("01");
        if (empty($nansei) || empty($hokutou)) {
            throw new \Exception('緯度経度のパラメータは必須です', 404);
        }
        $apiParam->setIdoKeidoNansei($nansei);
        $apiParam->setIdoKeidoHokuto($hokutou);

        return $this->search($apiParam, 'SPL_MAPESTATE');
    }
}
