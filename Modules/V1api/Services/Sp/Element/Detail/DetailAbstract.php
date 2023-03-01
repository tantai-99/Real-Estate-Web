<?php
namespace Modules\V1api\Services\Sp\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\KApi;
use Library\Custom\Model\Estate;
use Library\Custom\Hp\Map;
abstract class DetailAbstract
{

    protected $logger;
    protected $_config;

    protected $canDisplayMap = null;

    public function __construct()
    {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);

        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    /**
     * 用途地域 yoto_chiiki_nm
     * @param $dataModel
     */
    protected function getYotoChiiki($dataModel)
    {
    	$result = null;
    	$yoto = $this->getVal('yoto_chiiki_nm', $dataModel);
    	if (is_array($yoto)) {
    		foreach ($yoto as $entry) {
    			if (empty($entry)) continue;
    			$result .= is_null($result) ? $entry : "　" . $entry;
    		}
    		$result = is_null($result) ? '-' : $result;
    	} else {
    		$result = $yoto;
    	}
    	return $result;
    }

    protected function getMadoriWithUchiwake($dispModel) {
    	$result = $this->getVal('madori', $dispModel);
    	$uchiwake = $this->getVal('madori_uchiwake', $dispModel, true);
    	if ($uchiwake) {
    		$result .= "（{$uchiwake}）";
    	}
    	return $result;
    }

    protected function setOpenHouse($mainInfoElem, $dispModel) {
    	// オープンハウス
    	$opneHouseElem = $mainInfoElem['div.event-schedule'];
    	$openHouse = $this->getVal('open_house', $dispModel, true);
    	if (empty($openHouse)) {
    		$opneHouseElem->remove();
    	} else {
    		$opneHouseElem['td']->text($openHouse);
    	}
    }

    protected function getTatemonoName($dataModel, $dispModel) {
        return $this->getVal('csite_tatemono_nm', $dispModel);
    }

    protected function getReformRenovation($dispModel) {
    	$reform_renovation = '';
    	if (isset($dispModel->reform)) {
    		$reform_renovation = "リフォーム：" . $this->getVal('reform', $dispModel);
    	}
    	if (isset($dispModel->renovation)) {
    		if (!empty($reform_renovation)) $reform_renovation .= '<br><br>';
    		$reform_renovation = $reform_renovation . "リノベーション：" . $this->getVal('renovation', $dispModel);
    	}
    	return $reform_renovation;
    }

    protected function getKotsus($dispModel)
    {
        $kotsus = $dispModel->csite_kotsus;
        $kotsuTxt = count($kotsus) == 1 ? '-' : '';
        for ($j = 1; $j < count($kotsus); $j++)
        {
            $kotsuTxt = $kotsuTxt . $this->getKotsusValue($kotsus[$j]) . '<br>';
        }
    	return $kotsuTxt;
    }

    /**
     * csite_kotsuの配列の中身（array or string）を引数に、交通情報を作成して返す。
     *
     * @param $kotsu　csite_kotsuの配列の中身（必須）
     */
    protected function getKotsusValue($kotsu)
    {
    	$value = '';
    	if (is_array($kotsu)) {
    		if (count($kotsu) === 1) {
    			$value = $kotsu[0];
    		} else {
    			$value = $kotsu[0] . '<br>' . $kotsu[1];
    		}
    	} else {
    		$value = $kotsu;
    	}
    	return $value;
    }

    protected function getBikos($dispModel,$isIjihito = false)
    {
        $default = '-';
        $bikoTxt = "";
        $splitCount = 70;//区切り文字数の閾値

        $bikoList = [];
        if (isset($dispModel->csite_bikos) && is_array($dispModel->csite_bikos)) {
            $bikos = $dispModel->csite_bikos;
            foreach ($bikos as $key => $val)
            {
                if($key === 'ijihito' && $isIjihito) continue;
                if (is_array($val)) {
                    foreach ($val as $v) {
                        if(!empty($v)) $bikoList[] = $v;
                    }
                } else {
                    if(!empty($val)) $bikoList[] = $val;
                }
            }
        }

        //①温泉を追加
        if ( (isset($dispModel->onsen_hikikomi_jokyo) && $dispModel->onsen_hikikomi_jokyo != "") ||
            (isset($dispModel->onsen_riyo_keitai) && $dispModel->onsen_riyo_keitai != "") ||
            (isset($dispModel->onsen_hiyo_to) && $dispModel->onsen_hiyo_to != "")) {
            $bikoList[] = "温泉あり";

            if ( (isset($dispModel->onsen_hikikomi_jokyo) && $dispModel->onsen_hikikomi_jokyo != "") ) {
                $bikoList[] = "■引込状況：". $dispModel->onsen_hikikomi_jokyo;
            }

            if ( (isset($dispModel->onsen_riyo_keitai) && $dispModel->onsen_riyo_keitai != "") ) {
                $bikoList[] = "■利用形態：" . $dispModel->onsen_riyo_keitai;
            }

            if ( (isset($dispModel->onsen_hiyo_to) && $dispModel->onsen_hiyo_to != "") ) {
                $bikoList[] = "■費用等：" . $dispModel->onsen_hiyo_to;
            }
        }

        foreach($bikoList as &$biko) {
            // 1. タグを除去する
            $biko = strip_tags($biko);
            // 2. 各種改行を『\n』に統一
            $biko = str_replace(["\r\n", "\r"], "\n", $biko);
            // 3. 設備保証情報がある場合はタイトルを削除
            $biko = str_replace("設備保証：", "", $biko);
        }

        $strStart = [];
        $strEnd = [];
        $strcnt = $splitCount;

        $nBikoList = explode("\n", implode("\n", $bikoList));

        foreach($nBikoList as $val) {
            $strcnt = $strcnt - mb_strlen($val, 'UTF-8');
            if($strcnt < 0 && empty($strEnd)) {
                $splitpoint = mb_strlen($val) + $strcnt;
                $strStart[] = mb_substr($val, 0, $splitpoint);
                $strEnd[] = mb_substr($val, $splitpoint);
            } else if(!empty($strEnd)) {
                $strEnd[] = $val;
            } else {
                $strStart[] = $val;
            }
        }

        if(empty($strStart)) {
            $bikoTxt = $default;
        } else {
            if(empty($strEnd)) {
                $bikoTxt = implode("\n", $bikoList);
                $bikoTxt = str_replace("\n", "<br>", $bikoTxt);
            } else {
                $bikoTxt = "<p>" . implode("<br>", $strStart) . '</p> <div class="readmore"> <p>' . implode("<br>", $strEnd) . '</p>';
            }
        }
        return $bikoTxt;
    }


    //  設備
    protected function getSetsubis($dispModel)
    {
        $setsubisTxt = "-";
        $setsubis = $this->getVal('csite_setsubis', $dispModel, true);
        if(!$setsubis){
            return $setsubisTxt;
        }

        $setsubisTxt = "";
        foreach ( $setsubis as $setsubi ){
            $setsubisTxt .= $setsubi;
            if ($setsubi !== end($setsubis)) {
                $setsubisTxt .= "　";
            }
        }
        return $setsubisTxt;

    }

    protected function createGallery($bukken, $itemElem, $codeList)
    {
        $dataModel = (object) $bukken['data_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        /*
         * フォトギャラリー
         */
        $galleryElem = $itemElem['div.photo-slider'];
        $imgNum = 0;
        $wrapperElem = pq('<div class="swiper-wrapper" style="" />');
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
                if ($image->status != 2) { // 登録済み
                    continue;
                }
                // サムネイル
                $url = $img_server . $image->url;
                $anchorElem = pq('<div class="inner"><a rel="photo-slider-group" href="" title="" data-caption=""></a></div>');
                $anchorElem['a']->attr('href', $url . "?width=640&amp;height=640");
                $anchorElem['a']->attr('title', $codeList['sub_category'][$this->getVal('sub_category', $image, true)]);
                $anchorElem['a']->attr('data-caption', $this->getVal('caption', $image, true));
                $imgElem = "<img src='${url}?width=640&amp;height=640' alt='". $this->getVal('caption', $image, true) . "'>";
                $anchorElem['a']->append($imgElem);
                $sliderElem = pq('<div class="swiper-slide active-nav" />');
                $sliderElem->append($anchorElem);
                $wrapperElem->append($sliderElem);

                $imgNum = $imgNum +1;
            }
        }    
        // NHP-5163 物件詳細画面に表示する画像の点数17〜40枚目はdataModelのaux_imagesを使用する
        if (isset($dataModel->aux_images) && count($dataModel->aux_images) > 0)
        {
            $aux_images = $dataModel->aux_images;
            // シリアル番号で並び替え
            usort($aux_images, function($a, $b)
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
            
            foreach ($aux_images as $elem)
            {
                $aux_image = (object) $elem;
                if ($aux_image->status != 2) { // 登録済み
                    continue;
                }
                // サムネイル
                $url = $img_server . $aux_image->url;
                $anchorElem = pq('<div class="inner"><a rel="photo-slider-group" href="" title="" data-caption=""></a></div>');
                $anchorElem['a']->attr('href', $url . "?width=640&amp;height=640");
                $anchorElem['a']->attr('title', $codeList['sub_category'][$this->getVal('sub_category', $aux_image, true)]);
                $anchorElem['a']->attr('data-caption', $this->getVal('caption', $aux_image, true));
                $imgElem = "<img src='${url}?width=640&amp;height=640' alt='". $this->getVal('caption', $aux_image, true) . "'>";
                $anchorElem['a']->append($imgElem);
                $sliderElem = pq('<div class="swiper-slide active-nav" />');
                $sliderElem->append($anchorElem);
                $wrapperElem->append($sliderElem);

                $imgNum = $imgNum +1;
            }
        }
        // NHP-5163 物件詳細画面に表示する画像の周辺情報はshuhen_kankyosを使用する
        if (isset($dataModel->shuhen_kankyos) && count($dataModel->shuhen_kankyos) > 0)
        {
            $shuhen_kankyos = $dataModel->shuhen_kankyos;
    
            foreach ($shuhen_kankyos as $elem)
            {
                if (empty($elem['nm'])) {
                    continue;
                }
                if (empty($elem['url'])) {
                    continue;
                }
                $shuhen_kankyo = (object) $elem;

                // サムネイル
                $url = $img_server . $shuhen_kankyo->url;
                $anchorElem = pq('<div class="inner"><a rel="photo-slider-group" href="" title="" data-caption=""></a></div>');
                $anchorElem['a']->attr('href', $url . "?width=640&amp;height=640");
                $anchorElem['a']->attr('title', $shuhen_kankyo->shubetsu_nm);
                $anchorElem['a']->attr('data-caption', $shuhen_kankyo->nm);
                $imgElem = "<img src='${url}?width=640&amp;height=640' alt='". $shuhen_kankyo->nm . "'>";
                $anchorElem['a']->append($imgElem);
                $sliderElem = pq('<div class="swiper-slide active-nav" />');
                $sliderElem->append($anchorElem);
                $wrapperElem->append($sliderElem);

                $imgNum = $imgNum +1;
            }
        }

        if ($imgNum == 0) {
            $itemElem['div.photo-slider']->remove();
            $itemElem['div.photo-slider-info']->remove();
            $itemElem['p.photo-slider-num']->remove();
        } else {
            $galleryElem['div.swiper-wrapper']->replaceWith($wrapperElem);
            if ($imgNum == 1) {
                $galleryElem['ul.btn-move']->remove();
            }
        }
    }


    /*
     * パノラマムービー
     */
    protected function createPanorama($bukken, $itemElem)
    {
    	$dispModel = (object) $bukken['display_model'];
    	$dataModel = (object) $bukken['data_model'];

        /*
        $dispModel->panorama_contents_cd = 1;
        $dispModel->panorama_webvr_fl = true;
        $dispModel->csite_panorama_kokai_fl = true;
        $dataModel->panorama = [
            'url' => "https://rent.nurvecloud.com/panoramas/7152/embed",
            'qr_code_url_for_webvr' => "https://vrpanoramad.athome.jp/panoramas/_NRVzZg32F/embed?user_id=00212975%26from=at%26view_mode=vr",
            'accessible' => true
        ];
        */

        $panoramaElem = $itemElem['p.btn-movie'];
        $panoramaElem->empty();

        //VRパノラマ物件かどうか（新パノラマ）
        $panoramaType = Services\ServiceUtils::getPanoramaType($dispModel);

        if($panoramaType)
        {
            switch($panoramaType) {
                case Services\ServiceUtils::PANORAMA_TYPE_MOVIE:
                    $anchor = '<a href="panorama.html">パノラマを見る</a>';
                    $panoramaElem->append($anchor);
                    break;
                case Services\ServiceUtils::PANORAMA_TYPE_PHOTO:
                    $anchor = '<a href="panorama.html">フォトムービーを見る</a>';
                    $panoramaElem->append($anchor);
                    break;
                case Services\ServiceUtils::PANORAMA_TYPE_VR:
                    $anchor = '<a href="panorama.html" style="width:49%;float:left;">パノラマを見る</a>';
                    $panoramaElem->append($anchor);
                    $anchor = '<a href="panorama.html?view_mode=vr" style="width:49%;float:right;">VRを見る</a>';
                    $panoramaElem->append($anchor);
                    $panoramaElem->append('<br style="clear:both;"/>');
                    break;
                default:
                    break;
            }

        } else {
            $panoramaElem->remove();
        }

    }

    /**
     * アピールポイントの作成
     */
    protected function createIppanMessageShosai($bukken, $contentElem){

        $dispModel = (object) $bukken['display_model'];

        $ippanMessageShosai = $this->getVal('ippan_message_shosai', $dispModel, true);
        if($ippanMessageShosai){
            $appealElem = $contentElem['.appeal-home'];
            $appealElem->append(nl2br($ippanMessageShosai));
        }else{
            $contentElem['.appeal-home']->remove();
            $contentElem['#title-appeal-home']->remove();
        }
    }

    /**
     * 地図を表示できるかどうか
     * 　下記の場合に表示できないと判定する
     * 　・物件情報に、「地図検索不可フラグ(data_model.chizu_kensaku_fuka_fl=true)」が含まれる場合
     * 　・物件情報を所有する会員の会員情報に、「地図情報利用不可(isNotUsedMapDisplay=true)」が含まれる場合
     */
    protected function canDisplayMap($pageInitialSettings, $bukken, $shumoku)
    {
        if( is_null($this->canDisplayMap)){
            $this->canDisplayMap = Services\ServiceUtils::canDisplayMap($pageInitialSettings,$bukken, $shumoku);
        }
        return $this->canDisplayMap;
    }

    /**
     * 周辺情報を表示できるかどうか
     * 　下記の場合に表示できないと判定する
     * 　・物件情報に周辺環境（data_model.shuhen_kankyos）が含まれない場合
     *
     */
    protected function canDisplayShuhenInfo( $bukken, $shumoku){
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        if(isset($dataModel->shuhen_kankyos)){
            return true;
        }
        return false;
    }

    /**
     * 周辺情報タブを表示できるかどうか
     * 　周辺情報と地図が両方共表示されない状態であれば、周辺情報タブを表示しない
     */
    protected function canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku)
    {

        //周辺情報と地図が両方共表示されない状態であれば、周辺情報タブを表示しない
        if( !$this->canDisplayShuhenInfo( $bukken, $shumoku) &&
            !$this->canDisplayMap( $pageInitialSettings, $bukken, $shumoku)){
            return false;
        }
        return true;
    }


    /**
     * 周辺情報要素の作成
     */
    protected function createShuhenTab($bukken, $itemElem, $kaiinNo, $pageInitialSettings, $shumoku, $params, $searchCond)
    {

        // 周辺情報
        $shuhenElem = $itemElem['section.info-around'];

        if(!$this->canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku)){
            $shuhenElem->remove();
        }

        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        // 地図表示不可の場合は
        if (!$this->canDisplayMap($pageInitialSettings, $bukken, $shumoku))
        {
            $shuhenElem['.heading-article-lv2.around']->html($shuhenElem['.heading-article-lv2.around a']->text());
        }

        $shuhenElem['div.table-article-info table']->empty();
        if (isset($dataModel->shuhen_kankyos)) {
            $cnt = 0;
            foreach ($dataModel->shuhen_kankyos as $shuhenData)
            {
                if (empty($shuhenData['nm'])) {
                    continue;
                }
                $trElem = pq('<tr><td/></tr>');
                $shuhen = (object) $shuhenData;
                if (isset($shuhen->url)) {
                	$anchorElem = pq('<a rel="photo-slider-group" href="" title="" data-caption=""/></a>');
                	$trElem['td']->append($anchorElem);
                	$url = $img_server . $shuhen->url . "?width=640&amp;height=640";
                    $anchorElem->attr('href', $url);
	                $text = '';
                	if (isset($shuhen->nm)) {
                    	$anchorElem->attr('title', $shuhen->nm);
	                    $text = $shuhen->nm;
    	            }
        	        if (isset($shuhen->kyori)) {
                	    $anchorElem->attr('data-caption', '距離：'.$shuhen->kyori.'ｍ');
                    	$text = $text . (isset($shuhen->kyori) ? '<br><span>距離：'.$shuhen->kyori.'ｍ</span>': '' );
	                }
    	            $anchorElem->append($text);
                } else {
	                $text = '';
                	if (isset($shuhen->nm)) {
	                    $text = $shuhen->nm;
    	            }
        	        if (isset($shuhen->kyori)) {
                    	$text = $text . (isset($shuhen->kyori) ? '<br><span>距離：'.$shuhen->kyori.'ｍ</span>': '' );
	                }
                	$trElem['td']->append($text);
                }
                $shuhenElem['div.table-article-info table']->append($trElem);
                $cnt = $cnt +1;
                if ($cnt > 7) break;
            }
            if ($cnt == 0) {
//                 $shuhenElem['section.section-around']->remove();
            }


        } else {
            $shuhenElem['.heading-article-lv2.around']->remove();
        }

        // 4689: Check lat, lon exist
        $ido = isset($dataModel->ido) ? $dataModel->ido : null;
        $keido = isset($dataModel->keido) ? $dataModel->keido : null;
        if (!$ido || !$keido) {
            $itemElem['section.info-around']->remove();
        }
        // #4692
        //if ($this->isFDP($pageInitialSettings)){
        $settingRow = $searchCond->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if ($this->isFDP($pageInitialSettings) && count($fdp->fdp_type) > 0){
            $townElem = $itemElem['div.item-detail-tab-body.article-town'];
            // $settingRow = $searchCond->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
            // $fdp = json_decode($settingRow->display_fdp);
        // END #4692
            // 4689: Check lat, lon exist
            if (!$ido || !$keido) {
                $itemElem['section.section-near-info']->remove();
                $itemElem['section.info-around']->remove();
            }
            if (!$this->canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp) || !$this->canDisplayMap($pageInitialSettings, $bukken, $shumoku)) {
                $itemElem['section.section-near-info']->remove();
            }
            // 4689: Check lat, lon exist
            if ($this->canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp) && ($ido && $keido)) {
                $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
                $ken_cd  = substr($shikugun_cd, 0, 2);
                $ken_ct = Services\ServiceUtils::getKenCtByCd($ken_cd);
                $ken_nm  = $dispModel->ken_nm;

                $townElem = $itemElem['div.item-detail-tab-body.article-town'];
                $townElem['div.link-direct ul']->remove();

                $townElem->attr('data-gmap-pin-lat', $dataModel->ido)->attr('data-gmap-pin-long', $dataModel->keido);
                $townElem->attr('data-gmap-center-lat',$dataModel->ido)->attr('data-gmap-center-long', $dataModel->keido);
                $townElem->attr('data-ken-cd', $ken_cd);

                $townElem['div.desc-wrapper .desc-item.item-2']->text($ken_nm);

                for($i = 1; $i <= count(Estate\FdpType::getInstance()->getTownClass()); $i++) {
                    $class = Estate\FdpType::getInstance()->getTownClass()[$i];
                    if(in_array((string)$i, $fdp->town_type)) {
                        $townElem['div.'.$class]->attr('data-type', $i);
                    } else {
                        $townElem['div.'.$class]->remove();
                    }
                }
            } else {
                $townElem->remove();
            }

            // 4689: Check lat, lon exist
            if ($this->canDisplayFdp(Estate\FdpType::ELEVATION_TYPE, $fdp) && ($ido && $keido)) {
                $thumbnail = Services\ServiceUtils::getMainImageForSP( $dispModel, $params ) ;
                if (! is_null($thumbnail) && ( isset( $thumbnail->url ) )) {
                    $url = $img_server . $thumbnail->url . "?width=160&height=160";
                } else {
                    $url = $img_server . "/image_files/path/no_image";
                }
                $itemElem['section.chart-area']->attr('data-src', $url);
                $itemElem['section.chart-area']->attr('data-gmap-pin-lat', $dataModel->ido)->attr('data-gmap-pin-long', $dataModel->keido);
                $itemElem['section.chart-area']->attr('data-gmap-center-lat', $dataModel->ido)->attr('data-gmap-center-long', $dataModel->keido);
                $itemElem['section.chart-area']->attr('data-api-key', Map::getGooleMapKeyForUserSite());
                $itemElem['section.chart-area']->attr('data-api-channel', Map::getGoogleMapChannel( $pageInitialSettings->getCompany() ));
            } else {
                $itemElem['section.chart-area']->remove();
            }
        } else {
            $itemElem['section.section-near-info']->remove();
            $itemElem['section.chart-area']->remove();
            $itemElem['div.item-detail-tab-body.article-town']->remove();
        }
    }

    /**
     * 情報提供会社要素の作成
     */
    protected function createSectionCompany($pageInitialSettings, $contentElem, $bukken, $shumoku)
    {
        //情報提供会社は最後のセクションとする。
        $secionNum = $contentElem->find('section')->length;
        $sectionIndex = ($secionNum==0) ? 0 : $secionNum-1;

        $companyElem = $contentElem['section:eq('.$sectionIndex.') div.table-article-info table'];

        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        // 問い合わせ先URL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	// 2次広告自動公開物件の場合、主契約会員情報を設定
            $kaiin_link_no = $pageInitialSettings->getKaiinLinkNo();
            $kaiin_no = $pageInitialSettings->getMemberNo();
        } else {
        	$kaiin_link_no = $this->getVal('kaiin_link_no', $dispModel, true);
            $kaiin_no = $dispModel->csite_muke_kaiin_no;
        }

        // 店舗画像
//         $thumbnailUrl = "/image_files/index/member_agent/". $kaiin_link_no . "/3.jpeg";
//         $url = $img_server . $thumbnailUrl . "?width=120&height=120&margin=true";
//         $companyElem['div.company-body figure.company-ph img']->attr('src', $url );

        // 会員APIに接続して会員情報を取得。
        // KApi用パラメータ作成
        $apiParam = new KApi\KaiinParams();
        $apiParam->setKaiinNo($kaiin_no);
        // 結果JSONを元に要素を作成。
        $apiObj = new KApi\Kaiin();
        $kaiinInfo = (object) $apiObj->get($apiParam, '会員基本取得');

        $companyElem['tr:eq(0) th']->text($kaiinInfo->ippanShogo['shogoName']);
        $railTxt = $this->getVal('railLineName', $kaiinInfo) . '／' . $this->getVal('stationName', $kaiinInfo) .
            (empty($this->getVal('tohoJikan', $kaiinInfo, true)) ?
                (empty($this->getVal('tohoKyori', $kaiinInfo, true)) ? '' : "　徒歩". $this->getVal('tohoKyori', $kaiinInfo) . 'ｍ')
                : "　徒歩". $this->getVal('tohoJikan', $kaiinInfo) . '分');
        if ($this->getVal('busteiName', $kaiinInfo, true)) {
            $railTxt = $railTxt . '<br>' .
                "【バス】" . $this->getVal('busJikan', $kaiinInfo, true) . '分　' . $this->getVal('busteiName', $kaiinInfo, true) .
                (empty($this->getVal('busteiJikan', $kaiinInfo, true)) ?
                    (empty($this->getVal('busteiKyori', $kaiinInfo, true)) ? '' : "　停歩" . $kaiinInfo->busteiKyori . 'ｍ')
                    : "　停歩". $kaiinInfo->busteiJikan . '分');
        }
        $companyElem['tr:eq(1) td']->text('')->append($railTxt);

        $companyElem['tr:eq(2) td']->text($this->getVal('todofukenName', $kaiinInfo, true)
        			. $this->getVal('cityName', $kaiinInfo, true) . $this->getVal('townName', $kaiinInfo, true)
        			. $this->getVal('banchi', $kaiinInfo, true) . '　' . $this->getVal('buildingName', $kaiinInfo, true));
        $companyElem['tr:eq(3) td']->text(isset($kaiinInfo->contact['ippanTel']) ? $kaiinInfo->contact['ippanTel'] : '');
        $companyElem['tr:eq(4) td']->text($this->getVal('menkyoName', $kaiinInfo, true));

        $shozokuTxt = '';
        $dt = new \DateTime();
        $dt->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
        $today = $dt->format('Ymd');
        $i = 1;
        foreach ($kaiinInfo->shozokus as $shozoku) {
            $start_day = $this->getVal('shozokuDantaiNyukaiDate', $shozoku, true);
            if (empty($start_day)) $start_day = '20000101';
            $end_day = empty($this->getVal('shozokuDantaiTaikaiDate', $shozoku, true)) ? '20990101' : $shozoku['shozokuDantaiTaikaiDate'];
            if (strtotime($start_day) <= strtotime($today) && strtotime($end_day) >= strtotime($today)
                    && isset($shozoku['shozokuDantaiName']))
            {
//             	if ($i != 1) $shozokuTxt .= '　　　　　';
                $shozokuTxt = $shozokuTxt . $shozoku['shozokuDantaiName'] . '会員<br>';
                $i = $i +1;
                if ($i > 2) break;
            }
        }
        // 公取加盟を追加
        $kotoriTxt = isset($kaiinInfo->kotoriName) ? $kaiinInfo->kotoriName . '加盟' : '';
        $companyElem['tr:eq(5) td']->text('')->append($shozokuTxt . $kotoriTxt);

        // 取引態様
        // 二次広告(自動公開含む)では取引態様は、売物件の場合は「媒介」 、賃貸物件の場合は「仲介」 に強制的に変更する
        $torihikiTaiyo = '';
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	if (Services\ServiceUtils::isChintai($shumoku)) {
        		$torihikiTaiyo = '仲介';
        	} else {
        		$torihikiTaiyo = '媒介';
        	}
        } else {
        	$torihikiTaiyo = $this->getVal('csite_torihiki_taiyo', $dispModel, true);
        }
        $companyElem['tr:eq(6) td']->text($torihikiTaiyo);

        // お問い合わせボタン
        $companyElem['p.btn-mail-contact a']->attr('href', $inquiryURL);
    }

    protected function setProComment($contentElem, $dataModel, $dispModel) {
    	// おすすめコメント
    	$procomment = $this->getVal('staff_comment', $dispModel, true);
    	// ２次広告もしくは自動公開の場合は、おすすめコメントは表示しない。
    	if ($dispModel->niji_kokoku_jido_kokai_fl) {
    		$procomment = null;
    	}
        if (is_null($procomment)) {
            $contentElem['dl.pro-comment']->remove();
        } else {
            $contentElem['dl.pro-comment dd']->text($procomment);
        }
    }

    /**
     * （NHP-1806）
     * ■詳細画面・特記の「クレジットカード決済」の表示条件
     * 賃貸居住用[貸マン・貸アパ・貸戸建](スマホ)
     * 賃貸事業用[貸店・貸事・貸駐車・貸ビル・貸倉庫・その他](スマホ/PC)
     * 貸土地(スマホ/PC)
     * の二次広告自動公開物件でない時(niji_kokoku_jido_kokai_fl=false)、
     * 「特記」(現在display_model:tokkiを表示している認識)の「後ろ」に
     * display_model:credit_kessaiに何かしら値があったら
     * 全角スペースを挟んで「クレジットカード決済」の文字列を付与する。
     */
    protected function getTokkiVal($shumoku, $dataModel, $dispModel) {
  //       // 注意）賃貸系物件種別のみ対象となった変更のため、売買系物件種別はこのメソッドを使用していない。
  //       $tokkiTxt = $this->getVal('tokki', $dispModel, false);
  //       if ($dispModel->niji_kokoku_jido_kokai_fl) {
  //           return $tokkiTxt;
  //       }
  //       if (! $this->getVal('credit_kessai', $dispModel, true)) {
  //           return $tokkiTxt;
  //       }
  //       if($tokkiTxt == '-'){
		// 	$tokkiTxt = 'クレジットカード決済';
		// }else{
		// 	$tokkiTxt .= '　クレジットカード決済';
		// }
        $tokkiTxt = "-";
        $tokkiArr = $this->getVal('csite_tokkis', $dispModel, true);
        //クレジット決済は削除
        if(isset($tokkiArr['credit_kessai'])) unset($tokkiArr['credit_kessai']);

		$key	= "jutaku_seino_hosho_tsuki"	;
		if( isset( $tokkiArr[ $key	] ) )
		{	// ATHOME_HP_DEV-2840 項目整理「住宅性能保証付」項目削除に伴うフロント側では表示しない
			unset( $tokkiArr[ $key	] ) ;
		}

        if(is_array($tokkiArr)) {
            $tokkiTxt = implode("　", $tokkiArr);
        }

		if ( $tokkiTxt == "" )
		{
			$tokkiTxt	= "-"	;
		}

        return $tokkiTxt;
    }

    /**
     * （NHP-2822）
     *  ■詳細画面：使用部分面積を取得する
     *   仕様
     *    ・物件の上位種目が「売事業用（一括）(05)」の場合　　：display_model.tatemono_nobe_ms を表示する
     *    ・物件の上位種目が「売事業用（一括）(05)」以外の場合：display_model.tatemono_ms を表示する
     *
     */
    protected function getTatemonoMsVal($shumoku, $dataModel, $dispModel){

        return Services\ServiceUtils::getTatemonoMsVal($shumoku, $dataModel, $dispModel);

    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }
    protected function getKashihosho($dispModel){
        return nl2br($this->getVal('kashi_hosho', $dispModel),false);
    }
    protected function getKashihoken($dispModel){
        return nl2br($this->getVal('kashi_hoken', $dispModel),false);
    }
    protected function getHyoukaSyoumeisyo($dispModel){
        $defaultTex ='';

        if(isset($dispModel->inspection) && $dispModel->inspection != NULL){
            $defaultTex .= '■建物状況調査 <br>'.$this->getVal('inspection', $dispModel).'<br>';
        }
        if(isset($dispModel->choki_yuryo_jutaku_nintei_tsuchisho) && $dispModel->choki_yuryo_jutaku_nintei_tsuchisho != NULL){
            $defaultTex .= '■長期優良住宅認定通知書 <br>'.nl2br($this->getVal('choki_yuryo_jutaku_nintei_tsuchisho', $dispModel),false).'<br>';
        }
        if(isset($dispModel->flat35_tekigo_shomeisho) && $dispModel->flat35_tekigo_shomeisho != NULL){
            $defaultTex .= '■フラット35適合証明書 <br>'.$this->getVal('flat35_tekigo_shomeisho', $dispModel).'<br>';
        }
        if(isset($dispModel->flat35s_tekigo_shomeisho) && $dispModel->flat35s_tekigo_shomeisho != NULL){
            $defaultTex .= '■フラット35S適合証明書 <br>'.$this->getVal('flat35s_tekigo_shomeisho', $dispModel).'<br>';
        }
        if(isset($dispModel->taishin_kijun_tekigo_shomeisho) && $dispModel->taishin_kijun_tekigo_shomeisho != NULL){
            $defaultTex .= '■耐震基準適合証明書 <br>'.$this->getVal('taishin_kijun_tekigo_shomeisho', $dispModel).'<br>';
        }
        if(isset($dispModel->hotekigo_jokyo_chosa_hokokusho) && $dispModel->hotekigo_jokyo_chosa_hokokusho != NULL){
            $defaultTex .= '■法適合状況調査報告書 <br>'.$this->getVal('hotekigo_jokyo_chosa_hokokusho', $dispModel).'<br>';
        }
        if(isset($dispModel->tatemono_kensa_hokokusho) && $dispModel->tatemono_kensa_hokokusho != NULL){
            $defaultTex .= '■建築士等の建物検査報告書 <br>'.$this->getVal('tatemono_kensa_hokokusho', $dispModel).'<br>';
        }
        return empty($defaultTex) ? '-' : $defaultTex;
    }
    protected function getSonota($dispModel)
    {
        if (isset($dispModel->sonota_ichijikin) && $dispModel->sonota_ichijikin != null 
            && isset($dispModel->csite_kagi_kokandaito) && $dispModel->csite_kagi_kokandaito != null) {
            return $this->getVal('sonota_ichijikin', $dispModel).'、　'.$this->getVal('csite_kagi_kokandaito', $dispModel);
        }
        if (isset($dispModel->sonota_ichijikin) && $dispModel->sonota_ichijikin != null) {
            return $this->getVal('sonota_ichijikin', $dispModel);
        }
        if (isset($dispModel->csite_kagi_kokandaito) && $dispModel->csite_kagi_kokandaito != null) {
            return $this->getVal('csite_kagi_kokandaito', $dispModel);
        }
        return '-';
    }

    protected function isFDP($pageInitialSettings) {
        return Services\ServiceUtils::isFDP($pageInitialSettings);
    }

    protected function canDisplayFdp($type, $fdp) {
        return Services\ServiceUtils::canDisplayFdp($type, $fdp);
    }
}
