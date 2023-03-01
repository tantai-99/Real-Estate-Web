<?php
namespace Modules\V1api\Services\Sp\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\SpecialSettings;
use Library\Custom\Model\Top\TagTopOriginal;
use Library\Custom\Model\Lists\TagOriginal;

class KomaTop
{

    public function __construct()
    {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);
        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }
    
	public function createKoma($komaList, $params, SpecialSettings $specialSettings, $pageInitialSettings)
	{
        // 表示行数
        $row = $params->getKomaRows();

        //columns
        $columns = $params->getKomaColumns();
        
        $total = $komaList['total_count'];

        $komaResult = array();
        for ($i=0; $i < $row * $columns && $i < $total; $i++)
        {
            $bukken = $komaList['bukkens'][$i];            
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
            $komaResult[] = array(
                TagTopOriginal::TAG_PROPERTY_TYPE                 => Services\ServiceUtils::getShumokuDispModel($dispModel),
                TagTopOriginal::TAG_PROPERTY_IMAGE1               => $this->getImage($dataModel, TagTopOriginal::TAG_PROPERTY_IMAGE1),
                TagTopOriginal::TAG_PROPERTY_IMAGE2               => $this->getImage($dataModel, TagTopOriginal::TAG_PROPERTY_IMAGE2),
                TagTopOriginal::TAG_PROPERTY_IMAGE3               => $this->getImage($dataModel, TagTopOriginal::TAG_PROPERTY_IMAGE3),
                TagTopOriginal::TAG_PROPERTY_IMAGE4               => $this->getImage($dataModel, TagTopOriginal::TAG_PROPERTY_IMAGE4),
                TagTopOriginal::TAG_PROPERTY_TRAFFIC              => $this->getKotsusValue($dispModel->csite_kotsus[0]).' '.$this->getVal('toho', $dispModel),
                TagTopOriginal::TAG_PROPERTY_LOCATION             => $this->getVal('csite_shozaichi', $dispModel),
                TagTopOriginal::TAG_PROPERTY_STATIONWALKING       => $this->getVal('toho', $dispModel),
                TagTopOriginal::TAG_PROPERTY_RENT                 => str_replace('万円','<span>万円</span>', $this->getVal('csite_kakaku', $dispModel)),
                TagTopOriginal::TAG_PROPERTY_PRICE1               => $this->getVal('csite_kakaku', $dispModel),
                TagTopOriginal::TAG_PROPERTY_PRICE2               => str_replace('万円','', $this->getVal('csite_kakaku', $dispModel)),
                TagTopOriginal::TAG_PROPERTY_PRICE3               => number_format(((double)str_replace(array(',', '万円'),'', $this->getVal('csite_kakaku', $dispModel)))*TagOriginal::CONVERT_MAN_TO_INT),
                //TagTopOriginal::TAG_PROPERTY_CONSTRUCTION         => $this->getVal('tatemono_kozo', $dispModel),
                TagTopOriginal::TAG_PROPERTY_FLOORPLAN            => $this->getVal('madori', $dispModel),
                TagTopOriginal::TAG_PROPERTY_BUILDINGAREA         => $this->getVal('tatemono_ms', $dispModel),
                TagTopOriginal::TAG_PROPERTY_HIERARCHY            => $this->getVal('csite_kaidate_kai', $dispModel),
                TagTopOriginal::TAG_PROPERTY_SERCURITYDEPOSIT     => $this->getVal('shikikin', $dispModel),
                TagTopOriginal::TAG_PROPERTY_DEPOSIT              => $this->getVal('hoshokin', $dispModel),
                TagTopOriginal::TAG_PROPERTY_ADMINISTRATIONFEE    => $this->getVal('kanrihito', $dispModel),
                TagTopOriginal::TAG_PROPERTY_USEDPARTIALAREA      => $this->getVal('tatemono_ms', $dispModel),
                TagTopOriginal::TAG_PROPERTY_LANDAREA             => $this->getVal('tochi_ms', $dispModel),
                TagTopOriginal::TAG_PROPERTY_KEYMONEY             => $this->getVal('reikin', $dispModel),
                TagTopOriginal::TAG_PROPERTY_TSUBAUNITPRICE       => $this->getVal('tsubo_tanka', $dispModel),
                TagTopOriginal::TAG_PROPERTY_USAGEAREA            => $this->getYotoChiiki($dataModel),
                TagTopOriginal::TAG_PROPERTY_BASISNUMBER          => $this->getVal('tochi_tsubo_su', $dispModel),
                TagTopOriginal::TAG_PROPERTY_BUILDINGSTRUCTURE    => $this->getVal('tatemono_kozo', $dispModel),
                TagTopOriginal::TAG_PROPERTY_CONSTRUCTIONDATE     => $this->getVal('csite_chikunengetsu', $dispModel),
                TagTopOriginal::TAG_PROPERTY_NAME                 => Services\ServiceUtils::replaceSsiteBukkenTitle($this->getVal('csite_bukken_title', $dispModel)),
                TagTopOriginal::TAG_PROPERTY_REALESTATEURL        => $this->getRealEstateUrl($dispModel, $dataModel, $pageInitialSettings->searchSetting),
                TagTopOriginal::TAG_PROPERTY_NEW                  => $this->getNew($dispModel),
                TagTopOriginal::TAG_PROPERTY_COMMENT              => $this->getProComment($dataModel, $dispModel),
                TagTopOriginal::TAG_PROPERTY_WAYSIDE              => $this->getVal('ensen_nm', $dispModel),
                TagTopOriginal::TAG_PROPERTY_STATION              => $this->getVal('eki_nm', $dispModel).'駅',
                TagTopOriginal::TAG_PROPERTY_IMAGE_KOMA           => $this->getImageKoma($dispModel),

            );
        }
        return json_encode($komaResult);
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
    
    protected function getProComment($dataModel, $dispModel) {
        $procomment = $this->getVal('staff_comment', $dispModel, true);
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
            $procomment = '';
        }
        // $result = '';
        // if ( $procomment) {
        //     $result  = '<dl class="pro-comment">';
        //     $result .= '<dt>おすすめコメント</dt>';
        //     $result .= '<dd>'.$procomment.'</dd>';
        //     $result .= '</dl>';
        // }
        return $procomment;
    }

    protected function getKotsus($dispModel) {
        $kotsus = $this->getVal('csite_kotsus', $dispModel);
        $kotsusTxt = (isset($kotsus[0]) ? $kotsus[0] : '')
            . (isset($kotsus[1]) ? ' ' . $kotsus[1] : '');
        return $kotsusTxt;
    }

    protected function getImage($dataModel, $image) {
        $img_server = $this->getImgDomain($this->_config->img_server);
        if (isset($dataModel->images) && count($dataModel->images) > 0) {
            $images = $dataModel->images;
            $image_no = (int) str_replace('image', '', $image);
            foreach ($images as $elem) {
                $elem = (object) $elem;
                if ($elem->serial_no == $image_no) {
                    return $img_server.$elem->url;
                }
            }
        }
        return $img_server."/image_files/path/no_image";
    }


    protected function getRealEstateUrl($dispModel, $dataModel, $searchSetting) {
        // ATHOME_HP_DEV-4841 : 詳細URLの生成方法を変更 -
        return Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $searchSetting);
    }

    protected function getNew($dispModel) {
        $new = '';
        if ($dispModel->new_mark_fl_for_c) {
            $new = '<span>NEW</span>';
        }
        return $new;
    }

    /**
     * 用途地域 yoto_chiiki_nm
     * @param $dataModel
     */
    private function getYotoChiiki($dataModel)
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

    protected function getImageKoma($dispModel) {
        $img_server = $this->getImgDomain($this->_config->img_server);
        if (isset($dispModel->csite_image_for_koma)) {
            $image = $dispModel->csite_image_for_koma;
            return $img_server.$image['url'];
        }
        return $img_server."/image_files/path/no_image";
    }
}