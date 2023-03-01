<?php
namespace Modules\V1api\Services\Pc\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class Mansion extends DetailAbstract
{

    public function createElement(PageInitialSettings $pageInitialSettings, $contentElem, $bukken, $shumoku, Params $params, $codeList, $searchCond)
    {
    	$dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        // 種目情報の取得
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $shumoku_nm = Services\ServiceUtils::getShumokuNameByCd($shumoku);
        // 市区郡の取得
        $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
        // 都道府県の取得
        $ken_cd  = substr($shikugun_cd, 0, 2);
        $ken_ct = Services\ServiceUtils::getKenCtByCd($ken_cd);
        $ken_nm  = $dispModel->ken_nm;
        // 物件APIから取得
        //$shikugun_ct = Services\ServiceUtils::getShikugunObjByCd($ken_cd, $shikugun_cd)->shikugun_roman;
        $shikugun_nm = $this->getVal('csite_shozaichi', $dispModel);
        // 物件名
        $bukken_name = $this->getVal('csite_bukken_title', $dispModel);
        // 問い合わせ先URL
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);


        $mainElem = $contentElem['div.article-main-info'];

        $mainElem['h2.article-heading span.type']->text(Services\ServiceUtils::getShumokuDispModel($dispModel));
        $mainElem['h2.article-heading span.article-name']->text($bukken_name);

        $mainInfoElem = $mainElem['div.article-main-info-body'];

        /*
         * メイン画像
         */
        $this->createMainPhoto($bukken, $mainInfoElem, $params);


        /*
         * メインテーブル
         */
        $mainTableElem = $mainInfoElem['div.right table.table-main-info'];

        //  価格　階建／階　建物構造
        $trElem = $mainTableElem['tr:eq(0)'];
        $priceTxt = str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '<span>万円</span>';
        $trElem['td.cell-price']->text('')->append($priceTxt);
        $trElem['td:eq(1)']->text($this->getVal('csite_kaidate_kai', $dispModel));
        $trElem['td:eq(2)']->text($this->getVal('tatemono_kozo', $dispModel));
        //  駅路線
        $trElem = $mainTableElem['tr:eq(1)'];
        $trElem['td:first']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        // 所在地
        $trElem = $mainTableElem['tr:eq(2)'];
        $trElem['td:first']->text($this->getVal('csite_ken_shozaichi', $dispModel));
        // 築年・間取り・面積
        $trElem = $mainTableElem['tr:eq(3)'];
        $chikunenElem = $this->getVal('csite_chikunengetsu', $dispModel);
        // 新築アイコン
        if ($this->getVal('shinchiku_chuko_cd', $dispModel, true)  == '1') {
            $chikunenElem .= '<p><img src="/pc/imgs/icon_new_article.png" alt=""></p>';
        }
        // 未入居アイコン
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)) {
            $chikunenElem .= '<p><img src="/pc/imgs/icon_not_person.png" alt=""></p>';
        }
        $trElem['td:eq(0)']->text('')->append($chikunenElem);
        $trElem['td:eq(1)']->text($this->getVal('tatemono_ms', $dispModel));
        $trElem['td:eq(2)']->text($this->getVal('madori', $dispModel));

        // おすすめポイント
        $this->setArticlePoint($mainInfoElem, $dataModel, $dispModel);
        
        // オープンハウス
        $opneHouseElem = $mainInfoElem['table.event-schedule'];
        $openHouse = $this->getVal('open_house', $dispModel, true);
        if (empty($openHouse)) {
        	$opneHouseElem->remove();
        } else {
        	$opneHouseElem['td']->text($openHouse);
        }
        
        
        // 設備タグ
        $tagList = $mainInfoElem['div.article-tag ul li'];
        $kodawari = $dispModel->kodawari_joken_cd;
        if (! in_array("07001", $kodawari)) {
            $tagList->eq(0)->addClass('off');
        } // ２階以上
        if (! in_array("07002", $kodawari)) {
            $tagList->eq(1)->addClass('off');
        } // １０階建以上
        if (! in_array("07003", $kodawari)) {
            $tagList->eq(2)->addClass('off');
        } // 最上階
        if (! in_array("07005", $kodawari)) {
            $tagList->eq(3)->addClass('off');
        } // 角部屋
        if (! in_array("08009", $kodawari)) {
            $tagList->eq(4)->addClass('off');
        } // ペット相談
        if (! in_array("14007", $kodawari)) {
            $tagList->eq(5)->addClass('off');
        } // 駐車場（近隣含む）

        // if (! in_array("04001", $kodawari)) {
        //     $tagList->eq(6)->addClass('off');
        // } // ウォークイン
        if (!isset($dataModel->shuyo_saikomen_cd) || $dataModel->shuyo_saikomen_cd != "05") {
            $tagList->eq(6)->addClass('off');
        } // 南向き

        if (! in_array("06001", $kodawari)) {
            $tagList->eq(7)->addClass('off');
        } // オートロック
        if (! in_array("10001", $kodawari)) {
            $tagList->eq(8)->addClass('off');
        } // エレベーター
        if (! in_array("02003", $kodawari)) {
            $tagList->eq(9)->addClass('off');
        } // 追焚き機能

        // お問い合わせボタン
        $mainElem['p.btn-mail-contact a']->attr('href', $inquiryURL);
        // #4692
        // if ($this->isFDP($pageInitialSettings)) {
        $settingRow = $searchCond->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if (!$this->isFDP($pageInitialSettings) || count($fdp->fdp_type) == 0) {
        // END #4692
            $mainElem['p.btn-mail-contact a .btn-contact-fdp']->remove();
        }

        // 物件詳細タブ
        $itemElem = $contentElem['div.item-detail-tab-contents'];
        $this->createTabArea($bukken, $itemElem, $pageInitialSettings, $shumoku, $codeList, $params, $searchCond);



    }

    protected function createBukkenTab($bukken, $itemElem, $pageInitialSettings, $shumoku, $codeList, $fdp)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        // おすすめコメント
        $this->setProComment($itemElem, $dataModel, $dispModel);
        
        /*
         * フォトギャラリー
         */
        $this->createGallery($bukken, $itemElem, $codeList);

        /*
         * パノラマムービー
         */
        $this->createPanorama($bukken, $itemElem);

        /*
         * アピールポイント
         */
        $this->createIppanMessageShosai($bukken, $itemElem);


        /*
         * 物件詳細情報
         */
        $detailElem = $itemElem['section.section-detail-info'];

        // 物件詳細情報　テーブル１
        $detailTableElem1 = $detailElem['table.detail-info-table:eq(0)'];
        //  交通
        $trElem = $detailTableElem1['tr:eq(0)'];
        $trElem['td:first']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        //  その他交通
        $trElem = $detailTableElem1['tr:eq(1)'];
        $trElem['td:first']->text('')->append($this->getKotsus($dispModel));
        // 所在地
        $trElem = $detailTableElem1['tr:eq(2)'];
        $trElem['td:first']->text($this->getVal('csite_ken_shozaichi', $dispModel));
        // 物件種目
        $trElem = $detailTableElem1['tr:eq(3)'];
        $trElem['td:first']->text(Services\ServiceUtils::getShumokuDispModel($dispModel));


        // 物件詳細情報　テーブル２
        $detailTableElem2 = $detailElem['table.detail-info-table:eq(1)'];
        //  価格　平米単価
        $trElem = $detailTableElem2['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        $trElem['td:last']->text($this->getVal('heibei_tanka', $dispModel));
        //  管理費　修繕積立金
        $trElem = $detailTableElem2['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('kanrihi', $dispModel));
        $trElem['td:last']->text($this->getVal('shuzen_tsumitatekin', $dispModel));
        // 借地期間・地代(月額)  権利金
        $trElem = $detailTableElem2['tr:eq(2)'];
        $csite_keiyaku_kikan = $this->getVal('shakuchi_kikan', $dispModel) . '（' . $this->getVal('chidai', $dispModel) . '）';
        $trElem['td:first']->text($csite_keiyaku_kikan);
        $trElem['td:last']->text($this->getVal('kenrikin', $dispModel));
        //  敷金/保証金　維持費等
        $trElem = $detailTableElem2['tr:eq(3)'];
        $shikikin = $this->getVal('shikikin', $dispModel) . '／' . $this->getVal('hoshokin', $dispModel);
        $trElem['td:first']->text($shikikin);
        $trElem['td:last']->text($this->getVal('ijihito', $dispModel));
        // その他一時金　保険等
        $trElem = $detailTableElem2['tr:eq(4)'];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);


        // 物件詳細情報　テーブル３
        $detailTableElem3 = $detailElem['table.detail-info-table:eq(2)'];
        //  建物名・部屋番号
        $trElem = $detailTableElem3['tr:eq(0)'];
        $tatemono_nm = $this->getTatemonoName($dataModel, $dispModel);
        $trElem['td:first']->text($tatemono_nm);
        //  設備
        $trElem = $detailTableElem3['tr:eq(1)'];
        $setsubi = $this->getSetsubis( $dispModel );
        $trElem['td:first']->text($setsubi);

        //瑕疵保証
        $trElem = $detailTableElem3['tr:eq(2)'];
        $kashi_hosho       = $this->getKashihosho($dispModel);
        $trElem['td:first']->text('')->append(empty($kashi_hosho) ? '-' : $kashi_hosho);

        //瑕疵保険
        $trElem = $detailTableElem3['tr:eq(3)'];
        $kashi_hoken        = $this->getKashihoken($dispModel);
        $trElem['td:first']->text('')->append(empty($kashi_hoken) ? '-' : $kashi_hoken);

        //評価・証明書
        $trElem = $detailTableElem3['tr:eq(4)'];
        $trElem['td:first']->text('')->append($this->getHyoukaSyoumeisyo($dispModel));

        // 備考
        $trElem = $detailTableElem3['tr:eq(5)'];
        $trElem['td:first']->text('')->append($this->getBikos($dispModel));


        // 物件詳細情報　テーブル４
        $i = 0;
        $detailTableElem4 = $detailElem['table.detail-info-table:eq(3)'];
        //  間取り
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getMadoriWithUchiwake($dispModel));
        //  専有面積  バルコニー
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        $trElem['td:last']->text($this->getVal('balcony_ms', $dispModel));
        // 階建/階  建物構造
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kaidate_kai', $dispModel));
        $tatemono_kozo = $this->getVal('tatemono_kozo', $dispModel);
        $trElem['td:last']->text($tatemono_kozo);
        //  築年月　総戸数
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $chikunengetsu = $this->getVal('csite_chikunengetsu', $dispModel);
        $trElem['td:first']->text($chikunengetsu);
        $trElem['td:last']->text($this->getVal('sokosu', $dispModel));

        // リフォーム
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $reform = $this->getVal('reform', $dispModel, true);
        if($reform){
            $trElem['td:first']->text('')->append(nl2br($reform));
        }else{
            $trElem->remove();
            $i--;
        }

        // リノベーション
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $renovation = $this->getVal('renovation', $dispModel, true);
        if($renovation){
            $trElem['td:first']->text('')->append(nl2br($renovation));
        }else{
            $trElem->remove();
            $i--;
        }

        // リフォーム/リノベーション可能箇所
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        if($this->getVal('reform_renovation_ka_fl', $dataModel, true)){
            $trElem['td:first']->text('')->append(nl2br($this->getVal('reform_renovation_ka', $dispModel)));
        }else{
            $trElem->remove();
            $i--;
        }

        //  駐車場  バイク置き場
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chushajo', $dispModel));
        $trElem['td:last']->text($this->getVal('bike_okiba', $dispModel));
        //  駐輪場  ペット
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('churinjo', $dispModel));
        $trElem['td:last']->text($this->getVal('pet', $dispModel));
        //  土地権利　敷地面積
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tochi_kenri_nm', $dataModel));
        $trElem['td:last']->text($this->getVal('tochi_shikichi_ms', $dispModel));
        // 管理形態／管理員の勤務形態　国土法届出
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kanri_keitai', $dispModel));
        $trElem['td:last']->text($this->getVal('kokudoho_nm', $dataModel));


        // 物件詳細情報　テーブル５
        $detailTableElem5 = $detailElem['table.detail-info-table:eq(4)'];
        //  条件等  現況
        $trElem = $detailTableElem5['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_jokento', $dispModel));
        $trElem['td:last']->text($this->getVal('genkyo_nm', $dataModel));
        //  引渡し 仲介手数料
        $trElem = $detailTableElem5['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('hikiwatashi', $dispModel));
        $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem['th:last']->text('');
        	$trElem['td:last']->text('');
        } else {
        	$trElem['td:last']->text($chukai);
        }
        //  物件番号　管理番号
        $trElem = $detailTableElem5['tr:eq(2)'];
        $trElem['td:first']->text($this->getVal('bukken_no', $dispModel));
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem['th:last']->text('');
        	$trElem['td:last']->text('');
        } else {
        	$trElem['td:last']->text($this->getVal('kanri_no', $dispModel));
        }
        // 情報公開日  次回更新予定日
        $trElem = $detailTableElem5['tr:eq(3)'];
        $trElem['td:first']->text($this->getVal('csite_kokai_date', $dispModel));
        $trElem['td:last']->text($this->getVal('jikai_koshin_yotei_date', $dispModel));

        /*
         * 情報提供会社
         */
        $companyElem = $itemElem['section.section-company'];
        $this->createSectionCompany($pageInitialSettings, $companyElem, $bukken, $shumoku, $fdp);
    }
}