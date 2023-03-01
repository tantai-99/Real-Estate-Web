<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Models;

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

    public function createElement(
        $type_ct,
        $doc,
        Datas $datas,
        Params $params,
        Models\SpecialSettings $specialSettings,
        $isSpecial = false,
        Models\PageInitialSettings $pageInitialSettings,
        Models\SearchCondSettings $searchSettings
    ) {
    	$bukkenList = $datas->getBukkenList();
        // 特集を取得
        $specialRow = $isSpecial ? $specialSettings->getCurrentPagesSpecialRow() : null;
        $specialSetting = $isSpecial ? $specialRow->toSettingObject() : null;
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

        $choson_ct = $params->getChosonCt();


        // アサイド検索条件処理
        // 物件検索側では絞り込み条件の設定は無い
        $asidetElem = $doc['div.articlelist-side.contents-left'];

        // 都道府県名
        $asidetElem['.area']->text($ken_nm);

        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
            case $params::SEARCH_TYPE_EKI:
            case $params::SEARCH_TYPE_LINEEKI_POST:
                $asidetElem['section.articlelist-side-section.change-area:last']->remove();

                $ensenTxt = '';
                if ($s_type == $params::SEARCH_TYPE_EKI) {
                    $ensenTxt = $ensen_nm;
                } else if ($s_type == $params::SEARCH_TYPE_LINE) {
                    $ensenTxt = $ensen_nm;
                } else {
                    // 駅ローマ字から沿線情報を取得
                    // 駅ローマ字より沿線コードを取得する
                    $ensenCtList = array();
                    foreach ((array) $eki_ct as $eki) {
                        $ekiObj = Models\EnsenEki::getObjBySingle($eki);
                        array_push($ensenCtList, $ekiObj->getEnsenCt());
                    }
                    $ensenObjList = Services\ServiceUtils::getEnsenListByConsts($ken_cd, $ensenCtList);
                    $i = 1;
                    foreach ($ensenObjList as $elem)
                    {
                        $ensenObj = (object) $elem;
                        switch ($i)
                        {
                            case 1:
                                $ensenTxt = $ensenObj->ensen_nm;
                                break;
                            case 2:
                                $ensenTxt = $ensenTxt . "・" . $ensenObj->ensen_nm;
                                break;
                            case 3:
                                $cnt = count((array)$ensenObjList) -2;
                                $ensenTxt = $ensenTxt . "（他${cnt}路線）";
                                break;
                        }
                        $i++;
                        if ($i >= 4) break;
                    }
                }

                $i = 1;
                $ekiTxt = '';
                if (! is_null($params->getEkiCt()))
                {
                    $ekiObjList = Services\ServiceUtils::getEkiListByConsts($params->getEkiCt());
                    foreach ($ekiObjList as $elem)
                    {
                        $ekiObj = (object) $elem;
                        switch ($i)
                        {
                            case 1:
                                $ekiTxt = $ekiObj->eki_nm . '駅';
                                break;
                            case 2:
                                $ekiTxt = $ekiTxt . "・" . $ekiObj->eki_nm . '駅';
                                break;
                            case 3:
                                $cnt = count($params->getEkiCt()) -2;
                                $ekiTxt = $ekiTxt . "（他${cnt}駅）";
                                break;
                        }
                        $i++;
                        if ($i >= 4) break;
                    }
                }

                $asidetElem['section.articlelist-side-section.change-area p.area-detail']->text('')->append($ensenTxt . '<br>' . $ekiTxt);
                break;
            case $params::SEARCH_TYPE_CITY:
            case $params::SEARCH_TYPE_SEIREI:
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_MAP:
                $asidetElem['section.articlelist-side-section.change-area:first']->remove();
                $asidetElem['section.articlelist-side-section.change-area p.area-detail']->empty();
                // 市区郡名はJSで書き込むように変更
                /*
                $shikugunObjList = Services\ServiceUtils::getShikugunListByConsts($ken_cd, $params->getShikugunCt());
                $i = 1;
                $shikugunTxt = '';
                foreach ($shikugunObjList as $elem)
                {
                    $shikugunObj = (object) $elem;
                    switch ($i)
                    {
                        case 1:
                            $shikugunTxt = $shikugunObj->shikugun_nm;
                            break;
                        case 2:
                            $shikugunTxt = $shikugunTxt . "・" . $shikugunObj->shikugun_nm;
                            break;
                        case 3:
                            $cnt = count($params->getShikugunCt()) -2;
                            $shikugunTxt = $cnt > 1 ? $shikugunTxt . "（他${cnt}地域）" : '';
                            break;
                    }
                    $i++;
                    if ($i >= 4) break;
                }
                $asidetElem['section.articlelist-side-section.change-area p.area-detail']->text($shikugunTxt);
                */
                break;
            case $params::SEARCH_TYPE_CHOSON:
            case $params::SEARCH_TYPE_CHOSON_POST:
                // 沿線から探すを削除
                $asidetElem['section.articlelist-side-section.change-area:first']->remove();

                $chosonTxts = [];
                $count = 0;
                if(!empty($pNames->getChosonShikuguns())) {
                    foreach ($pNames->getChosonShikuguns() as $shikugunObj) {
                        $count += count($shikugunObj->chosons);
                        foreach ($shikugunObj->chosons as $choson) {
                            if (count($chosonTxts) == 2) {
                                break;
                            }
                            $chosonTxts[] = "{$shikugunObj->shikugun_nm}{$choson->choson_nm}";
                        }
                    }
                }

                $asidetElem['section.articlelist-side-section.change-area p.area-detail']
                    ->text(implode('・', $chosonTxts) . ($count > 2 ? "（他".($count - 2)."地域）" : ""));
                break;
            case $params::SEARCH_TYPE_DIRECT_RESULT:
            	// 直接一覧の場合
                if ($specialSetting && !$specialSetting->area_search_filter->has_search_page) {
                	$asidetElem['section.change-area']->remove();
                    // $asidetElem['.link-more-term']->remove();
                }
                break;
            case $params::SEARCH_TYPE_FREEWORD:
                $asidetElem['section.change-area']->remove();
                break;
            default:
            	throw new \Exception('Illegal parameter value. s_type=' . $s_type);
		}

        // 直接一覧（ajaxでの更新）
        if ($params->getDirectAccess()){
            $asidetElem['section.change-area']->remove();
            // $asidetElem['.link-more-term']->remove();
        }

        // fdp link
        $fdp = null;
        if (is_array($type_ct)) {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
        } else {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
        }
        $fdp = json_decode($settingRow->display_fdp);

        // 注意）０件の場合はここで終了
        // 検索結果ゼロのテキスト削除
        if ($bukkenList['total_count'] != 0) {
            $doc['p.tx-nohit']->remove();
        } else {
            $doc['div.contents-right section div.element-tx']->remove();
            $doc['div.contents-right section div.count-wrap']->remove();
            $doc['div.contents-right section dl.sort-select']->remove();
            $doc['div.contents-right section div.collect-processing']->remove();
            $doc['div.contents-right section div.sort-table']->remove();
            $doc['div.contents-right section div.article-object-wrapper']->remove();
            return;
        }

        // 物件要素の生成モジュール
        $bukkenMaker = new BukkenList();
        // 必要要素の初期化とテンプレ化
        $wrapperElem = $doc['div.article-object-wrapper']->empty();
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
            $wrapperElem->append($bukkenElem);
        }
        // アサイドのアイコン説明の制御
        $bukkenMaker::renderAsideIcon($doc, $shumoku);

        // 一括問い合わせURL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);
        $doc['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        // sort-table処理
        $sortMaker = new SortTable();
        $sortElem = $sortMaker->createElement($shumoku, $params->getSort(), $params->isPicBukken());
        $doc['div.sort-table']->replaceWith($sortElem);

        /*
         * ページング処理
         */
        $page        = $bukkenList['current_page'];
        $total_page  = $bukkenList['total_pages'];
        $per_page    = $bukkenList['per_page'];
        $total_count = $bukkenList['total_count'];

        $pagingElem = $doc['div.count-wrap'];

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

        $countTxt = "<span>".number_format($total_count)."</span>件中 ${first}〜${last}件を表示";
        $pagingElem['p.total-count']->empty()->append($countTxt);

        // ページング
        $base_url = '';
        $base_file = $isSpecial ? $specialRow->filename : $type_ct;
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_LINE:
                $base_url = "/${base_file}/${ken_ct}/result/${ensen_ct}-line.html";
                break;
            case $params::SEARCH_TYPE_CITY:
                $base_url = "/${base_file}/${ken_ct}/result/${shikugun_ct}-city.html";
                break;
            case $params::SEARCH_TYPE_SEIREI:
                $base_url = "/${base_file}/${ken_ct}/result/${locate_ct}-mcity.html";
                break;
            case $params::SEARCH_TYPE_CHOSON:
                $base_url = "/${base_file}/${ken_ct}/result/{$choson_ct[0]}.html";
                break;
            case $params::SEARCH_TYPE_EKI:
                $base_url = "/${base_file}/${ken_ct}/result/${eki_ct}-eki.html";
                break;
            case $params::SEARCH_TYPE_PREF:
            case $params::SEARCH_TYPE_CITY_POST:
            case $params::SEARCH_TYPE_LINEEKI_POST:
            case $params::SEARCH_TYPE_CHOSON_POST:
                $base_url = "/${base_file}/${ken_ct}/result/";
                break;
            case $params::SEARCH_TYPE_DIRECT_RESULT:
                $base_url = "/${base_file}/";
                break;
            case $params::SEARCH_TYPE_MAP:
                $base_url = "/${base_file}/${ken_ct}/result/${shikugun_ct}-map.html";
                break;
		}
        $articlePagerElem      = $pagingElem['ul.article-pager:first']->empty();
        $articlePagerLowerElem = $pagingElem['ul.article-pager:last']->empty();
        switch ($s_type) {
            case $params::SEARCH_TYPE_MAP:
                $pager_first = '<li class="pager-first"><a data-page="1" href="#">最初へ</a></li>';
                $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
                $pager_prev  = '<li class="pager-prev"><a data-page="'. $prev_page .'" href="#">前へ</a></li>';

                $articlePagerElem->append($pager_first);
                $articlePagerElem->append($pager_prev);
                $articlePagerLowerElem->append($pager_first);
                $articlePagerLowerElem->append($pager_prev);
                // 上部ページャー　最大５
                if ($page <= 3) {
                    $i = 1;
                } else  if (($total_page - $page) <= 2) {
                    $i = $total_page -4;
                } else {
                    $i = $page -2;
                }
                if ($i <= 0) {
                    $i = 1;
                }
                $lastPage = $i +4;
                for ($i; $i <= $total_page && $i <= $lastPage; $i++)
                {
                    if ($i === $page) {
                        $pager = "<li><span>${i}</span></li>";
                    } else {
                        $pager = "<li><a data-page='${i}' href='#'>${i}</a></li>";
                    }
                    $articlePagerElem->append($pager);
                }

                // 下部ページャー　最大８
                if ($page <= 4) {
                    $i = 1;
                } else  if (($total_page - $page) <= 3) {
                    $i = $total_page -7;
                } else {
                    $i = $page -3;
                }
                if ($i <= 0) {
                    $i = 1;
                }
                $lastPage = $i +7;
                for ($i; $i <= $total_page && $i <= $lastPage; $i++)
                {
                    if ($i === $page) {
                        $pager = "<li><span>${i}</span></li>";
                    } else {
                        $pager = "<li><a data-page='${i}' href='#'>${i}</a></li>";
                    }
                    $articlePagerLowerElem->append($pager);
                }

                $next_page = ($page == 0 || $page == $total_page) ? $total_page : $page +1;
                $pager_next = "<li class='pager-next'><a data-page='${next_page}' href='#'>次へ</a></li>";
                $pager_last = "<li class='pager-last'><a data-page='${total_page}' href='#'>最後へ</a></li>";
                $articlePagerElem->append($pager_next);
                $articlePagerElem->append($pager_last);
                $articlePagerLowerElem->append($pager_next);
                $articlePagerLowerElem->append($pager_last);
                break;
            default:
                $pager_first = "<li class='pager-first'><a href='${base_url}'>最初へ</a></li>";
                $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
                $pager_prev  = "<li class='pager-prev'><a href='${base_url}?page=${prev_page}'>前へ</a></li>";

                $articlePagerElem->append($pager_first);
                $articlePagerElem->append($pager_prev);
                $articlePagerLowerElem->append($pager_first);
                $articlePagerLowerElem->append($pager_prev);
                // 上部ページャー　最大５
                if ($page <= 3) {
                    $i = 1;
                } else  if (($total_page - $page) <= 2) {
                    $i = $total_page -4;
                } else {
                    $i = $page -2;
                }
                if ($i <= 0) {
                    $i = 1;
                }
                $lastPage = $i +4;
                for ($i; $i <= $total_page && $i <= $lastPage; $i++)
                {
                    if ($i === $page) {
                        $pager = "<li><span>${i}</span></li>";
                    } else {
                        $pager = "<li><a href='${base_url}?page=${i}'>${i}</a></li>";
                    }
                    $articlePagerElem->append($pager);
                }

                // 下部ページャー　最大８
                if ($page <= 4) {
                    $i = 1;
                } else  if (($total_page - $page) <= 3) {
                    $i = $total_page -7;
                } else {
                    $i = $page -3;
                }
                if ($i <= 0) {
                    $i = 1;
                }
                $lastPage = $i +7;
                for ($i; $i <= $total_page && $i <= $lastPage; $i++)
                {
                    if ($i === $page) {
                        $pager = "<li><span>${i}</span></li>";
                    } else {
                        $pager = "<li><a href='${base_url}?page=${i}'>${i}</a></li>";
                    }
                    $articlePagerLowerElem->append($pager);
                }

                $next_page = ($page == 0 || $page == $total_page) ? $total_page : $page +1;
                $pager_next = "<li class='pager-next'><a href='${base_url}?page=${next_page}'>次へ</a></li>";
                $pager_last = "<li class='pager-last'><a href='${base_url}?page=${total_page}'>最後へ</a></li>";
                $articlePagerElem->append($pager_next);
                $articlePagerElem->append($pager_last);
                $articlePagerLowerElem->append($pager_next);
                $articlePagerLowerElem->append($pager_last);
                break;
        }

        // 表示件数
        $perVal = $params->getPerPage();
        $sortSelectElem = $doc['dl.sort-select'];
        $sortSelectElem['select:first option:selected']->removeAttr('selected');
        $sortSelectElem["select:first option[value='${perVal}']"]->attr('selected', 'selected');
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
        $sorts_shumoku = 'bukken_shumoku,kakaku,'.$sortType.'eki_kyori';

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