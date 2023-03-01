<?php
namespace Modules\V1api\Services\Pc\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\KApi;
use Library\Custom\Model\Estate;
use Library\Custom\Hp\Map;
use Library\Custom\Qr;
use Library\Custom\Model\Lists\FdpFacility;

abstract class DetailAbstract
{

    protected $logger;
    protected $_config;

    public function __construct()
    {

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


    protected function createMainPhoto($bukken, $mainInfoElem, $params)
    {
        $display_model = (object) $bukken[ 'display_model' ] ;
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        /*
         * メイン画像
         */
        $thumbnail = Services\ServiceUtils::getMainImageForPC( $display_model, $params ) ;
        if (! is_null($thumbnail) && ( isset( $thumbnail->url ) )) {
            $url = $img_server . $thumbnail->url . "?width=160&height=160&margin=true";
            $mainInfoElem['div.left figure.article-ph img']->attr('src', $url );
        } else {
            $url = $img_server . "/image_files/path/no_image";
            $mainInfoElem['div.left figure.article-ph img']->attr('src', $url );
        }
    }


    protected function createGallery($bukken, $itemElem, $codeList)
    {
        $dataModel = (object) $bukken['data_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        /*
         * フォトギャラリー
         */
        $galleryElem = $itemElem['div.photo-gallery'];
        $listNum = 0;
        $imgNum = 1;
        $ulElem = pq("<ul class='thumb-list list${listNum}'/>");
        $zoomElem = pq("<p class='photo-zoom'/>"); // hidden要素だが、JSで側で移動させる。
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
                if ($imgNum == 7 || $imgNum == 13 || $imgNum == 19) {
                    $galleryElem['div.thumb-view']->append($ulElem);
                    $listNum = $listNum +1;
                    $ulElem = pq("<ul class='thumb-list list${listNum}'/>");
                }
                $url = $img_server . $image->url;
                $imgElem = "<img data-desc='". $codeList['sub_category'][$this->getVal('sub_category', $image, true)] . "' src='${url}?width=386&amp;height=386&amp;' alt='". $this->getVal('caption', $image, true) . "' oncontextmenu='return(false);'>";
                $liElem = pq("<li class='num${imgNum}'/>");
                $imgboxElem = pq("<div class='imgbox'>");
                $imgboxElem->append($imgElem);
                $liElem->append($imgboxElem);
                $ulElem->append($liElem);

                $zoomUrl = $img_server . $image->url . "?width=640&amp;height=640&amp;margin=true";
                $zoomImg = "<img id='gallery_detail_${imgNum}' data-src='${zoomUrl}' alt='". $this->getVal('caption', $image, true) . "'>";
                $zoomElem->append($zoomImg);

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
                // サムネイル
                if ($imgNum == 7 || $imgNum == 13 || $imgNum == 19 || $imgNum == 25 || $imgNum == 31 || $imgNum == 37 || $imgNum == 43) {
                    $galleryElem['div.thumb-view']->append($ulElem);
                    $listNum = $listNum +1;
                    $ulElem = pq("<ul class='thumb-list list${listNum}'/>");
                }
                $url = $img_server . $aux_image->url;
                $imgElem = "<img data-desc='". $codeList['sub_category'][$this->getVal('sub_category', $aux_image, true)] . "' src='${url}?width=386&amp;height=386&amp;' alt='". $this->getVal('caption', $aux_image, true) . "' oncontextmenu='return(false);'>";
                $liElem = pq("<li class='num${imgNum}'/>");
                $imgboxElem = pq("<div class='imgbox'>");
                $imgboxElem->append($imgElem);
                $liElem->append($imgboxElem);
                $ulElem->append($liElem);

                $zoomUrl = $img_server . $aux_image->url . "?width=640&amp;height=640&amp;margin=true";
                $zoomImg = "<img id='gallery_detail_${imgNum}' data-src='${zoomUrl}' alt='". $this->getVal('caption', $aux_image, true) . "'>";
                $zoomElem->append($zoomImg);

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
                if ($imgNum == 7 || $imgNum == 13 || $imgNum == 19 || $imgNum == 25 || $imgNum == 31 || $imgNum == 37 || $imgNum == 43) {
                    $galleryElem['div.thumb-view']->append($ulElem);
                    $listNum = $listNum +1;
                    $ulElem = pq("<ul class='thumb-list list${listNum}'/>");
                }
                $url = $img_server . $shuhen_kankyo->url;
                $imgElem = "<img data-desc='". $shuhen_kankyo->shubetsu_nm . "' src='${url}?width=386&amp;height=386&amp;' alt='". $shuhen_kankyo->nm . "' oncontextmenu='return(false);'>";
                $liElem = pq("<li class='num${imgNum}'/>");
                $imgboxElem = pq("<div class='imgbox'>");
                $imgboxElem->append($imgElem);
                $liElem->append($imgboxElem);
                $ulElem->append($liElem);

                $zoomUrl = $img_server . $shuhen_kankyo->url . "?width=640&amp;height=640&amp;margin=true";
                $zoomImg = "<img id='gallery_detail_${imgNum}' data-src='${zoomUrl}' alt='". $this->getVal('caption', $shuhen_kankyo, true) . "'>";
                $zoomElem->append($zoomImg);

                $imgNum = $imgNum +1;
            }
        }
        
        // 0件処理
        if ($imgNum == 1 && $listNum == 0) {
            $galleryElem->remove();
        } else {
            $galleryElem['div.thumb-view']->append($ulElem);
            $galleryElem->append($zoomElem);
            if ($imgNum == 2) {
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
        $dispModel->niji_kokoku_jido_kokai_fl = true;
        $dispModel->panorama_contents_cd = 1;
        $dispModel->panorama_webvr_fl = true;
        $dispModel->csite_panorama_kokai_fl = true;
        $dataModel->panorama = [
            'url' => "https://rent.nurvecloud.com/panoramas/7152/embed",
            'url_for_niji_kokoku' => "https://vrpanoramad.athome.jp/panoramas/_NRVzZg32F/embed?user_id=00212975&from=at",
            'qr_code_url_for_webvr' => "https://vrpanoramad.athome.jp/panoramas/_NRVzZg32F/embed?user_id=00212975%26from=at%26view_mode=vr",
            'accessible' => true
        ];
        */

        $panoramaElem = $itemElem['section.section-movie'];
        $panoramaElem['ul.movie-thumb']->empty();
        $panoramaLink = $itemElem['div.panorama-link'];

        //VRパノラマ物件かどうか（新パノラマ）
        $panoramaType = Services\ServiceUtils::getPanoramaType($dispModel);

        if($panoramaType)
        {
            $panoramaUrl = $dataModel->panorama['url'];

            // NHP-4930 niji_kokoku_jido_kokai_fl参照しURL書き換え
            if(isset($dispModel->niji_kokoku_jido_kokai_fl)) {
                if($dispModel->niji_kokoku_jido_kokai_fl) {
                    $panoramaUrl = $dataModel->panorama['url_for_niji_kokoku'];
                }
            }

            if($dataModel->panorama['accessible']) {

                //VR内見(新パノラマでも？)では「パノラマ・ムービーをご覧いただくには～」の文言を非表示。
                $panoramaElem['p.link-dl']->empty();

                //VR内見(新パノラマでも？)では『タイトル：パノラマムービー』の文言を非表示。
                $panoramaElem['h3.heading-article-lv2']->empty();

                $anchor = '<iframe src="" data-original="'.$panoramaUrl.'" frameborder="0" scrollimg="0" class="panorama-frame"></iframe>';
                $panoramaElem->append($anchor);
                //「パノラマムービー」とかと違い、VR内見((新パノラマでも？)でのみ表示。
                $anchorLink = '<a href="'.$panoramaUrl.'" target="_blank">別ウィンドウで開く</a>';
                $panoramaLink->append($anchorLink);
            
                if($panoramaType == Services\ServiceUtils::PANORAMA_TYPE_VR) {
                    $panoramaQrUrl = $panoramaUrl . "&view_mode=vr";

                    $panoramaLink->after('<div class="panorama-qr"></div>');
                    $panoramaQr = $itemElem['div.panorama-qr'];
                    $panoramaQr->attr('style', 'border-radius:10px 10px; width:90%;background-color:#eeece1;margin:0 auto 20px;padding:13px');
		        	$panoramaQr->append('<h2>VRで見る</h2>');
                    $qrImageBin = sprintf("data:image/png;base64,%s", base64_encode(Qr::pngBinary($panoramaQrUrl, 'M', 3, 3)));
                    $panoramaQr->append(sprintf("<div class='element-img-left'><img src='%s' style='max-width:170px'/></div>", $qrImageBin));
                    $qrAttention = 'おうちにいながら物件を内見しているように見ることができます。<br/>QRコードを読み取り、ＶＲが再生できます。ＶＲゴーグルで見てください。';
                    $panoramaQr->append(sprintf("<div class='element-left' style='width: 68%%'>%s</div>", $qrAttention));
                    $panoramaQr->append("<div style='clear:both;'></div>");
                }
            } else {
                $panoramaElem->remove();
            }
        }
        else
        {
            $panoramaElem->remove();
        }

    }


    /**
     * アピールポイント
     * 「一般メッセージ詳細」がある場合は「お問い合わせボタンを追加する」
     */
    protected function createIppanMessageShosai($bukken, $itemElem){

        $dispModel = (object) $bukken['display_model'];

        $ippanMessageShosai = $this->getVal('ippan_message_shosai', $dispModel, true);

        if($ippanMessageShosai){
            $appealElem = $itemElem['.appeal-home p'];
            $appealElem->append(nl2br($ippanMessageShosai));
        }else{
            $itemElem['.appeal-home']->remove();
            $itemElem['#contact-under-appeal-home']->remove();
            $itemElem['#title-appeal-home']->remove();
        }
    }


    /**
     * 周辺情報タブを表示できるかどうか
     * 　周辺情報と地図が両方共表示されない状態であれば、周辺情報タブを表示しない
     */
    protected function canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku, $fdp)
    {

        //周辺情報と地図が両方共表示されない状態であれば、周辺情報タブを表示しない
        if( !$this->canDisplayShuhenInfo($pageInitialSettings, $bukken, $shumoku) &&
            !$this->canDisplayMap($pageInitialSettings, $bukken, $shumoku) &&
            !$this->canDisplayFdp(Estate\FdpType::ELEVATION_TYPE, $fdp) || !Services\ServiceUtils::checkLatLon($bukken)) {
            return false;
        }
        return true;
    }

    /**
     * 周辺情報を表示できるかどうか
     * 　下記の場合に表示できないと判定する
     * 　・物件情報に周辺環境（data_model.shuhen_kankyos）が含まれない場合
     *
     */
    protected function canDisplayShuhenInfo($pageInitialSettings, $bukken, $shumoku){

        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        if(isset($dataModel->shuhen_kankyos)){
            return true;
        }
        return false;
    }

    /**
     * 地図を表示できるかどうかを取得する
     * 　下記の場合に表示できないと判定する
     * 　・物件情報に、「地図検索不可フラグ(data_model.chizu_kensaku_fuka_fl=true)」が含まれる場合
     * 　・物件情報を所有する会員の会員情報に、「地図情報利用不可(isNotUsedMapDisplay=true)」が含まれる場合
     */
    protected function canDisplayMap( $pageInitialSettings, $bukken, $shumoku){
        return Services\ServiceUtils::canDisplayMap($pageInitialSettings,$bukken, $shumoku);
    }


    /**
     * 物件詳細ページのタブエリアを作成する
     * 　・物件情報タブ
     * 　・周辺情報タブ
     *
     */
    protected function createTabArea($bukken, $itemElem, $pageInitialSettings, $shumoku,  $codeList,  $params, $searchCond)
    {

        // 物件情報タブと周辺情報タブ
        $tab = $itemElem['.tab'];
        $bukkenTab = $tab['li:nth-child(1) a'];
        $shuhenTab = $tab['li:nth-child(2) a'];

        // タブのリンク
        $estateDetailUrl = "/{$params->getTypeCt()}/detail-{$params->getBukkenId()}/";
        // 4593: check anchor link detail
        $bukkenTab->attr('href', $estateDetailUrl.'#property');
        $shuhenTab->attr('href', "{$estateDetailUrl}map.html#nearby");

        $settingRow = $searchCond->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        // #4692
        // if ($this->isFDP($pageInitialSettings) && $this->canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp)) {
        // 4689: Check lat, lon exist
        if ($this->isFDP($pageInitialSettings) && $this->canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp) && Services\ServiceUtils::checkLatLon($bukken)) {
        // END #4692
            $html = '<li id="town"><a href="'.$estateDetailUrl.'townstats.html#town">街のこと(統計情報)</a></li>';
            $tab['ul']->append($html);
        }
        // 周辺情報タブの作成
        if ($params->getTab() == 2)
        {
            // タブ切り替え
            $itemElem['div.tab:first li:not(nth-child(2))']->removeClass('cu');
            $itemElem['div.tab:first li:nth-child(2)']->addClass('cu');
            $itemElem['div.tab.bottom li:not(nth-child(2))']->removeClass('cu');
            $itemElem['div.tab.bottom li:nth-child(2)']->addClass('cu');

            $this->createShuhenTab($bukken, $itemElem, $pageInitialSettings, $shumoku, $fdp);

            // 物件情報タブの削除
            $itemElem['div.item-detail-tab-body.article-info']->remove();
            $itemElem['ul.article-info-attention']->remove();

            $itemElem['div.item-detail-tab-body.article-town']->remove();

            //  周辺情報タブに表示するものがなければ、404
            if(!$this->canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku, $fdp)){
                throw new \Exception('地図ページはありません。', 404);
            }

        // 物件情報タブの作成
        } elseif($params->getTab() == 1) {

            $this->createBukkenTab($bukken, $itemElem, $pageInitialSettings, $shumoku, $codeList, $fdp);

            // 周辺情報タブの削除
            $itemElem['div.item-detail-tab-body.around-info']->remove();

            $itemElem['div.item-detail-tab-body.article-town']->remove();

            //  周辺情報タブに表示するものがなければ、タブリンクを消す
            if(!$this->canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku, $fdp)){
                $shuhenTab->parent()->remove();
            }
        } else {
            $itemElem['div.tab:first li:not(nth-child(3))']->removeClass('cu');
            $itemElem['div.tab:first li:nth-child(3)']->addClass('cu');
            $itemElem['div.tab.bottom li:not(nth-child(3))']->removeClass('cu');
            $itemElem['div.tab.bottom li:nth-child(3)']->addClass('cu');

            $itemElem['ul.article-info-attention']->remove();
            $this->createTownTab($bukken, $itemElem, $fdp, $shumoku);

            if(!$this->canDisplayShuhenTab($pageInitialSettings, $bukken, $shumoku, $fdp)){
                $shuhenTab->parent()->remove();
                // 4689: Check lat, lon exist
                if (!Services\ServiceUtils::checkLatLon($bukken)) {
                    throw new \Exception('', 404);
                }
            }
        }
    }



    /**
     * 周辺情報要素の作成
     */
    protected function createShuhenTab($bukken, $itemElem, $pageInitialSettings, $shumoku, $fdp)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        // 周辺情報
        $shuhenElem = $itemElem['div.item-detail-tab-body.around-info'];

        if ($this->isFDP($pageInitialSettings) && count($fdp->fdp_type) > 0) {
            $this->createShuhenTabFdp($bukken, $shuhenElem, $pageInitialSettings, $shumoku, $fdp);
        } else {
            $shuhenElem['section.chart-area']->remove();
            $shuhenElem['div.link-direct']->remove();
            $shuhenElem['p.btn-mail-contact a .btn-contact-fdp']->remove();
        }

        // 地図の表示判定
        if ($this->canDisplayMap($pageInitialSettings, $bukken, $shumoku))
        {
            $shuhenElem['section.section-map-fdp']->remove();
            $shuhenElem['section.section-map p.map-address']->text($this->getVal('csite_shozaichi', $dispModel));

            $mapElem = $shuhenElem['section.section-map div.map-article'];

            // 4689: Check lat, lon exist
            if (Services\ServiceUtils::checkLatLon($bukken)) {
                $ido = $dataModel->ido;
                $keido = $dataModel->keido;
                $mapElem->attr('data-gmap-pin-lat', $ido)->attr('data-gmap-pin-long', $keido);
                $mapElem->attr('data-gmap-center-lat', $ido)->attr('data-gmap-center-long', $keido);
            }
            $mapElem->attr('data-api-key', Map::getGooleMapKeyForUserSite());
            $mapElem->attr('data-api-channel', Map::getGoogleMapChannel( $pageInitialSettings->getCompany() ));
            $mapAnnotationText = Services\ServiceUtils::getShuhenMapAnnotation($this->getVal('matching_level_cd', $dispModel));
            if (!empty($mapAnnotationText)) {
                $shuhenElem['section.section-map p.map-annotation']->html($mapAnnotationText);
            }
            $shuhenElem['#goto-map p.peripheral-annotation']->remove();
        }
        else
        {
            $shuhenElem['section.section-map']->remove();
            $shuhenElem['section.section-map-fdp']->remove();
        }

        $shuhenElem['ul.around-list']->empty();
        if ($this->canDisplayShuhenInfo($pageInitialSettings, $bukken, $shumoku)) {
            $zoomElem = pq("<p class='photo-zoom'/>"); // hidden要素だが、JSで側で移動させる。
            $cnt = 0;
            $imgCnt = 0;
            foreach ($dataModel->shuhen_kankyos as $elem)
            {
                if (empty($elem['nm'])) {
                    continue;
                }
                $liElem = pq('<li/>');
                $shuhen = (object) $elem;
                if (isset($shuhen->url)) {
                    $url = $img_server . $shuhen->url . "?width=156&height=117&margin=true";
                    $figElem = pq('<figure class="around-thumb"><a href=""><img src="" title=""/></a></figure>');
                    $figElem['img']->attr('src', $url);
                    if (isset($shuhen->nm)) {
                        $title = $shuhen->nm . (isset($shuhen->kyori) ? 'まで'.$shuhen->kyori.'ｍ': '' );
                        $figElem['img']->attr('title', $title);
                    }
                    $liElem->append($figElem);

                    $imgCnt++;
                    $zoomUrl = $img_server . $shuhen->url . "?width=640&amp;height=640&amp;margin=true";
                    $zoomImg = "<img id='gallery_detail_{$imgCnt}' data-src='${zoomUrl}'>";
                    $zoomElem->append($zoomImg);
                }
                if (isset($shuhen->nm)) {
                    $title = $shuhen->nm . (isset($shuhen->kyori) ? 'まで'.$shuhen->kyori.'ｍ': '' );
                    $liElem->append($title);
                }
                if (isset($shuhen->shubetsu_nm)) {
                    $title = '<span class="tx-desc">' . $shuhen->shubetsu_nm .'</span>';
                    $liElem->append($title);
                }
                $shuhenElem['ul.around-list']->append($liElem);
                $cnt = $cnt +1;
                if ($cnt > 7) break;
            }
            if ($cnt == 0) {
                $shuhenElem['section.section-around']->remove();
            } else if ($imgCnt > 0) {
                $shuhenElem['section.section-around']->append($zoomElem);
            }
        } else {
            $shuhenElem['section.section-around']->remove();
            $shuhenElem['div.link-direct li.goto-around']->remove();
        }

        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        $shuhenElem['p.btn-mail-contact a']->attr('href', $inquiryURL);
    }

    protected function createTownTab($bukken, $itemElem, $fdp, $shumoku)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
        $ken_cd  = substr($shikugun_cd, 0, 2);
        $ken_ct = Services\ServiceUtils::getKenCtByCd($ken_cd);
        $ken_nm  = $dispModel->ken_nm;

        $townElem = $itemElem['div.item-detail-tab-body.article-town'];
        $townElem['div.link-direct ul']->remove();

        // 4689: Check lat, lon exist
        if (Services\ServiceUtils::checkLatLon($bukken)) {
            $ido = $dataModel->ido;
            $keido = $dataModel->keido;
            $townElem->attr('data-gmap-pin-lat', $ido)->attr('data-gmap-pin-long', $keido);
            $townElem->attr('data-gmap-center-lat', $ido)->attr('data-gmap-center-long', $keido);
        }
        $townElem->attr('data-ken-cd', $ken_cd);

        $townElem['div.group-title li.note-pref']->text($ken_nm);

        $li = '';
        $count = 0;
        foreach(Estate\FdpType::getInstance()->getTownClass() as $key=> $classTown) {
            if(in_array((string)$key, $fdp->town_type)) {
                $count++;
                $townElem['div.'.$classTown]->attr('data-type', $key);
                $li .= '<li><a href="#'.$classTown.'">'.Estate\FdpType::getInstance()->getTown()[$key].'</a></li>';
                if ($count == 3) {
                    $townElem['div.link-direct']->append('<ul>'.$li.'</ul>');
                    $li = '';
                    $count = 0;
                }
            } else {
                $townElem['div.'.$classTown]->remove();
            }
            if ($key == count(Estate\FdpType::getInstance()->getTownClass()) && $count < 3) {
                $townElem['div.link-direct']->append('<ul>'.$li.'</ul>');
            }
        }

        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        $townElem['p.btn-mail-contact a']->attr('href', $inquiryURL);

        $bukkenElem = $itemElem['div.item-detail-tab-body.article-info'];
        $bukkenElem->remove();
        $shuhenElem = $itemElem['div.item-detail-tab-body.around-info'];
        $shuhenElem->remove();
    }

    protected function createShuhenTabFdp($bukken, $shuhenElem, $pageInitialSettings, $shumoku, $fdp) {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 4689: Check lat, lon exist
        if (Services\ServiceUtils::checkLatLon($bukken)) {
            $ido = $dataModel->ido;
            $keido = $dataModel->keido;
        }
        // 周辺施設情報
        if ($this->canDisplayMap($pageInitialSettings, $bukken, $shumoku)) {
            if ($this->canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp)) {
                $shuhenElem['section.section-map']->remove();
                $fdpElem = $shuhenElem['section.section-map-fdp']->removeClass('section-map-fdp')->addClass('section-map');
                $fdpElem->addClass('has-facility');
                $fdpElem['div.wrap-box div.wrapper-table']->remove();

                $facilityMenu = $fdpElem['div.tag-menu ul'];
                $facilityMenu['li']->remove();
                $facility = '';
                $count = 0;
                foreach (FdpFacility::getInstance()->listFacilityName() as $key=>$name) {
                    $class = in_array($key, FdpFacility::getInstance()->listFacilityDisplayBegin()) ? 'show' : 'hidden';
                    $facility .=  '<li id="'.$key.'" class="'.$class.'"><i></i>'.$name.'</li>';
                }
                $facilityMenu->append($facility);
            } else {
                $shuhenElem['section.section-map-fdp']->remove();
                $shuhenElem['section.section-map p.map-address']->text($this->getVal('csite_shozaichi', $dispModel));
                $shuhenElem['div.link-direct li.goto-map a']->text('地図');
                $shuhenElem['section.section-map div.map-article']->attr('class', 'map-facility')->attr('id', 'map');
                $shuhenElem['#goto-map p.peripheral-annotation']->remove();
            }
            $shuhenElem['section.section-map']->attr('id', 'goto-map');
            $mapElem = $shuhenElem['section.section-map div.map-facility'];
            // 4689: Check lat, lon exist
            if (Services\ServiceUtils::checkLatLon($bukken)) {
                $mapElem->attr('data-gmap-pin-lat', $ido)->attr('data-gmap-pin-long', $keido);
                $mapElem->attr('data-gmap-center-lat', $ido)->attr('data-gmap-center-long', $keido);
            }
            $mapElem->attr('data-api-key', Map::getGooleMapKeyForUserSite());
            $mapElem->attr('data-api-channel', Map::getGoogleMapChannel( $pageInitialSettings->getCompany() ));
            $mapAnnotationText = Services\ServiceUtils::getShuhenMapAnnotation($this->getVal('matching_level_cd', $dispModel));
            if (!empty($mapAnnotationText)) {
                $shuhenElem['section.section-map p.map-annotation']->html($mapAnnotationText);
            }
        } else {
            $shuhenElem['section.section-map']->remove();
            $shuhenElem['section.section-map-fdp']->remove();
            $shuhenElem['div.link-direct li.goto-map']->remove();
        }

        // 道のりと高低差
        if ($this->canDisplayFdp(Estate\FdpType::ELEVATION_TYPE, $fdp)) {
            // 4689: Check lat, lon exist
            if (Services\ServiceUtils::checkLatLon($bukken)) {
                $shuhenElem['section.chart-area']->attr('data-gmap-pin-lat', $ido)->attr('data-gmap-pin-long', $keido);
                $shuhenElem['section.chart-area']->attr('data-gmap-center-lat', $ido)->attr('data-gmap-center-long', $keido);
            }
            $shuhenElem['section.chart-area']->attr('data-api-key', Map::getGooleMapKeyForUserSite());
            $shuhenElem['section.chart-area']->attr('data-api-channel', Map::getGoogleMapChannel( $pageInitialSettings->getCompany() ));
        } else {
            $shuhenElem['section.chart-area']->remove();
            $shuhenElem['div.link-direct li.goto-chart']->remove();
        }
    }

    /**
     * 情報提供会社要素の作成
     */
    protected function createSectionCompany($pageInitialSettings, $companyElem, $bukken, $shumoku, $fdp)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        // 問い合わせ先URL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        // 二次広告自動公開判定
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
            // 二次広告自動公開物件の場合、主契約会員情報を設定
            $kaiin_link_no = $pageInitialSettings->getKaiinLinkNo();
            $kaiin_no = $pageInitialSettings->getMemberNo();
        } else {
            $kaiin_link_no = $this->getVal('kaiin_link_no', $dispModel, true);
            $kaiin_no = $dispModel->csite_muke_kaiin_no;
        }

        //         // 店舗画像
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

        $shogoName = isset($kaiinInfo->ippanShogo['shogoName']) ?
            $kaiinInfo->ippanShogo['shogoName'] : '-';
        $companyElem['p.company-name']->text($shogoName);
        $railTxt = $this->getVal('railLineName', $kaiinInfo) . '／' . $this->getVal('stationName', $kaiinInfo);
        $railTxt .= (empty($this->getVal('tohoJikan', $kaiinInfo, true)) ?
            (empty($this->getVal('tohoKyori', $kaiinInfo, true)) ? '' : "　徒歩". $this->getVal('tohoKyori', $kaiinInfo) . 'ｍ')
            : "　徒歩". $this->getVal('tohoJikan', $kaiinInfo) . '分');
        if ($this->getVal('busteiName', $kaiinInfo, true)) {
            if (empty($this->getVal('busJikan', $kaiinInfo, true))) {
                $railTxt = $railTxt . '<br>' .
                "　　　【バス】" . $this->getVal('busteiName', $kaiinInfo, true);
            } else {
                $railTxt = $railTxt . '<br>' .
                "　　　【バス】" . $this->getVal('busJikan', $kaiinInfo, true) . '分　' . $this->getVal('busteiName', $kaiinInfo, true);
            }
            $railTxt .=
                (empty($this->getVal('busteiJikan', $kaiinInfo, true)) ?
                    (empty($this->getVal('busteiKyori', $kaiinInfo, true)) ? '' : "　停歩" . $kaiinInfo->busteiKyori . 'ｍ')
                    : "　停歩". $kaiinInfo->busteiJikan . '分');
        }
        $companyElem['dl.company-tx dd:eq(0)']->text('')->append($railTxt);
        $companyElem['dl.company-tx dd:eq(1)']->text($this->getVal('todofukenName', $kaiinInfo, true)
                                                     . $this->getVal('cityName', $kaiinInfo, true) . $this->getVal('townName', $kaiinInfo, true)
                                                     . $this->getVal('banchi', $kaiinInfo, true) . '　' . $this->getVal('buildingName', $kaiinInfo, true));
        $ippanTel = isset($kaiinInfo->contact['ippanTel']) ? $kaiinInfo->contact['ippanTel'] : '';
        $companyElem['dl.company-tx dd:eq(2)']->text($ippanTel);
        $daihyoFax = isset($kaiinInfo->contact['daihyoFax']) ? $kaiinInfo->contact['daihyoFax'] : '';
        $companyElem['dl.company-tx dd:eq(3)']->text($daihyoFax);
        $shozokuHtml = '';
        $shozokuArray = [];
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
                $shozokuArray[] = "{$shozoku['shozokuDantaiName']}会員";
                if (++$i > 2) break;
            }
        }

        if (count($shozokuArray) < 1){
            $shozokuArray = ['&nbsp;'];
        }

        foreach ($shozokuArray as $text) {
            $shozokuHtml .= "<tr><td>{$text}</td></tr>";
        }
        $shozokuHtml = "<table><tbody>{$shozokuHtml}</tbody></table>";
        $companyElem['dl.company-tx dd:eq(4)']->empty()->append($shozokuHtml);

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
        $companyElem['dl.company-tx dd:eq(5)']->text($torihikiTaiyo);
        $companyElem['dl.company-tx dd:eq(6)']->text($this->getVal('menkyoName', $kaiinInfo, true));
        // 公取加盟を追加
        $kotoriTxt = isset($kaiinInfo->kotoriName) ? $kaiinInfo->kotoriName . '加盟' : '';
        $companyElem['dl.company-tx dd:eq(7)']->text('')->append($kotoriTxt);

        // お問い合わせボタン
        $companyElem['p.btn-mail-contact a']->attr('href', $inquiryURL);
        // #4692
        // if ($this->isFDP($pageInitialSettings)) {
		if (!$this->isFDP($pageInitialSettings) || count($fdp->fdp_type) == 0) {
		// END #4692
            $companyElem['p.btn-mail-contact a .btn-contact-fdp']->remove();
        }
    }


    protected function setProComment($itemElem, $dataModel, $dispModel) {
        // おすすめコメント
        $procomment = $this->getVal('staff_comment', $dispModel, true);
        // ２次広告もしくは自動公開の場合は、おすすめコメントは表示しない。
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
            $procomment = null;
        }
        if (is_null($procomment)) {
            $itemElem['dl.pro-comment']->remove();
        } else {
            $itemElem['dl.pro-comment dd']->text($procomment);
        }
    }

    protected function setArticlePoint($mainInfoElem, $dataModel, $dispModel) {

//      $pointTxt = $this->getVal('tokki', $dispModel, true);
//     	if ($this->getVal('kanri_hoshiki_cd', $dataModel, true)) {
//     		switch ($this->getVal('kanri_hoshiki_cd', $dataModel, true)) {
//     			case '1':
//     				$pointTxt .= '　管理人常駐';
//     				break;
//     			case '2':
//     				$pointTxt .= '　日勤管理';
//     				break;
//     			case '3':
//     				$pointTxt .= '　巡回管理';
//     				break;
//     		}
//     	}
//     	$pointTxt .= $this->getVal('pet_sodan_ari_fl', $dataModel, true) ? '　ペット相談' : null;
//     	$pointTxt .= $this->getVal('free_rent_ari_fl', $dataModel, true) ? '　フリーレント' : null;
//     	$pointTxt .= $this->getVal('maisonnet_ari_fl', $dataModel, true) ? '　メゾネット' : null;
//     	$pointTxt .= $this->getVal('reform_ari_fl', $dataModel, true) ?    '　リフォーム' : null;
//     	$pointTxt .= $this->getVal('renovation_fl', $dataModel, true) ?    '　リノベーション' : null;
//     	$pointTxt .= $this->getVal('resort_fl', $dataModel, true) ?        '　リゾート向き' : null;
//     	$pointTxt .= $this->getVal('credit_kessai', $dispModel, true) ?    '　クレジットカード決済' : null;
// //     	$pointTxt .= $this->getVal('nairankai_joho', $dispModel, true);


//         //④再構築不可を削除を追加
//         $pointTxt = str_replace("再建築不可", "", $pointTxt);

//         //物件詳細⑦表示箇所変更
//         $delete_str = [
//             '単身者限定',
//             '非喫煙者限定',
//             'BELS/省エネ基準適合認定',
//             '低炭素住宅（省エネ性高い）',
//             '省エネラベルあり',
//             '建築確認完了検査済証あり',
//             '法適合状況調査報告書あり',
//             'インスペクション（建物検査）済み',
//             '修繕・点検の記録あり',
//             '空き家バンク登録物件',
//             '新築時・増改築時の設計図書あり',
//         ];
//         foreach ($delete_str as $key => $value) {
//             $pointTxt = str_replace($value, "", $pointTxt);
//         }

        $pointTxt = "";
        $pointArr = $this->getVal('csite_osusumes', $dispModel, true);


        //２次広告の場合,クレジット決済は削除
        if(!isset($dispModel->niji_kokoku_jido_kokai_fl) || $dispModel->niji_kokoku_jido_kokai_fl == true) {
           if(isset($pointArr['credit_kessai'])) unset($pointArr['credit_kessai']);
        }

		$key	= "jutaku_seino_hosho_tsuki"	;
		if( isset( $pointArr[ $key	] ) )
		{	// ATHOME_HP_DEV-2840 項目整理「住宅性能保証付」項目削除に伴うフロント側では表示しない
			unset( $pointArr[ $key	] ) ;
		}

        if(is_array($pointArr)) {
            $pointTxt = implode("　", $pointArr);
        }

    	if (empty($pointTxt))
    	{
    		$mainInfoElem['section.article-point']->remove();
    	} else {
    		$mainInfoElem['section.article-point p']->text($pointTxt);
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
  //       if ($shumoku == Services\ServiceUtils::TYPE_CHINTAI) {
  //           // ※そもそも種別：賃貸の物件詳細画面に特記の項目は無いのでここに来ることは無いが念のため
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

        if(is_array($tokkiArr)) {
            $tokkiTxt = implode("　", $tokkiArr);
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
