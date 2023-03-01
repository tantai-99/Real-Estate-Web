<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Services;

class Koma
{

    public function __construct()
    {
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }
    
	public function createKoma($komaElem, $komaList, $params, $pageInitialSettings)
	{
        // 表示行数
        $row = $params->getKomaRows();
        
        $total = $komaList['total_count'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        $komaElem->empty();

        // .element-recommend
        $komaElem->append(pq('<div class="element element-recommend estate-koma" />'));
        $komaElem['div.element-recommend']->attr('data-special-path', $params->getSpecialPath());

        $usedIndex = array();
        for ($i=0; $i < $row * 4 && $i < $total; $i++)
        {
            $bukken = $komaList['bukkens'][$i];            
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
        // <div class="recommend-item">
        //   <p class="recommend-ph">
        //     <a href="#"><img src="/pc/imgs/img_loading.gif" alt=""></a></p>
        //   <p class="recommend-name"><a href="#">物件名</a></p>
        //   <p class="tx-price">価格：-万円</p>
        //   <p class="recommend-kind">間取り：-</p>
        //   <p class="recommend-station">徒歩-分</p>
        // </div>

            //賃料非表示
            $isKakakuHikokai = false;
            if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
                $isKakakuHikokai = true;
            }

            // ATHOME_HP_DEV-4841 : 詳細URLの生成方法を変更 - Services\ServiceUtils::getDetailURL利用 -
            // $type_ct = Services\ServiceUtils::getShumokuCtByCd($dispModel->csite_bukken_shumoku_cd[0]);
            // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
            $detail_url = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting);

            $divElem = pq('<div class="recommend-item" />');
            // image
	        $thumbnail = Services\ServiceUtils::getMainImageKoma($dataModel, $params);
    	    $img_url = '';
            $img_domain = $this->getImgDomain($img_server);
            if (!is_null($thumbnail)) {
                $img_url = $img_domain.$thumbnail->url."?width=320&amp;height=320&amp;margin=true";
            }
            else {
                $img_url = $img_domain."/image_files/path/no_image";
            }
            $divElem->append('<p class="recommend-ph"><a href="' . $detail_url . '" target="_blank"><img data-original="'. $img_url .  '" alt=""></a></p>');
            $divElem->append('<p class="recommend-name"><a href="' . $detail_url . '" target="_blank">' . Services\ServiceUtils::replaceSsiteBukkenTitle($this->getVal('csite_bukken_title', $dispModel, true)) . '</a></p>');
            if(!$isKakakuHikokai) {
                $divElem->append('<p class="tx-price">' . $this->getVal('csite_kakaku', $dispModel) .'</p>');
            }else{
                $divElem->append('<p class="tx-price">' . "賃料要確認" .'</p>');
            }


            $divElem->append('<p class="recommend-kind">' . $this->getMadoriMenseki($dispModel) . '/' . Services\ServiceUtils::getShumokuDispModel($dispModel) . '</p>');
            $divElem->append('<p class="recommend-station">' . $this->csiteKotsuSubstr($dispModel) . '</p>');
            $komaElem['div.element-recommend']->append($divElem);
        }
	}
    
	public function createRecommendKoma($recommendElem, $recommendList, $params, $pageInitialSettings)
	{
        $total = $recommendList['total_count'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        $itemsElem = $recommendElem['div.recommend-item-show'];
        $itemsElem->empty();
        $usedIndex = array();
        for ($i=0; $i < 10 && $i < $total; $i++)
        {
            array_push($usedIndex, $i);
            $bukken = $recommendList['bukkens'][$i];            
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            //賃料非表示
            $isKakakuHikokai = false;
            if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
                $isKakakuHikokai = true;
            }

            // ATHOME_HP_DEV-4841 : 詳細URLの生成方法を変更 - Services\ServiceUtils::getDetailURL利用 -
            // $type_ct = Services\ServiceUtils::getShumokuCtByCd($dispModel->csite_bukken_shumoku_cd[0]);
            // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
            $detail_url = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting);

            $divElem = pq('<div class="recommend-item" />');
            // image
	        $thumbnail = Services\ServiceUtils::getMainImageKoma($dataModel, $params);
    	    $img_url = '';
	        if (! is_null($thumbnail)) {
    	        $img_url = $img_server . $thumbnail->url . "?width=320&amp;height=320&amp;margin=true";
 	        } else {
    	        $img_url = $img_server . "/image_files/path/no_image";
        	}
            $divElem->append('<p class="recommend-ph"><a href="' . $detail_url . '" target="_blank"><img src="'. $img_url .  '" alt=""></a></p>');
            $divElem->append('<p class="recommend-name"><a href="' . $detail_url . '" target="_blank">' . Services\ServiceUtils::replaceSsiteBukkenTitle($this->getVal('csite_bukken_title', $dispModel, true)) . '</a></p>');


            if(!$isKakakuHikokai) {
                $divElem->append('<p class="tx-price">' . $this->getVal('csite_kakaku', $dispModel) .'</p>');
            }else{
                $divElem->append('<p class="tx-price">' .  "賃料要確認".'</p>');
            }


            $divElem->append('<p class="recommend-kind">' . $this->getMadoriMenseki($dispModel) . '/' . Services\ServiceUtils::getShumokuDispModel($dispModel) . '</p>');
            $divElem->append('<p class="recommend-station">' . $this->csiteKotsuSubstr($dispModel) . '</p>');
            $itemsElem->append($divElem);
        }
	}

	public function createHistoryKoma($historyElem, $bukkenList, $params, $pageInitialSettings)
	{

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;
        $historyElem['div']->remove();
        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            //賃料非表示
            $isKakakuHikokai = false;
            if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
                $isKakakuHikokai = true;
            }

            // ATHOME_HP_DEV-4841 : 詳細URLの生成方法を変更 - Services\ServiceUtils::getDetailURL利用 -
            // $type_ct = Services\ServiceUtils::getShumokuCtByCd($dispModel->csite_bukken_shumoku_cd[0]);
            // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
            $detail_url = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting);
            
            $divElem = pq('<div class="watch-list" />');
            // image
	        $thumbnail = Services\ServiceUtils::getMainImageKoma($dataModel, $params);
    	    $img_url = '';
	        if (! is_null($thumbnail)) {
    	        $img_url = $img_server . $thumbnail->url . "?width=320&amp;height=320&amp;margin=true";
 	        } else {
    	        $img_url = $img_server . "/image_files/path/no_image";
        	}
            $divElem->append('<p class="watch-ph"><a href="' . $detail_url . '" target="_blank"><img src="'. $img_url .  '" alt=""></a></p>');
            $divElem->append('<p class="watch-name"><a href="' . $detail_url . '" target="_blank">' . Services\ServiceUtils::replaceSsiteBukkenTitle($this->getVal('csite_bukken_title', $dispModel, true)) . '</a></p>');
            if(!$isKakakuHikokai) {
                $divElem->append('<p class="watch-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>');
            }else{
                $divElem->append('<p class="watch-price">' . "賃料要確認". '</p>');
            }
            $divElem->append('<p class="watch-kind">' . $this->getMadoriMenseki($dispModel) . '/' . Services\ServiceUtils::getShumokuDispModel($dispModel) . '</p>');
            $divElem->append('<p class="watch-time">' . $this->csiteKotsuSubstr($dispModel) . '</p>');
            $historyElem->append($divElem);
        }
    }

    private function getMadoriMenseki($dispModel)
    {
        $shumoku = $dispModel->csite_bukken_shumoku_cd[0];
        $result = '-';
        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
            case Services\ServiceUtils::TYPE_MANSION:
            case Services\ServiceUtils::TYPE_KODATE:
                $result =  $this->getVal('madori', $dispModel);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
            case Services\ServiceUtils::TYPE_KASI_OTHER:
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $result =  $this->getVal('tatemono_ms', $dispModel);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
                $result =  $this->getVal('tochi_ms', $dispModel);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                break;
            default:
                throw new Exception('Illegal Argument.');
                break;
        }
        return $result;
    }

    private function csiteKotsuSubstr($dispModel)
    {
        $MAX = 11;
        $THREE_POINT_READER = '…';
        $result = '';
        if($dispModel->ensen_eki_nashi_fl){
            return $dispModel->csite_shozaichi;
        }else{
            foreach($dispModel->csite_kotsus as $csite_kotsus){
                if(preg_match('/バス/',$csite_kotsus)){
                    continue;
                }
                $line = preg_replace('/ \/.*?$/','',$csite_kotsus);
                if (mb_strlen($line) > $MAX) {
                    $line = mb_substr($line, 0, $MAX-1) . $THREE_POINT_READER;
                }
                $station = preg_replace('/^.*?\/ /','',$csite_kotsus);
                $station = preg_replace('/駅/','',$station);
                $station = "「{$station}」駅";
                if (mb_strlen($station) > $MAX) {
                    $station = mb_substr($station, 0, $MAX-1) . $THREE_POINT_READER;
                }
                $result = $result.$line.'<br>'.$station.'<br>';
            }
        }
        return $result;
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    /**
     * img[src]
     * - 環境ごとにプロトコル切り替え
     *
     * @param $img_server
     * @return string
     */
    private function getImgDomain($img_server) {

        if ((boolean)$this->_config->img_can_use_https) {
            return str_replace('http:', '', $img_server);
        }

        return $img_server;
    }

}