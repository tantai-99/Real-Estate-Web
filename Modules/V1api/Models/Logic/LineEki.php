<?php
namespace Modules\V1api\Models\Logic;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\ParamNames;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\BApi;
use Library\Custom\Estate\Setting\SearchFilter\Front;

class LineEki
{
	/**
	 * 駅選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 * @param $fromLine trueの場合、CMS設定の沿線からデータ指定沿線を取得
	 */
	public function getEkiList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            Front $searchFilter=null,
			$fromLine = false)
	{
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
	
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		if ($fromLine) {
			// CMS設定から指定沿線を取得
			// 検索設定の沿線コードを取得
			$ensen_cd_api = $settings->search->getEnsen($type_id, $ken_cd);
		} else {
			// リクエストパラメータから指定沿線を取得
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}

		// BApi用パラメータ作成
		$apiParam = new BApi\EkiParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		// 		$apiParam->setKenCd($ken_cd);
		$apiParam->setEnsenCd($ensen_cd_api);
		// 		$apiParam->setEnsenRoman($params->getEnsenCt());
		$apiParam->setEnsenEkiCd($ensen_eki_cd);

        // こだわり条件
        if($searchFilter) {
            $apiParam->setSearchFilter($searchFilter);
        }

            // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\Eki();
		$ekiWithEnsen = $apiObj->getEkiWithEnsen($apiParam, 'EKI_SELECT');
		return $ekiWithEnsen;
	}
	
	public function getEkiSettingByKenEnsen(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			Front $searchFilter)
    {
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
	
		$comId = $params->getComId();
		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
		foreach ($ensenObjList as $ensenObj) {
			array_push($ensen_cd_api, $ensenObj['code']);
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = $settings->search->getEkiByKenEnsen($type_id, $ken_cd, $ensen_cd_api);
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}
		return $ensen_eki_cd;
	}
	
	public function getEkiListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter,
			$fromLine = false)
	{
		$type_ct = $params->getTypeCt();
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
		// 沿線の取得（複数指定の場合は使用できない）
		$ensen_ct = $params->getEnsenCt(); // 単数or複数
		$ensen_cd = $pNames->getEnsenCd();
		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;
		/**
		 * 駅選択
		 */
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		if ($fromLine) {
			// CMSから沿線指定を取得
			$ensen_cd_api = $areaSearchFilter->area_3->getDataByPref($ken_cd);
			if (!$ensen_cd_api) {
				throw new \Exception('都道府県の沿線が設定されていない', 404);
			}
		} else {
			// リクエストパラメータから沿線指定を取得
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = [];
		$kenEkiCodes = $areaSearchFilter->area_4;
		foreach ($kenEkiCodes as $ken => $ekiCodes) {
			foreach ($ekiCodes as $code) {
				$ekiCodeParts = explode(':', $code);
				if (in_array($ekiCodeParts[0], $ensen_cd_api)) {
					$ensen_eki_cd[] = $code;
				}
			}
		}
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}
		// 		$ekiCodes = $specialSetting->area_search_filter->area_4->getDataByPref($ken_cd);
		// 		foreach ($ekiCodes as $code) {
		// 			$ekiCodeParts = explode(':', $code);
		// 			if (in_array($ekiCodeParts[0], $ensen_cd_api)) {
		// 				$ensen_eki_cd[] = $code;
		// 			}
		// 		}
		// 		if (!$ensen_eki_cd) {
		// 			throw new Zend_Controller_Router_Exception('都道府県の駅が設定されていない', 404);
		// 		}
	
		// BApi用パラメータ作成
		$apiParam = new BApi\EkiParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		// 		$apiParam->setKenCd($ken_cd);
		$apiParam->setEnsenCd($ensen_cd_api);
		$apiParam->setEnsenEkiCd($ensen_eki_cd);
	
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
		
		// 絞り込み検索設定
		$apiParam->setSearchFilter($specialSetting->search_filter, null, true);

        // こだわり条件
        if($searchFilter) {
            $apiParam->setSearchFilter($searchFilter, null, true);
        }


        // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\Eki();
		return $apiObj->getEkiWithEnsen($apiParam, 'SPL_EKI_SELECT');
	}
	public function getEkiSettingByKenEnsenSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		$type_ct = $params->getTypeCt();
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
	
		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
        $areaSearchFilter = $specialSetting->area_search_filter;
	
		$comId = $params->getComId();
		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
		foreach ($ensenObjList as $ensenObj) {
			array_push($ensen_cd_api, $ensenObj['code']);
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = array();
		$ekiCodes = $areaSearchFilter->area_4->getDataByPref($ken_cd);
		foreach ($ekiCodes as $code) {
			$ekiCodeParts = explode(':', $code);
			if (in_array($ekiCodeParts[0], $ensen_cd_api)) {
				$ensen_eki_cd[] = $code;
			}
		}
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}
		return $ensen_eki_cd;
	}
	
	/**
	 * 沿線選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getLineList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		$type_ct = $params->getTypeCt();
		$type_id =Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku    = $pNames->getShumokuCd();
		$ken_cd  = $pNames->getKenCd();
	
// 		$comId = $params->getComId();
// 		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// 検索設定の沿線コードを取得
		$ensen_cd = $settings->search->getEnsen($type_id, $ken_cd);
	
		// BApi用パラメータ作成
		$apiParam = new BApi\EnsenListParams();
// 		$apiParam->setGroupId($comId);
// 		$apiParam->setKaiinLinkNo($kaiinLinkNo);
// 		$apiParam->setCsiteBukkenShumoku($shumoku);
		$apiParam->setKenCd($ken_cd);
		$apiParam->setGrouping($apiParam::GROUPING_TYPE_TRUE);
		$apiParam->setEnsenCd($ensen_cd);

        // 結果JSONを元に要素を作成。
		$apiObj = new BApi\EnsenList();
		$ensenWithGroup = $apiObj->getEnsen($apiParam, 'LINE_SELECT');
		return $ensenWithGroup;
	}

	/**
	 * 沿線選択画面用のデータリストを返す。(ATHOME_HP_DEV-4901)
	 * @param numeric $ken_cd
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 * @param Front $searchFilter
	 * @param $fromLine trueの場合、CMS設定の沿線からデータ指定沿線を取得
	 */
	public function getPrefLineCountList(
			$ken_cd,
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            Front $searchFilter=null,
			$fromLine = false)
	{
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku = $pNames->getShumokuCd();
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		if ($fromLine) {
			// CMS設定から指定沿線を取得
			// 検索設定の沿線コードを取得
			$ensen_cd_api = $settings->search->getEnsen($type_id, $ken_cd);
		} else {
			// リクエストパラメータから指定沿線を取得
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}

		// ATHOME_HP_DEV-5218
		$use_ensen_api = false;		// ensen/search.json API 利用の有無

		// BApi用パラメータ作成
		if($use_ensen_api) {
			$apiParam = new BApi\EnsenParams();		// <- 沿線検索用
			$apiObj = new BApi\Ensen();
		} else {
			$apiParam = new BApi\EkiParams();		// <- 駅検索用
			$apiObj = new BApi\Eki();
		}

		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		$apiParam->setEnsenCd($ensen_cd_api);
		$apiParam->setEnsenEkiCd($ensen_eki_cd);
		if($use_ensen_api) $apiParam->setKenCd($ken_cd);		// <- 沿線時のみ

		// こだわり条件: 特集のときは必要
		if($searchFilter) {
			$apiParam->setSearchFilter($searchFilter);
		}

		if($use_ensen_api) {
			$ensenWithGroup = $apiObj->getEnsenWithGroup($apiParam, 'LINE_SELECT_COUNT');
		} else {
			$resultEkiJson = $apiObj->getEkiWithEnsen($apiParam, 'LINE_SELECT_COUNT');

			$ensenList = [];

			foreach($resultEkiJson['ensens'] as $ensen) {
				$count = 0;
				foreach($ensen['ekis'] as $eki) {
					$count += intval($eki['count']);
				}
				$ensen['count'] = $count;
				unset($ensen['ekis']);
				$ensenList[] = $ensen;
			}

			$ensenWithGroup = [];
			$ensenWithGroup['ensens'] = [];
			$ensenWithGroup['ensens'][] = [ 'ensens' => $ensenList ];
		}

		$result = [];
		foreach ($ensenWithGroup['ensens'][0]['ensens'] as $ensen) {
			$result[ $ensen['code'] ] = $ensen['count'];
		}
		return $result;
	}

	/**
	 * 特集用沿線選択画面用のデータリストを返す。(ATHOME_HP_DEV-4901)
	 * @param numeric $ken_cd
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 * @param (Object) $searchFilter  Library\Custom\Estate\Setting\SearchFilter\Special/Library\Custom\Estate\Setting\SearchFilter\Front
	 * @param $fromLine trueの場合、CMS設定の沿線からデータ指定沿線を取得
	 */
	public function getPrefLineCountListSpl(
			$ken_cd,
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter,
			$fromLine = false)
	{
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku = $pNames->getShumokuCd();

		// 沿線の取得（複数指定の場合は使用できない）
		$ensen_ct = $params->getEnsenCt(); // 単数or複数
		$ensen_cd = $pNames->getEnsenCd();

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		$specialSetting = $specialRow->toSettingObject();
		/**
		 * 駅選択
		 */
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();

		// 検索設定の駅コードを取得
		$ensen_cd_api = array();
		if ($fromLine) {
			// CMSから沿線指定を取得
			$ensen_cd_api = $specialSetting->area_search_filter->area_3->getDataByPref($ken_cd);
			if (!$ensen_cd_api) {
				throw new \Exception('都道府県の沿線が設定されていない', 404);
			}
		} else {
			// リクエストパラメータから沿線指定を取得
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $params->getEnsenCt());
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = [];
		$kenEkiCodes = $specialSetting->area_search_filter->area_4;
		foreach ($kenEkiCodes as $ken => $ekiCodes) {
			foreach ($ekiCodes as $code) {
				$ekiCodeParts = explode(':', $code);
				if (in_array($ekiCodeParts[0], $ensen_cd_api)) {
					$ensen_eki_cd[] = $code;
				}
			}
		}
		if (!$ensen_eki_cd) {
			throw new \Exception('都道府県の駅が設定されていない', 404);
		}

		// ATHOME_HP_DEV-5218
		$use_ensen_api = false;		// ensen/search.json API 利用の有無

		// BApi用パラメータ作成
		if($use_ensen_api) {
			$apiParam = new BApi\EnsenParams();		// <- 沿線検索用
			$apiObj = new BApi\Ensen();
		} else {
			$apiParam = new BApi\EkiParams();		// <- 駅検索用
			$apiObj = new BApi\Eki();
		}

		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		$apiParam->setEnsenCd($ensen_cd_api);
		$apiParam->setEnsenEkiCd($ensen_eki_cd);
		if($use_ensen_api)	$apiParam->setKenCd($ken_cd);		// <- 沿線時のみ

		// ATHOME_HP_DEV-4901
		$methodSetting = Estate\SpecialMethodSetting::getInstance();

		if($methodSetting->hasInvidialMethod($specialSetting->method_setting)) {
			// 検索種別が『個別に物件を選択して特集をつくる』の場合は 物件リストを付与
			$apiParam->setId(Services\ServiceUtils::setBukkenIdPublish($specialSetting->houses_id));
		} else {
			// 「オーナーチェンジ」
			$apiParam->setOwnerChangeFl($specialSetting->owner_change);
			// 「自社物件」「2次広告物件」「2次広告自動公開物件」
			$apiParam->setKokaiType($specialSetting->jisha_bukken, $specialSetting->niji_kokoku, $specialSetting->niji_kokoku_jido_kokai);
			// 検索エンジンレンタルのみ公開の物件だけを表示する
			$apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
			if($methodSetting->hasRecommenedMethod($specialSetting->method_setting)) {
				// 検索種別が『おすすめ公開中の特集をつくる』の場合は osusume_kokai_fl付与
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

		// 絞り込み検索設定
		$apiParam->setSearchFilter($specialSetting->search_filter, null, true);

		// こだわり条件
		if($searchFilter) {
			$apiParam->setSearchFilter($searchFilter, null, true);
		}

		if($use_ensen_api) {
			$ensenWithGroup = $apiObj->getEnsenWithGroup($apiParam, 'LINE_SELECT_COUNT');
		} else {
			$resultEkiJson = $apiObj->getEkiWithEnsen($apiParam, 'LINE_SELECT_COUNT');

			$ensenList = [];

			foreach($resultEkiJson['ensens'] as $ensen) {
				$count = 0;
				foreach($ensen['ekis'] as $eki) {
					$count += intval($eki['count']);
				}
				$ensen['count'] = $count;
				unset($ensen['ekis']);
				$ensenList[] = $ensen;
			}

			$ensenWithGroup = [];
			$ensenWithGroup['ensens'] = [];
			$ensenWithGroup['ensens'][] = [ 'ensens' => $ensenList ];
		}

		$result = [];
		foreach ($ensenWithGroup['ensens'][0]['ensens'] as $ensen) {
			$result[ $ensen['code'] ] = $ensen['count'];
		}
		return $result;
	}


	// CMSから県をまたいで指定された沿線の駅をすべて取得。
	// 沿線ごとに駅の物件数をカウントしリストにして返す。
	public function getLineCountList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            $searchFilter)
    {

		// ATHOME_HP_DEV-4901
		// 都道府県がある場合と無い場合で集計処理を変える
        /**
         ATHOME_HP_DEV-5210 & 5218
		$ken_cd = null;
		$ken_cd = $pNames->getKenCd();

		if(isset($ken_cd)) {
			return $this->getPrefLineCountList($ken_cd, $params, $settings, $pNames, $searchFilter, true);
		}
        */

		// CMSから県をまたいで指定された沿線の駅をすべて取得。
		$ekiWithEnsen = $this->getEkiList($params, $settings, $pNames, $searchFilter, true);
		$result = array();
		foreach ($ekiWithEnsen['ensens'] as $ensen) {
			$cnt = 0;
			foreach ($ensen['ekis'] as $eki) {
				$cnt += $eki['count'];
			}
			$result[$ensen['code']] = $cnt;
		}
		return $result;
	}
	
	public function getLineListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		$ken_cd  = $pNames->getKenCd();
		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;
		/**
		 * 沿線選択
		 */
// 		$comId = $params->getComId();
// 		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		// 検索設定の沿線コードを取得
		$ensen_cd = $areaSearchFilter->area_3->getDataByPref($ken_cd);
		if (!$ensen_cd) {
			throw new \Exception('都道府県の沿線が設定されていない', 404);
		}

		// BApi用パラメータ作成
		$apiParam = new BApi\EnsenListParams();
// 		$apiParam->setGroupId($comId);
// 		$apiParam->setKaiinLinkNo($kaiinLinkNo);
// 		$apiParam->setCsiteBukkenShumoku($shumoku);
		$apiParam->setKenCd($ken_cd);
		$apiParam->setGrouping($apiParam::GROUPING_TYPE_TRUE);
		$apiParam->setEnsenCd($ensen_cd);
	
// 		// 検索エンジンレンタルのみ公開の物件だけを表示する
// 		$apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
// 		// 「2次広告自動公開物件」
// 		$apiParam->setNijiKokokuJidoKokaiFl($specialSetting->second_estate_enabled);
// 		// 「エンド向け仲介手数料不要の物件」
// 		$apiParam->setEndMukeEnabled($specialSetting->end_muke_enabled);
// 		// 「２次広告物件（他社物件）のみ抽出」
// 		$apiParam->setOnlySecond($specialSetting->only_second);
// 		// 「２次広告物件除いて（自社物件）抽出」
// 		$apiParam->setExcludeSecond($specialSetting->exclude_second);
		
		// 絞り込み検索設定
		// $apiParam->setSearchFilter($specialSetting->search_filter);
	
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\EnsenList();
		return $apiObj->getEnsen($apiParam, 'SPL_LINE_SELECT');
	}
	
	// CMSから県をまたいで指定された沿線の駅をすべて取得。
	// 沿線ごとに駅の物件数をカウントしリストにして返す。
	public function getLineCountListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
            $searchFilter)
	{
		// ATHOME_HP_DEV-4901
		// 都道府県がある場合と無い場合で集計処理を変える
        /**
         ATHOME_HP_DEV-5210 & 5218
		$ken_cd = null;
		$ken_cd = $pNames->getKenCd();
		if(isset($ken_cd)) {
			return $this->getPrefLineCountListSpl($ken_cd, $params, $settings, $pNames, $searchFilter, true);
		}
        */

		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		// 特集検索設定取得
		$specialSetting = $specialRow->toSettingObject();
		
		// CMSから県をまたいで指定された沿線の駅をすべて取得。
		//$ekiWithEnsen = $this->getEkiListSpl($params, $settings, $pNames, $specialSetting->search_filter, true);
        $ekiWithEnsen = $this->getEkiListSpl($params, $settings, $pNames, $searchFilter, true);

		$result = array();
		foreach ($ekiWithEnsen['ensens'] as $ensen) {
			$cnt = 0;
			foreach ($ensen['ekis'] as $eki) {
				$cnt += $eki['count'];
			}
			$result[$ensen['code']] = $cnt;
		}
		return $result;
	}
	
	/**
	 * モーダル用駅選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getModalEkiList(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			Front $searchFilter)
    {
		// 種目情報の取得
		$type_ct = $params->getTypeCt();
		$type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
		$shumoku    = $pNames->getShumokuCd();
		// 都道府県の取得
		$ken_cd  = $pNames->getKenCd();
	
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		$ensen_cd_api = array();
		$ensen_eki_cd = null;
	
		// BApi用パラメータ作成
		$apiParam = new BApi\EkiParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
		//         $apiParam->setKenCd($ken_cd);
	
		$ensenCtList = $params->getEnsenCt();

		if ($ensenCtList != null) {
			// 沿線ローマ字より沿線コードを取得する
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		else
		{
			// 駅複数の場合がある。
			$eki_ct = $params->getEkiCt();
			// 駅ローマ字より沿線コードを取得する
			$ensenCtList = array();
			foreach ((array) $eki_ct as $eki) {
				$ekiObj = EnsenEki::getObjBySingle($eki);
				array_push($ensenCtList, $ekiObj->getEnsenCt());
			}
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		$apiParam->setEnsenCd($ensen_cd_api);
		// 沿線コードをキーに、DB駅設定を取得
		//         $ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ken_cd, $ensen_cd_api);
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd = $settings->search->getEkiByEnsen($type_id, $ensen_cd_api);
		$apiParam->setEnsenEkiCd($ensen_eki_cd);

        // こだわり条件
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter);
        }

        // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\Eki();
		return $apiObj->getEkiWithEnsen($apiParam, 'MODAL_EKI_SELECT');
	}
	
	public function getModalEkiListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames,
			$searchFilter)
	{
		// 特集取得
		$specialRow = $settings->special->getCurrentPagesSpecialRow();
		$specialSetting = $specialRow->toSettingObject();
        $methodSetting = Estate\SpecialMethodSetting::getInstance();
        $areaSearchFilter = $specialSetting->area_search_filter;
		// 種目情報の取得
		$type_ct = $params->getTypeCt();
		$shumoku    = $pNames->getShumokuCd();
		// 都道府県の取得
		$ken_cd  = $pNames->getKenCd();
	
		$comId = $params->getComId();
		$kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
		$ensen_cd_api = array();
		$ensen_eki_cd = null;
	
		// BApi用パラメータ作成
		$apiParam = new BApi\EkiParams();
		$apiParam->setGroupId($comId);
		$apiParam->setKaiinLinkNo($kaiinLinkNo);
		$apiParam->setCsiteBukkenShumoku($shumoku);
// 		$apiParam->setKenCd($ken_cd);
	
		$ensenCtList = $params->getEnsenCt();
		if ($ensenCtList != null) {
			// 沿線ローマ字より沿線コードを取得する
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		else
		{
			// 駅複数の場合がある。
			$eki_ct = $params->getEkiCt();
			// 駅ローマ字より沿線コードを取得する
			$ensenCtList = array();
			foreach ((array) $eki_ct as $eki) {
				$ekiObj = EnsenEki::getObjBySingle($eki);
				array_push($ensenCtList, $ekiObj->getEnsenCt());
			}
			$ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
			foreach ($ensenObjList as $ensenObj) {
				array_push($ensen_cd_api, $ensenObj['code']);
			}
		}
		// 沿線コードをキーに、DB駅設定を取得
		$ensen_eki_cd_list = $this->getSplEkiSettings($areaSearchFilter->area_4, $ensen_cd_api);
		$apiParam->setEnsenCd($ensen_cd_api);
		// 		foreach ($specialSetting->area_search_filter->area_4[$ken_cd] as $ensen_eki)
			// 		{
			// 			$ensen = substr($ensen_eki, 0, 3);
			// 			if (in_array($ensen, $ensen_cd_api)) {
			// 				array_push($ensen_eki_cd_list, $ensen_eki);
			// 			}
			// 		}
		$apiParam->setEnsenEkiCd($ensen_eki_cd_list);
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

        // こだわり条件
        if($searchFilter){
            $apiParam->setSearchFilter($searchFilter, null, true);
        }

        // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
		// 結果JSONを元に要素を作成。
		$apiObj = new BApi\Eki();
		return $apiObj->getEkiWithEnsen($apiParam, 'MODAL_EKI_SELECT');			
	}
	
	private function getSplEkiSettings($area_4, $targetEnsenList)
	{
		$ensen_eki_cd_list = array();
		foreach ($area_4 as $ken => $ensen_ekiList) {
			foreach ($ensen_ekiList as $ensen_eki)
			{
				$ensen = substr($ensen_eki, 0, 4);
				if (in_array($ensen, $targetEnsenList)) {
					array_push($ensen_eki_cd_list, $ensen_eki);
				}
			}
		}
		return $ensen_eki_cd_list;
	}
	
	/**
	 * モーダル沿線選択画面用のデータリストを返す。
	 * @param Params $params
	 * @param Settings $settings
	 * @param ParamNames $pNames
	 */
	public function getModalLineList(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		// 通常の沿線選択画面用のデータリストと同じ。
		return $this->getLineList($params, $settings, $pNames);
	}
	
	public function getModalLineListSpl(
			Params $params,
			Settings $settings,
			ParamNames $pNames)
	{
		// 通常の沿線選択画面用のデータリストと同じ。
		return $this->getLineListSpl($params, $settings, $pNames);
	}

    public function getPrefRecommendByLine($params, $settings, $pNames, $searchFilter, $specialRow, $searchSetting) {
        
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();

        // 特集検索設定取得
        $specialSetting = $specialRow->toSettingObject();
        $areaSearchFilter = $searchSetting->area_search_filter;
        /**
         * 駅選択
         */
        $comId = $params->getComId();
        $kaiinLinkNo = $settings->page->getAllRelativeKaiinLinkNo();
        // 検索設定の駅コードを取得
        $ensen_cd_api = $areaSearchFilter->area_3->getAll();
        $ensen_eki_cd = $areaSearchFilter->area_4->getAll();

        $apiParam = new BApi\EkiParams();
        $apiParam->setGroupId($comId);
        $apiParam->setKaiinLinkNo($kaiinLinkNo);
        $apiParam->setCsiteBukkenShumoku($shumoku);
        // 		$apiParam->setKenCd($ken_cd);
        $apiParam->setEnsenCd($ensen_cd_api);
        $apiParam->setEnsenEkiCd($ensen_eki_cd);
        // 「オーナーチェンジ」
        $apiParam->setOwnerChangeFl($specialSetting->owner_change);
        // 「自社物件」「2次広告物件」「2次広告自動公開物件」
        $apiParam->setKokaiType($specialSetting->jisha_bukken, $specialSetting->niji_kokoku, $specialSetting->niji_kokoku_jido_kokai);
        // 検索エンジンレンタルのみ公開の物件だけを表示する
        $apiParam->setOnlyEREnabled($specialSetting->only_er_enabled);
        $apiParam->setOsusumeKokaiFl('true');

        // 絞り込み検索設定
        $apiParam->setSearchFilter($specialSetting->search_filter, null, true);

        // こだわり条件
        if($searchFilter) {
            $apiParam->setSearchFilter($searchFilter, null, true);
        }

        // 全会員リンク番号をキーに物件API：駅一覧にアクセスし情報を取得
        // 結果JSONを元に要素を作成。
        $apiObj = new BApi\Eki();
        $ensenList = $apiObj->getEkiWithEnsen($apiParam, 'SPL_EKI_SELECT');
        $prefs = []; 
        foreach ($ensenList['ensens'] as $ensens) {
            foreach ($ensens['ekis'] as $ekis) {
                if ($ekis['count'] <= 0 || in_array($ekis['ken_cd'], $prefs)) continue;
                $prefs[] = $ekis['ken_cd'];
            }
        }
        return $prefs;
    }

	/**
	 * 沿線駅のリストリストを返す。
	 * @param Settings $settings
	 * @param integer $type
	 * @param integer $ken_code
	 */
	public function getKenEnsenEkiList(Settings $settings, $type, $ken_code=null)
	{
		$searchSettings = $settings->company->getSearchSettingRowset()->getRowByTypeId($type);
		$areaSearchFilter = json_decode($searchSettings->area_search_filter);

		// 沿線コード一覧
		$settingEnsens  = [];
		foreach($areaSearchFilter->area_3 as $ken_cd => $area_3) {
			$settingEnsens = array_merge($settingEnsens, $area_3);
		}
		$settingEnsens = array_merge(array_unique($settingEnsens));	// 重複沿線除外
		if(empty($settingEnsens)) {
			return $kenEnsenEkiCds;
		}

		$apiParam = new BApi\EkiListParams();
		if(!is_null($ken_code)) {
			$apiParam->setKenCd($ken_code);
		}
		$apiParam->setEnsenCd($settingEnsens);

		$apiEkiList = new BApi\EkiList();
		$ekiList = $apiEkiList->getEki($apiParam, 'EKILIST');

		return $ekiList;
	}
}
	

