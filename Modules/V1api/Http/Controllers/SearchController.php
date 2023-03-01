<?php
namespace Modules\V1api\Http\Controllers;

use Modules\V1api\Models;
use Modules\V1api\Services;
use Modules\V1api\Models\ParamNames;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use App\Traits\JsonResponse;

class SearchController extends ApiAbstractController
{
    use JsonResponse;
    protected $params;
    protected $settings;

	public function preDispatch()
	{
		// パラメータ取得
		$params = (object) $this->_request->all();

		// ATHOME_HP_DEV-5001
		if(getActionName() == 'detail') {
			if(preg_match("/^(\S{1,})\:cms$/", $params->bukken_id, $m)) {
				$params->bukken_id = $m[1];
				$params->fromcms = true;
			}
		}

		$this->params = new Models\Params($params);
		$this->settings = new Models\Settings($this->params);
	}

    /**
     * 物件検索TOP画面API
     */
	public function shumoku()
	{
    	$logic = new Models\Logic();
    	$datas = $logic->shumoku($this->params, $this->settings);

		if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\Shumoku();
        }
        else
        {
            $maker = new Services\Sp\Shumoku();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
	}

    /**
     * 賃貸物件検索TOP画面API
     */
    public function rent()
    {
    	
    	$logic = new Models\Logic();
    	$datas = $logic->rent($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\Rent();
        }
        else
        {
            $maker = new Services\Sp\Rent();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 売買物件検索TOP画面API
     */
    public function purchase()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->purchase($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\Purchase();
    	}
        else
        {
            $maker = new Services\Sp\Purchase();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 都道府県選択画面API
     */
    public function pref() {
        $logic = new Models\Logic();
    	$datas = $logic->pref($this->params, $this->settings);

        $prefs = $datas->getPrefSetting();
    	// 都道府県がひとつの場合にリダイレクト
    	if ($prefs && count($prefs) === 1 && $this->params->getAllowRedirect() === 'true') {

    		$type_ct = $this->params->getTypeCt();
    		$ken_ct = Estate\PrefCodeList::getInstance()->getUrl($prefs[0]);

    		// 市区郡から探す
    		$url = "/{$type_ct}/$ken_ct/";

    		// 沿線から探すのみ場合
    		if (!$this->settings->search->canAreaSearch($type_ct) && $this->settings->search->canLineSearch($type_ct)) {
    			$url .= 'line.html';
    		}
            // 地域から探すのみ
    		if (!$this->settings->search->canAreaSearch($type_ct)
            && !$this->settings->search->canLineSearch($type_ct)
            && $this->settings->search->canSpatialSearch($type_ct)) {
                $url .= 'map.html';
            }
    		return $this->successV1api(['redirect_to' => $url]);
    	}

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\Pref();
    	}
        else
        {
    		$maker = new Services\Sp\Pref();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 地域から探す画面API
     */
    public function city()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->city($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\City();
    	}
        else
        {
    		$maker = new Services\Sp\City();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	//$this->view->assign($elements);

        return $this->successV1api($elements);
    }

    /**
     * 町名検索
     */
    public function choson() {
        $logic = new Models\Logic();
        $datas = $logic->choson($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\Choson();
        }
        else
        {
            $maker = new Services\Sp\Choson();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 沿線から探す画面API
     */
    public function line()
    {
        
        $logic = new Models\Logic();
    	$datas = $logic->line($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\Line();
    	}
        else
        {
    		$maker = new Services\Sp\Line();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	// $this->view->assign($elements);
        return $this->successV1api($elements);
    }

    /**
     * 駅から探す画面API
     */
    public function eki()
    {
        $logic = new Models\Logic();
    	$datas = $logic->eki($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\Eki();
    	}
        else
        {
    		$maker = new Services\Sp\Eki();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }

    /**
     * SP用こだわり条件から探す画面API
     */
    public function condition()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->condition($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
            throw new \Exception('Illegal Access.');
    	}
    	else
    	{
    		$maker = new Services\Sp\Condition();
    	}
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }

    /**
     * 物件一覧画面API
     */
    public function result()
    {
        $logic = new Models\Logic();
        if ($this->params->isFreeword()) {
            $datas = $logic->resultFreeword($this->params, $this->settings);
            if ($this->params->isPcMedia())
            {
                $maker = new Services\Pc\ResultFreeword();
                $elements = $maker->execute($this->params, $this->settings, $datas);

                $hiddenMaker = new Services\Pc\ResultHidden();
                $hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);
                $elem=array_merge($elements, $hiddenElements);
            }
            else
            {
                $maker = new Services\Sp\ResultFreeword();
                $elements = $maker->execute($this->params, $this->settings, $datas);
                $elem =$elements;
            }
        } else {
            $datas = $logic->result($this->params, $this->settings);
        	if ($this->params->isPcMedia())
	    	{
	    		$maker = new Services\Pc\Result();
	    		$elements = $maker->execute($this->params, $this->settings, $datas);

	    		$hiddenMaker = new Services\Pc\ResultHidden();
	    		$hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);
                $elem=array_merge($elements, $hiddenElements);   
	    	}
	        else
	        {
	    		$maker = new Services\Sp\Result();
	    		$elements = $maker->execute($this->params, $this->settings, $datas);
                $elem=$elements;        
	        }
        }

        /*
         *　評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        if ($this->params->isProdPublish() == true){
        // 検索結果０件はログ出力しない
            $bukkenList = $datas->getBukkenList();
            if ($bukkenList['total_count'] != 0) 
            {
                $type_ct = (array) $this->params->getTypeCt();
    	        $class = Estate\TypeList::getInstance()->getClassByUrl($type_ct[0]);
        	    $special_id = null;
            	Models\Logger\CLogger::logResult(
                		$this->params, $class, $special_id);
            }
        }
        return $this->successV1api($elem);
    }

    /**
     * 物件詳細画面API
     */
    public function detail()
    {
        $logic = new Models\Logic();
        $datas = $logic->detail($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
        	$maker = new Services\Pc\Detail();
        }
        else
        {
        	$maker = new Services\Sp\Detail();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        /*
         *　評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        $bukken = $datas->getBukken();
        if($this->params->isProdPublish() == true && $this->params->isFromCms() ==false && !is_null($bukken)){
            // Controllerで物件情報をAPIから取得
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            // ATHOME_HP_DEV-5001 
            $typeList = Estate\TypeList::getInstance();

            $display_flg = false;   // 詳細表示フラグ(default:false);
            $classes = [];          // 物件詳細APIの種別 - 種目データ
            foreach($dispModel->csite_bukken_shumoku_cd as $scode) {
                $type  = $typeList->getByShumokuCode($scode);
                $class = $typeList->getClassByType($type);
                if(!isset($classes[ $class ])) {
                    $classes[ $class ] = [];
                }
                $classes[ $class ][] = $type;
            }

            // 表示対象物件の種別を順次参照し、対応する種別の物件検索設定を取り出し比較
            foreach ($classes as $class => $types) {
                $searchSettings = [];
                foreach($types as $type) {
                    if(!empty($searchSettings)) {
                        continue;
                    }
                    // 種目が属する検索種別の設定を取得
                    $searchSettings = $this->settings->company->getSearchSettingRowset()->getRowByTypeId( $type );

                    if(empty($searchSettings)) {
                        // 検索設定で対象種別を利用していないなら次の種別をチェックする
                        continue;
                    }
                    if(count(array_intersect($searchSettings->getEnabledEstateTypeArray(), $types)) == 0) {
                        // 検索設定の種目(複数)と詳細APIの種目(複数)で重複がなければ次の種別をチェックする
                        continue;
                    }

                    $areaSearchFilter = json_decode($searchSettings->area_search_filter);
                    if($searchSettings->hasAreaSearchType() || $searchSettings->hasSpatialSearchType()) {
                        // 地域から探す(1) or 地図から探す(3)
                        // -> 1.都道府県コード(ken_cd)を比較
                        $ken_cd = $dispModel->ken_cd;
                        if(in_array($ken_cd, $areaSearchFilter->area_1, true)) {
                            // -> 2.市区郡(shozaichi_cd1)を比較
                            $shozaichi_cd1 = $dataModel->shozaichi_cd1;
                            if(in_array($shozaichi_cd1, $areaSearchFilter->area_2->{ $ken_cd }, true)) {
                                // -> 3.町村字(shozaichi_cd2)を比較
                                if(isset($areaSearchFilter->area_5->{ $ken_cd }->{ $shozaichi_cd1 })) {
                                    // 町村字コードは先頭3Byte
                                    $shozaichi_cd2 = substr($dataModel->shozaichi_cd2, 0, 3);
                                    if(in_array($shozaichi_cd2, $areaSearchFilter->area_5->{ $ken_cd }->{ $shozaichi_cd1 }, true)) {
                                        // -> 4.町村字詳細(shozaichi_cd2)を比較
                                        if(isset($areaSearchFilter->area_6->{ $ken_cd }->{ $shozaichi_cd1 }->{ $shozaichi_cd2 })) {
                                            if(in_array($dataModel->shozaichi_cd2, $areaSearchFilter->area_6->{ $ken_cd }->{ $shozaichi_cd1 }->{ $shozaichi_cd2 }, true)) {
                                                $display_flg = true;
                                                break;
                                            }
                                        } else {
                                            // 個別町村字詳細なしは全町村字詳細のため合致
                                            $display_flg = true;
                                            break;
                                        }
                                    }
                                } else {
                                    // 個別町村字なしは全町村字のため合致
                                    $display_flg = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($searchSettings->hasLineSearchType()) {
                        // 沿線・駅から探す(2)がある

                        // 対象種目の沿線より、ken_ensen_eki_cdを取得
                        $kenEnsenEkiCds = $logic->getKenEnsenEkiCds($this->settings, $type);

                        $settingEnsenEkis = [];    // CMSの検索駅一覧を初期化
                        foreach($areaSearchFilter->area_4 as $ken_cd => $area_4) {
                            foreach($areaSearchFilter->area_4->{ $ken_cd } as $ensenEki) {
                                if(isset($kenEnsenEkiCds[ $ensenEki ])) {
                                    $settingEnsenEkis[] = $kenEnsenEkiCds[ $ensenEki ];
                                }
                            }
                        }

                        // 最寄駅比較を display_model.kotsu[x].ken_ensen_eki_cdを利用
                        foreach($dispModel->kotsus as $kotsu) {
                            if(!isset($kotsu[ 'ken_ensen_eki_cd' ])) {
                                continue;
                            }
                            if(in_array($kotsu[ 'ken_ensen_eki_cd' ], $settingEnsenEkis, true)) {
                                $display_flg = true;
                                break;
                            }
                        }
                    }
                }
                if($display_flg) {
                    break;
                }
            }

            if(!$display_flg) {
                // 表示しない場合は404(Not Found)を返す
                throw new \Exception('ページが見つかりません', 404);
                exit;
            }

            // SPパノラマログ
            if ($this->params->isSpMedia() && $this->params->getPanorama()) {
                // 会員番号
                $member_no = $this->settings->company->getRow()->member_no;
                // パノラマコンテンツID
                $panorama_contents_id = $this->getVal('panorama_contents_id', $dataModel, true);
                // 物件番号
                $bukken_no = $this->getVal('bukken_no', $dispModel, true);
                // 物件ID
    			$bukken_id = $this->getVal('id', $dispModel, true);
                // 物件バージョン番号
                $version_no    = $this->getVal('version_no', $dataModel, true);

                Models\Logger\CLogger::logPanorama(
                    $this->params, $bukken_no, $member_no, $panorama_contents_id, '02', $bukken_id, $version_no);

                return $this->successV1api($elements);
            }

            // ２次広告自動公開
            $isNijiKokokuJidou = $dispModel->niji_kokoku_jido_kokai_fl;
            $class = Estate\TypeList::getInstance()->getClassByUrl($this->params->getTypeCt());
            $bukken_no = $this->getVal('bukken_no', $dispModel, true);
            $special_path = $this->params->getSpecialPath();
            if (! is_null($special_path)) {
                $currentPagesSpecialRow = $this->settings->special->findByUrl($this->params->getSpecialPath());
                $special_id = $currentPagesSpecialRow->id;
            } else {
                $special_id = null;
            }

            $bukken_id  = $this->getVal('id', $dispModel, true);
    		$version_no = $this->getVal('version_no', $dataModel, true); 

            // パラメータより取得
            $isOsusume = is_null($this->params->getFromRecommend()) ? false : $this->params->getFromRecommend();
            Models\Logger\CLogger::logDetail(
                $this->params, $class, $bukken_no, $isNijiKokokuJidou, $special_id, $isOsusume, $bukken_id, $version_no);
            // 4293: Add log detail FDP
            $isFDP = Services\ServiceUtils::isFDP($this->settings->page);
            if ($isFDP) {
                Models\Logger\CLogger::logDetailFDP(
                $this->params, $class, $bukken_no, $isNijiKokokuJidou, $special_id, $isOsusume, $this->params->getTab());
            }
        }
        return $this->successV1api($elements);
    }

    /**
     * お気に入り　物件一覧画面API
     */
    public function favorite()
    {
        $logic = new Models\Logic();

        $personal_sort = $this->params->getParam('sort');
        if($personal_sort != 'asc') {
            $this->params->setParam('personal_sort', $personal_sort);
        }
    	$datas = $logic->favorite($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\Favorite();
    	}
    	else
        {
    		$maker = new Services\Sp\Favorite();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }

    /**
     * 最近見た物件　物件一覧画面API
     */
    public function history()
    {
        $logic = new Models\Logic();

        $personal_sort = $this->params->getParam('sort');
        if($personal_sort != 'asc') {
            $this->params->setParam('personal_sort', $personal_sort);
        }
    	$datas = $logic->history($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\History();
    	}
    	else
        {
    		$maker = new Services\Sp\History();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }

    /**
     * 情報の見方API
     */
    public function howtoinfo()
    {
        $logic = new Models\Logic();
    	$datas = $logic->howtoinfo($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\Howtoinfo();
    	}
    	else
        {
    		$maker = new Services\Sp\Howtoinfo();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }

    /**
     * 地図から探す画面API
     */
    public function spatialCity()
    {
        $logic = new Models\Logic();
        $datas = $logic->city($this->params, $this->settings, true);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\SpatialCity();
        }
        else
        {
            $maker = new Services\Sp\SpatialCity();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    /**
     * 地図検索画面API
     */
    public function spatialMap()
    {
        $datas = new Datas();
        $datas->setParamNames(new ParamNames($this->params));
        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\SpatialMap();
        }
        else
        {
            $maker = new Services\Sp\SpatialMap();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    /**
     * 地図検索中心点API
     */
    public function spatialMapcenter()
    {
        $logic = new Models\Logic();
        $coordinate = $logic->mapcenter($this->params, $this->settings);
        return $this->successV1api(['coordinate' => $coordinate]);
    }
    /**
     * 地図検索アサイドと物件情報API
     */
    public function spatialEstate()
    {
        $logic = new Models\Logic();
        $datas = $logic->spatialEstate($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\SpatialEstate();
    		$elements = $maker->execute($this->params, $this->settings, $datas);

    		$hiddenMaker = new Services\Pc\ResultHidden();
    		$hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);

            $elem=array_merge($elements,$hiddenElements);
    	}
        else
        {
            $maker = new Services\Sp\SpatialEstate();
            $elements = $maker->execute($this->params, $this->settings, $datas);
            $elem=$elements;
        }

        /*
         * 評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        if ($this->params->isProdPublish() == true){
            $spatialEstate = $datas->getSpatialEstate();
            if ($spatialEstate['total_count'] != 0) {
                $class = Estate\TypeList::getInstance()->getClassByUrl($this->params->getTypeCt());
                $special_id = null;
                Models\Logger\CLogger::logMap($this->params, $class, $special_id);
            }
        }
        return $this->successV1api($elem);
    }
    /**
     * 地図検索物件一覧（右カラム）
     */
    public function spatialEstatelist()
    {
        $logic = new Models\Logic();
        $datas = $logic->spatialMapEstatelist($this->params, $this->settings);

        if ($this->params->isPcMedia())
        {
            $maker = new Services\Pc\SpatialEstatelist();
        }
        else
        {
            $maker = new Services\Sp\SpatialEstatelist();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

}
