<?php
namespace Modules\V1api\Services\Sp\Element;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Models;
use Modules\V1api\Models\SpecialSettings;

class Result
{

    protected $logger;
    protected $_config;

    public function __construct()
    {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);

        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    public function createElement($type_ct, $doc, Datas $datas, Params $params, SpecialSettings $specialSettings, $isSpecial = false, $pageInitialSettings, $searchSettings)
    {
        $bukkenList = $datas->getBukkenList();
        // 特集を取得
        $specialRow = $isSpecial ? $specialSettings->getCurrentPagesSpecialRow() : null;
        $specialSetting = $isSpecial ? $specialRow->toSettingObject() : null;

        $url_type = $isSpecial? $specialRow->filename: $type_ct;


        // 検索タイプ
        $s_type = $params->getSearchType();

        $pNames = $datas->getParamNames();
        // 種目情報の取得
        // $type_ct = $params->getTypeCt();

        // 特集複数種目対応
        if (is_array($type_ct) && count($type_ct) === 1) {
            $type_ct = $type_ct[0];
        }
        if (is_array($type_ct)) {
            $shumoku = [];
            foreach ($type_ct as $ct) {
                $type_id = Estate\TypeList::getInstance()->getTypeByUrl($ct);
                $shumoku[] = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
            }

        } else {
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $shumoku    = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
            $shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);
        }

        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();
        // 沿線の取得（複数指定の場合は使用できない）
        $ensen_ct = $params->getEnsenCt(); // 単数or複数
        $ensen_cd = $pNames->getEnsenCd();
        $ensen_nm = $pNames->getEnsenName();
        // 駅の取得（複数指定の場合は使用できない）
        $eki_ct = $params->getEkiCt(); // 単数or複数
        $eki_cd = $pNames->getEkiCd();
        $eki_nm = $pNames->getEkiName();
        // 検索タイプ：駅の場合は、駅ひとつ指定なので、駅ローマ字から沿線情報を取得
        if ($s_type == $params::SEARCH_TYPE_EKI) {
            $ekiObj = Models\EnsenEki::getObjBySingle($eki_ct);
            $ensen_ct = $ekiObj->getEnsenCt();
            $ensenObj = Services\ServiceUtils::getEnsenObjByConst($ken_cd, $ensen_ct);
            $ensen_cd = $ensenObj->code;
            $ensen_nm = $ensenObj->ensen_nm;
        }

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();
        $locate_nm = $pNames->getLocateName();
        $f_type = $params->getFType();

        // 市区郡を変更 or 沿線を変更ボタン
        call_user_func(function ($doc, $s_type, $params, $url_type, $ken_ct, $specialSetting, $f_type) {

            $text = '';
            $href = '';

            // 条件を変更するボタン
            switch ($s_type) {
                case $params::SEARCH_TYPE_LINE:
                case $params::SEARCH_TYPE_EKI:
                case $params::SEARCH_TYPE_LINEEKI_POST:
                    $text = '沿線を変更';
                    $href = "/{$url_type}/{$ken_ct}/line.html";
                    break;
                case $params::SEARCH_TYPE_CITY:
                case $params::SEARCH_TYPE_CITY_POST:
                case $params::SEARCH_TYPE_SEIREI:
                case $params::SEARCH_TYPE_PREF:
                case $params::SEARCH_TYPE_CHOSON:
                case $params::SEARCH_TYPE_CHOSON_POST:
                    $text = '市区郡を変更';
                    $href = "/{$url_type}/{$ken_ct}/";
                    break;
            	case $params::SEARCH_TYPE_DIRECT_RESULT:
					// エリア
					if ($specialSetting->area_search_filter->hasAreaSearchType()) {
	            		$text = '市区郡を変更';
					} else {
						$text = '沿線を変更';
					}
                    $href = "/{$url_type}/{$ken_ct}/";
            		break;
                case $params::SEARCH_TYPE_FREEWORD:
                    $doc['.btn-narrow-down li:eq(0)']->attr('style', "width:100%");
                    $doc['.btn-narrow-down li:eq(1)']->remove();
                    $doc['.btn-term-change li:eq(0)']->attr('style', "width:100%");
                    $doc['.btn-term-change li:eq(1)']->remove();

                    break;
            }

            if (!empty($ken_ct) && $ken_ct !== 'result') {
                $doc['.btn-narrow-down li:eq(0) a']->attr('href', "/{$url_type}/{$ken_ct}/condition/");
                $doc['.btn-term-change li:eq(0) a']->attr('href', "/{$url_type}/{$ken_ct}/condition/");
            } else {
                if (!is_array($url_type)) {
                    $doc['.btn-narrow-down li:eq(0) a']->attr('href', "/{$url_type}/condition/");
                    $doc['.btn-term-change li:eq(0) a']->attr('href', "/{$url_type}/condition/");
                }
            }
            $doc['.btn-narrow-down li:eq(1) a']
                ->text($text)
                ->attr('href', $href);
            $doc['.btn-term-change li:eq(1) a']
                ->text($text)
                ->attr('href', $href);
            if ($s_type == $params::SEARCH_TYPE_FREEWORD) {
                $doc['.search-freeword a']->attr('href', "/{$f_type}/result/");
                $doc['.btn-narrow-down li:eq(0) a']->attr('href', "/{$f_type}/condition/");
            } elseif ($s_type == $params::SEARCH_TYPE_PREF && $ken_ct == 'result') {
                $doc['.search-freeword a']->attr('href', "/{$url_type}/result/");
            } else {
                $doc['.search-freeword a']->attr('href', "/{$url_type}/{$ken_ct}/result/");
            }

        }, $doc, $s_type, $params, $url_type, $ken_ct, $specialSetting, $f_type);

        // 一括問い合わせURL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        $doc['p.btn-all-contact a']->attr('href', $inquiryURL);


        $bukkenList = $datas->getBukkenList();

        // 注意）０件の場合はここで終了
        // 検索結果ゼロのテキスト削除
        if ($bukkenList['total_count'] != 0) {
            $doc['p.tx-nohit']->remove();
        } else {
            $doc['p.total-count']->remove();
            $doc['div.list-change']->remove();
            $doc['div.article-object']->remove();
            $doc['p.btn-all-contact']->remove();
            $doc['div.article-pager']->remove();
            return;
        }

        // fdp link
        $fdp = null;
        if (is_array($type_ct)) {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
        } else {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
        }
        $fdp = json_decode($settingRow->display_fdp);

        // 物件要素の生成モジュール
        $bukkenMaker = new BukkenList();
        // 必要要素の初期化とテンプレ化
        $doc['div.article-object']->remove();

        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
            $highlight = array();
            if(isset($bukken['highlight'])) {
                $highlight = (object) array_merge($bukken['highlight'], $bukken['data_model_highlight']);
            }

            // $bukkenElemに物件APIから取得した情報を設定
            $bukkenElem = $bukkenMaker->createElement($shumoku, $dispModel, $dataModel, $params, $dispModel->niji_kokoku_jido_kokai_fl, $highlight, $fdp, $pageInitialSettings);
            $doc['p.btn-all-contact']->before($bukkenElem);
        }

        // 一括問い合わせURL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        $doc['.btn-all-contact a']->attr('href', $inquiryURL);

        /*
         * ページング処理
         */
        $page        = $bukkenList['current_page'];
        $total_page  = $bukkenList['total_pages'];
        $per_page    = $bukkenList['per_page'];
        $total_count = $bukkenList['total_count'];

        // カウント部分
        if ($total_count === 0)
        {
            $first = 0;
            $last = 0;
        } else {
            $first = $per_page * ($page - 1) + 1;
            $last  = $per_page * $page;
            if ($last > $total_count) $last = $total_count;
        }
        $countTxt = "<span class='bold'>該当件数</span>" .
            "<span class='total-num'>".number_format($total_count)."件</span>" .
            "${first}〜${last}件を表示";
        $doc['p.total-count']->empty()->append($countTxt);

        // ページング
        $base_url = '';
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $base_url = "/{$url_type}/${ken_ct}/result/${ensen_ct}-line.html";
                break;
            case $params::SEARCH_TYPE_CITY:
                $base_url = "/{$url_type}/${ken_ct}/result/${shikugun_ct}-city.html";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $base_url = "/{$url_type}/${ken_ct}/result/${locate_ct}-mcity.html";
                break;
            case $params::SEARCH_TYPE_EKI:
                $base_url = "/{$url_type}/${ken_ct}/result/${eki_ct}-eki.html";
                break;
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
                $base_url = "/{$url_type}/${ken_ct}/result/";
                break;
            case $params::SEARCH_TYPE_DIRECT_RESULT:
                $base_url = "/{$url_type}/";
                break;
		}
        $articlePagerElem      = $doc['div.article-pager ul']->empty();

        $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
        if ($page > 1) {
            $pager_prev  = "<li class='pager-prev'><a data-page=${prev_page} href='${base_url}?page=${prev_page}'>前へ</a></li>";
        } else {
            $pager_prev  = "<li class='pager-prev'><span>前へ</span></li>";
        }
        $articlePagerElem->append($pager_prev);

        $countTxt = "<li class='count-num'><span>${first}〜${last}件/${total_count}件</span></li>";
        $articlePagerElem->append($countTxt);

        $next_page = ($page == 0 || $page == $total_page) ? $total_page : $page +1;
        if ($next_page != $page) {
            $pager_next = "<li class='pager-next'><a data-page=${next_page} href='${base_url}?page=${next_page}'>次へ</a></li>";
        } else {
            $pager_next = "<li class='pager-next'><span>次へ</span></li>";
        }

        $articlePagerElem->append($pager_next);

        $sortSelectElem = $doc['p.sort-select'];
        // 並び替え
        $sort = $params->getSort();
        $sortElem = $sortSelectElem['select:last']->empty();
        $sortType = '';
        switch($s_type){
            case Params::SEARCH_TYPE_LINE:
            case Params::SEARCH_TYPE_EKI:
            case Params::SEARCH_TYPE_LINEEKI_POST:
                $sortType = 'ensen_eki,';
                $sorts_ensen_eki = 'ensen_eki,kakaku,eki_kyori';
                $sorts_shozaichi = 'shozaichi_kana,kakaku,ensen_eki,eki_kyori';
                $sorts_kodate_ensen_eki = 'joi_shumoku:desc,ensen_eki,kakaku,eki_kyori';
                $sorts_kodate_shozaichi = 'joi_shumoku:desc,shozaichi_kana,kakaku,ensen_eki,eki_kyori';
                break;

            case Params::SEARCH_TYPE_CITY:
            case Params::SEARCH_TYPE_SEIREI:
            case Params::SEARCH_TYPE_PREF:
            case Params::SEARCH_TYPE_CITY_POST:
            case Params::SEARCH_TYPE_CHOSON:
            case Params::SEARCH_TYPE_CHOSON_POST:
            case Params::SEARCH_TYPE_MAP:
                $sortType = 'shozaichi_kana,';
                $sorts_ensen_eki = 'ensen_eki,kakaku,shozaichi_kana,eki_kyori';
                $sorts_shozaichi = 'shozaichi_kana,kakaku,eki_kyori';
                $sorts_kodate_ensen_eki = 'joi_shumoku:desc,ensen_eki,shozaichi_kana,kakaku,eki_kyori';
                $sorts_kodate_shozaichi = 'joi_shumoku:desc,shozaichi_kana,kakaku,eki_kyori';
                break;
            case Params::SEARCH_TYPE_FREEWORD:
                $sorts_ensen_eki = 'ensen_eki,kakaku,eki_kyori';
                $sorts_shozaichi = 'shozaichi_kana,kakaku,eki_kyori';
                $sorts_kodate_ensen_eki = 'joi_shumoku:desc,ensen_eki,kakaku,eki_kyori';
                $sorts_kodate_shozaichi = 'joi_shumoku:desc,shozaichi_kana,kakaku,eki_kyori';
                break;
        }

        $sorts_kakaku = 'kakaku,'.$sortType.'eki_kyori';
        $sorts_kakaku_desc = 'kakaku:desc,'.$sortType.'eki_kyori';
        $sorts_eki_kyori = 'eki_kyori,kakaku,'.$sortType;
        $sorts_madori_index = 'madori_index,kakaku,'.$sortType.'eki_kyori';
        $sorts_tatemono_ms = 'senyumenseki:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_tochi_ms = 'tochi_ms:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_chikunengetsu = 'chikunengetsu:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_shinchaku = 'b_muke_c_muke_er_nomi_kokai_date:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_shumoku = 'bukken_shumoku,kakaku,ensen_eki,'.$sortType.'eki_kyori';

        $sorts_kodate_kakaku = 'joi_shumoku:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_kodate_kakaku_desc = 'joi_shumoku:desc,kakaku:desc,'.$sortType.'eki_kyori';
        $sorts_kodate_eki_kyori = 'joi_shumoku:desc,eki_kyori,kakaku,'.$sortType;
        $sorts_kodate_madori_index = 'joi_shumoku:desc,madori_index,kakaku,'.$sortType.'eki_kyori';
        $sorts_kodate_tatemono_ms = 'joi_shumoku:desc,senyumenseki:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_kodate_tochi_ms = 'joi_shumoku:desc,tochi_ms:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_kodate_chikunengetsu = 'joi_shumoku:desc,chikunengetsu:desc,kakaku,'.$sortType.'eki_kyori';
        $sorts_kodate_shinchaku = 'joi_shumoku:desc,b_muke_c_muke_er_nomi_kokai_date:desc,kakaku,'.$sortType.'eki_kyori';


        if (is_array($type_ct)) {
            /**
             * 特集一括種目の場合
             */

            // 価格 or 賃料
            $kakakuLabel = Services\ServiceUtils::isChintai(
                Estate\TypeList::getInstance()->getShumokuCode(
                    Estate\TypeList::getInstance()->getTypeByUrl($type_ct[0])
                )
            ) ? '賃料' : '価格';

            $sortElem->append("<option value='". $sorts_kakaku. "'>{$kakakuLabel}が安い順</option>");
            $sortElem->append("<option value='". $sorts_kakaku_desc."'>{$kakakuLabel}が高い順</option>");
            $sortElem->append("<option value='". $sorts_ensen_eki."'>駅順</option>");
            $sortElem->append("<option value='". $sorts_shozaichi."'>住所順</option>");
            $sortElem->append("<option value='". $sorts_eki_kyori."'>駅から近い順</option>");
            $sortElem->append("<option value='". $sorts_chikunengetsu."'>築年月が浅い順</option>");
            $sortElem->append("<option value='". $sorts_shinchaku."'>新着順</option>");

            if($kakakuLabel == '価格'){
                $sortElem->append("<option value='". $sorts_shumoku."'>物件種目順</option>");
            }

            if ($sortElem["option[value='${sort}']"]->size() == 0) {
                $sortElem["option[value='']"]->attr('selected', 'selected');
            } else {
                $sortElem["option[value='${sort}']"]->attr('selected', 'selected');
            }
        }
        elseif (Services\ServiceUtils::isChintai($shumoku))
        {
            /*
             * 賃貸の並び順
             */
            $sortElem->append("<option value='". $sorts_kakaku. "'>賃料が安い順</option>");
            $sortElem->append("<option value='". $sorts_kakaku_desc."'>賃料が高い順</option>");
            $sortElem->append("<option value='". $sorts_ensen_eki."'>駅順</option>");
            $sortElem->append("<option value='". $sorts_shozaichi."'>住所順</option>");
            $sortElem->append("<option value='". $sorts_eki_kyori."'>駅から近い順</option>");
            if ($shumoku == Services\ServiceUtils::TYPE_CHINTAI) {
                $sortElem->append("<option value='". $sorts_madori_index. "'>間取り順</option>");
            }
            if ($shumoku == Services\ServiceUtils::TYPE_KASI_TOCHI){
                $sortElem->append("<option value='". $sorts_tochi_ms."'>面積が広い順</option>");
            }
            if ($shumoku != Services\ServiceUtils::TYPE_PARKING &&
                    $shumoku != Services\ServiceUtils::TYPE_KASI_TOCHI) {
                $sortElem->append("<option value='". $sorts_tatemono_ms."'>面積が広い順</option>");
                $sortElem->append("<option value='". $sorts_chikunengetsu."'>築年月が浅い順</option>");
            }
            $sortElem->append("<option value='". $sorts_shinchaku."'>新着順</option>");
            if ($sortElem["option[value='${sort}']"]->size() == 0) {
            	$sortElem["option[value='']"]->attr('selected', 'selected');
            } else {
            	$sortElem["option[value='${sort}']"]->attr('selected', 'selected');
            }
        } else {
            /*
             * 売買の並び順
             */
            if($shumoku == Services\ServiceUtils::TYPE_KODATE){
                $sortElem->append("<option value='". $sorts_kodate_kakaku. "'>価格が安い順</option>");
                $sortElem->append("<option value='". $sorts_kodate_kakaku_desc."'>価格が高い順</option>");
                $sortElem->append("<option value='". $sorts_kodate_ensen_eki."'>駅順</option>");
                $sortElem->append("<option value='". $sorts_kodate_shozaichi."'>住所順</option>");
                $sortElem->append("<option value='". $sorts_kodate_eki_kyori."'>駅から近い順</option>");

                $sortElem->append("<option value='". $sorts_kodate_madori_index."'>間取り順</option>");
                $sortElem->append("<option value='". $sorts_kodate_tatemono_ms."'>建物面積が広い順</option>");
                $sortElem->append("<option value='". $sorts_kodate_tochi_ms."'>土地面積が広い順</option>");
                $sortElem->append("<option value='". $sorts_kodate_chikunengetsu."'>築年月が浅い順</option>");
                $sortElem->append("<option value='". $sorts_kodate_shinchaku."'>新着順</option>");
            }else{
                $sortElem->append("<option value='". $sorts_kakaku. "'>価格が安い順</option>");
                $sortElem->append("<option value='". $sorts_kakaku_desc."'>価格が高い順</option>");
                $sortElem->append("<option value='". $sorts_ensen_eki."'>駅順</option>");
                $sortElem->append("<option value='". $sorts_shozaichi."'>住所順</option>");
                $sortElem->append("<option value='". $sorts_eki_kyori."'>駅から近い順</option>");

                if($shumoku == Services\ServiceUtils::TYPE_MANSION){
                    $sortElem->append("<option value='". $sorts_madori_index."'>間取り順</option>");
                }
                if($shumoku == Services\ServiceUtils::TYPE_URI_TOCHI){
                    $sortElem->append("<option value='". $sorts_tochi_ms."'>面積が広い順</option>");
                }else{
                    $sortElem->append("<option value='". $sorts_tatemono_ms."'>建物面積が広い順</option>");
                    if($shumoku == Services\ServiceUtils::TYPE_MANSION){
                        $sortElem->append("<option value='". $sorts_chikunengetsu."'>完成時期（築年数）順</option>");
                    }else{
                        $sortElem->append("<option value='". $sorts_chikunengetsu."'>築年月が浅い順</option>");
                    }
                }
                $sortElem->append("<option value='". $sorts_shinchaku."'>新着順</option>");
            }
            if ($sortElem["option[value='${sort}']"]->size() == 0) {
            	$sortElem["option[value='']"]->attr('selected', 'selected');
            } else {
            	$sortElem["option[value='${sort}']"]->attr('selected', 'selected');
            }
        }
	}

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }
}