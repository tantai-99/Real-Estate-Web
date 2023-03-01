<?php
namespace Modules\V1api\Services\Cms\Element;

use Modules\V1api\Models;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;

class HouseList
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;

    private $search_settings;

    public function __construct()
    {
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');

        $this->search_settings = [];
    }

    /**
     * 物件一覧の１物件の要素を作成して返します。
     *
     * @param $shumoku 物件種目のコード
     * @param $dispModel 物件APIの表示モデル
     * @param $dataModel 物件APIのデータモデル
     * @return 物件一覧の１物件の要素
     */
    public function createElement($dispModel, $dataModel, Models\Params $params, $codeList, $settingRow, $pageInitialSettings)
    {
        // ATHOME_HP_DEV-5001
        if(isset($settingRow)) {
            if(empty($this->search_settings)) {
                // 公開中の種別-種目リスト(enabled_estate_type)を物件APIの種目コード(ex 貸店舗・事務所 -> 5009)に変換し、プロパティ格納
                $shumokuList = explode(",", $settingRow->enabled_estate_type);
                foreach($shumokuList as $shumoku_cd) {
                    $this->search_settings[] = Estate\TypeList::getShumokuCode(trim($shumoku_cd));
                }
            }
        }
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/houselist/houselist.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);
        $eleHouse = $doc['span input[name="houses_id[]"]'];
        if ($params->getLinkPage()) {
            $eleHouse->attr('type', 'radio');
            $value = $this->getVal('id', $dispModel);
        }else {
            $eleHouse->attr('type', 'checkbox');
            $value = $dispModel->id.':'.$dispModel->ken_cd;
        }
        $eleHouse->val($value);
        $doc['th.cell1 span:eq(0)']->text($this->getVal('kanri_no', $dispModel));
        $doc['th.cell1 span:eq(1)']->text($this->getVal('bukken_no', $dispModel));
        $doc['th.cell2 span:eq(0)']->text(Services\ServiceUtils::getShumokuDispModel($dispModel));
        $doc['th.cell2 span:eq(1)']->text($this->getVal('csite_kakaku', $dispModel));
        $doc['th.cell2 span:eq(2)']->text($this->getPublishDate($dispModel->kokaichu_kokais));
        $doc['th.cell3 span:eq(0)']->text($this->getPropertyName($dataModel, $dispModel));
        $doc['th.cell3 span:eq(1)']->text($this->getVal('csite_shozaichi', $dispModel));
        $doc['th.cell3 span:eq(2)']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        $doc['th.cell4 span:eq(0)']->text($this->getVal('image_cnt', $dispModel));
        $doc['th.cell4 span:eq(1)']->text($this->getImageMadori($dispModel, $codeList));
        $doc['th.cell5 span:eq(0)']->text($this->getCategory($dataModel, $dispModel));
        $doc['th.cell5 span:eq(1)']->text($this->getProComment($dataModel, $dispModel));

        /**
         ATHOME_HP_DEV-5001
         - リンク表示条件にdisplayModel::csite_bukken_shumoku_cd と、公開中種目の比較追加
         - 詳細URLの第一階層パスをCMSではなく、公開中の種目から生成
         */
        if ($settingRow
         && count( array_intersect($this->search_settings, $this->getVal('csite_bukken_shumoku_cd', $dispModel)) )
         && in_array($dispModel->ken_cd, json_decode($settingRow->area_search_filter)->area_1)) {
            $detailUrl = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $this->search_settings);
            $domain = 'https://www.'.$pageInitialSettings->getCompany()->domain.$detailUrl;

            // ATHOME_HP_DEV-5001
            $doc['th.cell5 span:eq(2) a']->attr('href', "#")->attr('onclick', "$(this).closest('span').find('form').eq(0).submit();return false;");
            $detailFormElem = pq('<form/>')->attr('method', 'post')->attr('action', $domain)->attr('target', '_blank');
            $detailFormElem->append(pq('<input/>')->attr('type', 'hidden')->attr('name', 'from-cms')->attr('value', '1'));
            $doc['th.cell5 span:eq(2)']->append($detailFormElem);
        } else {
            $doc['th.cell5 span:eq(2) a']->addClass('is-disable');
        }
        return $doc;
    }

    protected function getCategory($dataModel, $dispModel) {
        $category = array(
            0 => '物件区分：無',
            1 => '物件区分：自社',
            2 => '物件区分：他社',
        );
        $value = 0;
        if ($dispModel->niji_kokoku_jido_kokai_fl == true || (isset($dataModel->jishatasha_cd) && $dataModel->jishatasha_cd == 2)) {
            $value = 2;
        } else if (isset($dataModel->jishatasha_cd) && $dataModel->jishatasha_cd != 2) {
            $value = $dataModel->jishatasha_cd;
        }
        return $category[$value];
    }

    protected function getProComment($dataModel, $dispModel) {
        $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        if (is_null($procomment) || $dispModel->niji_kokoku_jido_kokai_fl) {
            $procomment = 'おすすめコメント：無';
        }
        return $procomment;
    }

    protected function getImageMadori($dispModel, $codeList) {
        return '間取り図：'.(isset($dispModel->csite_image_madori) ? '有' : '無');
    }

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
    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    protected function getPublishDate($kokaichuKokais) {
        if (empty($kokaichuKokais)) {
            return '';
        }
        $dates = array();
        foreach($kokaichuKokais as $kokais) {
            if (in_array($kokais['kokaisaki_cd'], array('B200', 'B100', 'E200'))) {
                $dates[] = $kokais['shokai_kokai_date'];
            }
        }
        return min($dates);
    }

    protected function getPropertyName($dataModel, $dispModel) {
        if (!$dispModel->niji_kokoku_jido_kokai_fl) {
            return (isset($dispModel->tatemono_nm) ? $dispModel->tatemono_nm : '-');
        }
        $values = [];
        if (isset($dataModel->kaiin_muke_tatemono_hihyoji_fl) && !$dataModel->kaiin_muke_tatemono_hihyoji_fl) {
            $values[] = $dataModel->tatemono_nm;
        }
        switch ($dispModel->joi_shumoku_cd) {
            case '01':    // 売土地
                if (!$dataModel->kai_kukaku_no_hihyoji_fl) {
                    $values[] = '区画番号：'.(isset($dataModel->kukaku_no) ? $dataModel->kukaku_no : '-');
                }
                break;
            case '02':    // 売戸建
                if (!$dataModel->kai_goto_no_hihyoji_fl) {
                    $values[] = '号棟番号：'.(isset($dataModel->goto_no) ? $dataModel->goto_no : '-');
                }
                break;
            default:
                if (isset($dataModel->kaiin_muke_heya_no_hihyoji_fl) && !$dataModel->kaiin_muke_heya_no_hihyoji_fl) {
                    $values[] = '部屋番号：'.(isset($dataModel->heya_no) ? $dataModel->heya_no : '-');
                }
                break;
        }

        if (empty($values)) {
            return '-';
        } else {
            if (count($values) == 1) {
                return $values[0];
            } else {
                return implode(' ', $values);
            }
        }
    }
}