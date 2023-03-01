<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Library\Custom\Model\Lists;
use Library\Custom\Hp\Map;
use Modules\V1api\Services\Sp\Element;

class Detail extends Services\AbstractElementService
{
	public $head;
	public $header;
    public $content;
    public $contentFDP;
    public $isFDP;
    public $breadCrumb;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // パノラマ
        if ($params->getPanorama()) {
            $this->head    = $this->head($params, $settings, $datas);
            $this->content = $this->panorama($params, $settings, $datas);
        }
        // 物件情報
	    else if ($params->getTab() == 1) {

        	$this->head = $this->head($params, $settings, $datas);
        	$this->header = $this->header($params, $settings, $datas);
            $this->content = $this->content($params, $settings, $datas);
            $this->isFDP = $this->isFDP($params, $settings);
        // 周辺環境（地図）
        } else {

// 			$bukken = $datas->getBukken();
// 			$dispModel = (object) $bukken['display_model'];
// 			$bukken_name = Services\ServiceUtils::getVal('csite_bukken_title', $dispModel);
// 			$this->head = $bukken_name;
        	$this->head = $this->head($params, $settings, $datas);
            $this->content = $this->map($params, $settings, $datas);
            $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
            $fdp = json_decode($settingRow->display_fdp);
            // #4692
            // if (Services\ServiceUtils::isFDP($settings->page) && 
            //     Services\ServiceUtils::canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp))
            if (Services\ServiceUtils::isFDP($settings->page) && Services\ServiceUtils::canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp)) {
                $this->contentFDP = $this->contentFDP($params, $settings, $datas);
            }
            // END #4692
        }
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            if (is_null($datas->getBukken())) {
                throw new \Exception('', 404);
            }
        }
	}
	
	private function head(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pageInitialSettings = $settings->page;
		$siteName = $pageInitialSettings->getSiteName();
		$keyword = $pageInitialSettings->getKeyword();
		$comName = $pageInitialSettings->getCompanyName();
		$description = $pageInitialSettings->getDescription();
	
		$pNames = $datas->getParamNames();
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            // 物件情報の取得
            $bukken = $datas->getBukken();
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
        
            // 種目情報の取得
            $shumoku    = $dispModel->csite_bukken_shumoku_cd[0];
            // $shumoku_nm = Services\ServiceUtils::getShumokuNameByCd($shumoku);
            $shumoku_nm = $pNames->getShumokuName();
        
            $bukken_id = $dispModel->id;
            $bukken_name = Services\ServiceUtils::getVal('csite_bukken_title', $dispModel);
            $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);
            $bukken_info = $bukken_name . ' ' . $dispModel->shikugun_nm . 'の' . $dispModel->shumoku_nm;
        
            // {物件検索の情報と同じ}の物件情報｜{$物件種目}｜{$CMS初期設定サイト名}：{$物件ID}
            $title_txt   = "${bukken_info}の物件情報｜${shumoku_nm}｜${siteName}：${bukken_id}";
            $keyword_txt = "${bukken_name},${shumoku_nm},${keyword}";
            // 【{$会社名}】{物件検索の情報と同じ}の（{$物件種目}）物件詳細。{$CMS初期設定サイトの説明}
            $desc_txt   = "【${comName}】${bukken_info}の(${shumoku_nm})物件詳細。${description}";
        } else {
            $shumoku_nm = $pNames->getShumokuName();
            $title_txt   = "物件情報｜${shumoku_nm}｜${siteName}";
            $keyword_txt = "${shumoku_nm},${keyword}";
            $desc_txt   = "【${comName}】(${shumoku_nm})物件詳細。${description}";
        }
        
        if ($params->getTab() == 2)
        {
            $title_txt = '周辺環境｜'.$title_txt;
            $keyword_txt = '周辺環境,'.$keyword_txt;
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $desc_txt   = "【${comName}】${bukken_info}の(${shumoku_nm})の周辺環境。${description}";
            } else {
                $desc_txt = "【${comName}】(${shumoku_nm})の周辺環境。${description}";
            }
        }
	
		$head = new Services\Head();
		$head->title = $title_txt;
		$head->keywords = $keyword_txt;
		$head->description = $desc_txt;
	
		return $head->html();
	}
	

	private function header(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pNames = $datas->getParamNames();
        // 4697 Check Kaiin Stop
        $shumoku_nm = $pNames->getShumokuName();
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            // 物件情報の取得
            $bukken = $datas->getBukken();
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            // 種目情報の取得
            // $shumoku    = $dispModel->csite_bukken_shumoku_cd[0];
            // $shumoku_nm = Services\ServiceUtils::getShumokuNameByCd($shumoku);
            
            $bukken_id = $dispModel->id;
            $bukken_name = Services\ServiceUtils::getVal('csite_bukken_title', $dispModel);
            $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);
        }
//         $bukken_info = $bukken_name . ' ' . $dispModel->shikugun_nm . 'の' . $dispModel->shumoku_nm;

        // ({$物件種目}){物件検索の情報と同じ}の物件情報
        // ({$物件種目}){物件検索の情報と同じ}の周辺環境		
        $suffix = $params->getTab() == 2 ? '周辺環境' : '物件情報';
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            return "<h1 class='tx-explain'>(${shumoku_nm})${suffix}</h1>";
        }
        return "<h1 class='tx-explain'>(${shumoku_nm})${bukken_name}の${suffix}</h1>";
	}

	private function map(
			Params $params,
			Settings $settings,
			Datas $datas)
	{

	    //地図ページを表示できなければ404
        if(!$this->canDisplayMap($settings, $datas)){
            throw new \Exception('地図ページはありません。', 404);
        }

        $bukken = $datas->getBukken();
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        $mapAnnotationText = Services\ServiceUtils::getShuhenMapAnnotation($this->getVal('matching_level_cd', $dispModel));
        $ido = $dataModel->ido;
            $keido = $dataModel->keido;
			return 	[
				"lat" => $ido,
				"lng" => $keido,
                "apiKey" => Map::getGooleMapKeyForUserSite(),
                "apiChannel" => Map::getGoogleMapChannel($settings->company->getRow()),
                "mapAnnotationText" => $mapAnnotationText,
			];	
	}


    private function panorama(
        Params $params,
        Settings $settings,
        Datas $datas)
    {
        $bukken = $datas->getBukken();
        $dispModel = (object) $bukken['display_model'];
        $dataModel = (object) $bukken['data_model'];

        /*
        $dispModel->niji_kokoku_jido_kokai_fl = true;
        $dispModel->panorama_contents_cd = 1;
        $dispModel->panorama_webvr_fl = true;
        $dispModel->csite_panorama_kokai_fl = true;
        $dataModel->panorama = [
            'url' => "https://rent.nurvecloud.com/panoramas/7152/embed",
            'url_for_niji_kokoku' => "https://vrpanoramad.athome.jp/panoramas/_NRVzZg32F/embed?user_id=00212975&from=at",
            'qr_code_url_for_webvr' => "https://vrpanoramad.athome.jp/panoramas/_NRVzZg32F/embed?user_id=00212975&from=at&view_mode=vr",
            'accessible' => true
        ];
        */

		//VRパノラマ物件かどうか（新パノラマ）
        $panoramaType = Services\ServiceUtils::getPanoramaType($dispModel);
        if($panoramaType == Services\ServiceUtils::PANORAMA_TYPE_NONE) {
            throw new \Exception('パノラマページはありません。', 404);
        }

        //$panoramaUrl = "https://rent.nurvecloud.com/panoramas/7152/embed";
        $panoramaUrl = $dataModel->panorama['url'];

        // NHP-4930 niji_kokoku_jido_kokai_fl参照しURL書き換え
        if(isset($dispModel->niji_kokoku_jido_kokai_fl)) {
            if($dispModel->niji_kokoku_jido_kokai_fl) {
                $panoramaUrl = $dataModel->panorama['url_for_niji_kokoku'];
            }
        }

        // 該当コンテンツがVR対応かつ、リクエストがVRなら qr_code_url_for_webvrを表示
        if($panoramaType == Services\ServiceUtils::PANORAMA_TYPE_VR && $params->getParam('view_mode') == 'vr') {
            $panoramaUrl = $panoramaUrl . "&view_mode=vr";
        }

        return 	[
            "panoramaUrl" => $panoramaUrl,
        ];

    }

    private function canDisplayMap($settings, $datas){
        $pageInitialSettings = $settings->page;
        $bukken  = $datas->getBukken();
        $shumoku = $datas->getParamNames()->getShumokuCd();

        return Services\ServiceUtils::canDisplayMap($pageInitialSettings, $bukken, $shumoku);
    }

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".sp.tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/detail/content.sp.tpl");
	
		// 変数
        // 会員番号
        $kaiinNo = $settings->page->getMemberNo();
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
	
		$pNames = $datas->getParamNames();

        $bukken = $datas->getBukken();
		
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 市区郡の取得
        $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
        // 都道府県の取得
        $ken_cd  = substr($shikugun_cd, 0, 2);
        $ken_ct = Services\ServiceUtils::getKenCtByCd($ken_cd);
        $ken_nm  = $dispModel->ken_nm;
        // 物件APIから取得
        $shikugun_ct = Services\ServiceUtils::getShikugunObjByCd($ken_cd, $shikugun_cd)->shikugun_roman;
        $shikugun_nm = $this->getVal('shikugun_nm', $dispModel);
        
        // 物件名
        $bukken_name = $this->getVal('csite_bukken_title', $dispModel);
        $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);
        /*
         * パンくず作成
         * 市区町村選択検索パターンで作成する。
         */
        $top = $this->getSearchTopFileName($searchCond);        
        $levels = [
            // URL = NAME
            "/${top}"      => $this::BREAD_CRUMB_1ST_SHUMOKU
        ];
        if (count($datas->getPrefSetting()) !== 1) {
            $levels += [
                "/${type_ct}/" => $this::BREAD_CRUMB_2ND_PREF
            ];
        }
        $levels += [
            "/${type_ct}/${ken_ct}/" => "${ken_nm}",
            "/${type_ct}/${ken_ct}/result/${shikugun_ct}-city.html" => "${shikugun_nm}の物件一覧",
            '' => "${bukken_name}"
        ];
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        $detailMaker = new Element\Detail();
        $detailElem = $detailMaker->createElement($shumoku, $kaiinNo, $bukken, $params, $datas->getCodeList(), $settings->page, $searchCond);
        
        $doc->append($detailElem);
        
        return $doc->html();
    }

    private function contentFDP(
            Params $params,
            Settings $settings,
            Datas $datas) 
    {
        $doc = $this->getTemplateDoc("/detail/fdp-map.sp.tpl");

        $bukken = $datas->getBukken();

        $display_model = (object) $bukken[ 'display_model' ] ;
        
        // 環境によるイメージサーバの切り替え
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);

        // コンフィグ取得
        $config = getConfigs('v1api.api');
        /*
         * メイン画像
         */
        $thumbnail = Services\ServiceUtils::getMainImageForSP( $display_model, $params ) ;
        if (! is_null($thumbnail) && ( isset( $thumbnail->url ) )) {
            $url = $config->img_server . $thumbnail->url . "?width=160&height=160";
        } else {
            $url = $config->img_server . "/image_files/path/no_image";
        }

        $doc['.fdp-map']->attr('data-src', $url);

        $facilityMenu = $doc['.fdp-map .facility-map ul']->empty();
        $facility = '';
        foreach (Lists\FdpFacility::getInstance()->listFacilityName() as $key=>$name) {
            $class = in_array($key, Lists\FdpFacility::getInstance()->listFacilityDisplayBegin()) ? 'show' : 'hidden';
            $facility .= '<li id="'.$key.'" class="'.$class.'"><img src="/sp/imgs/fdp/'.$key.'.svg">'.$name.'</li>';
        }
        $facilityMenu->append($facility);

        return $doc->html();

    }

    private function isFDP(
        Params $params,
        Settings $settings) 
    {
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if (Services\ServiceUtils::isFDP($settings->page) && count($fdp->fdp_type)) {
            return true;
        }
        return false;
    }

}