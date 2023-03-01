<?php
namespace Modules\V1api\Services\Pc\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class KasiOther extends DetailAbstract
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

        //  賃料・管理費
        $trElem = $mainTableElem['tr:eq(0)'];
        $priceTxt = str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '<span>万円</span>';
        $trElem['td:eq(0)']->text('')->append($priceTxt);
        $kanrihito = $this->getVal('kanrihito', $dispModel);
        $trElem['td:eq(1)']->text($kanrihito);

        //  敷金・保証金・礼金
        $trElem = $mainTableElem['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('shikikin', $dispModel)
            . '/' . $this->getVal('hoshokin', $dispModel));
        $trElem['td:last']->text($this->getVal('reikin', $dispModel));
        //  交通　最初のひとつ
        $trElem = $mainTableElem['tr:eq(2)'];
        $trElem['td:first']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        // 所在地
        $trElem = $mainTableElem['tr:eq(3)'];
        $trElem['td:first']->text($this->getVal('csite_ken_shozaichi', $dispModel));
        // 築年・使用部分面積・坪数／坪単価
        $trElem = $mainTableElem['tr:eq(4)'];
        $chikunenElem = $this->getVal('csite_chikunengetsu', $dispModel);

        // 新築アイコン
        //if ($this->getVal('shinchiku_chuko_cd', $dispModel, true)  == '1') {
        //    $chikunenElem .= '<p><img src="/pc/imgs/icon_new_article.png" alt=""></p>';
        //}
        // 未入居アイコン
        //if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)) {
        //    $chikunenElem .= '<p><img src="/pc/imgs/icon_not_person.png" alt=""></p>';
        //}

        $trElem['td:eq(0)']->text('')->append($chikunenElem);
        $trElem['td:eq(1)']->text($this->getVal('tatemono_ms', $dispModel));
        $tuboTxt = $this->getVal('tatemono_tsubo_su', $dispModel) . '／' . $this->getVal('tsubo_tanka_manen', $dispModel);
        $trElem['td:eq(2)']->text($tuboTxt);


        // 設備タグ
        $tagList = $mainInfoElem['div.article-tag ul li'];
        $kodawari = $dispModel->kodawari_joken_cd;
        if (! in_array("14007", $kodawari)) {
            $tagList->eq(0)->addClass('off');
        } // 駐車場（近隣含む）
        if (! in_array("08001", $kodawari)) {
            $tagList->eq(1)->addClass('off');
        } // 即引渡し可
        if (! in_array("10001", $kodawari)) {
            $tagList->eq(2)->addClass('off');
        } // エレベーター

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
        //  賃料 管理費等
        $trElem = $detailTableElem2['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        $kanrihito = $this->getVal('kanrihito', $dispModel);
        $trElem['td:last']->text($kanrihito);
        //  敷金 / 補償金　礼金
        $trElem = $detailTableElem2['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('shikikin', $dispModel) . '/' . $this->getVal('hoshokin', $dispModel));
        $trElem['td:last']->text($this->getVal('reikin', $dispModel));
        // 敷引　保証金償却
        $trElem = $detailTableElem2['tr:eq(2)'];
        $shikibiki = $this->getVal('shikibiki', $dispModel);
        $trElem['td:first']->text($shikibiki);
        $hoshokin_shokyaku = $this->getVal('hoshokin_shokyaku', $dispModel);
        $trElem['td:last']->text($hoshokin_shokyaku);
        // その他一時金　造作譲渡
        $trElem = $detailTableElem2['tr:eq(3)'];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);
        $trElem['td:last']->text($this->getVal('zosaku_joto', $dispModel));
        // 維持費等・保険等
        $trElem = $detailTableElem2['tr:eq(4)'];
        $trElem['td:first']->text($this->getVal('ijihito', $dispModel));
        $trElem['td:last']->text($this->getVal('hokento', $dispModel));
        // 権利金・坪単価
        $trElem = $detailTableElem2['tr:eq(5)'];
        $trElem['td:first']->text($this->getVal('kenrikin', $dispModel));
        $trElem['td:last']->text($this->getVal('tsubo_tanka_manen', $dispModel));
        // クレジットカード決済
        $trElem = $detailTableElem2['tr:eq(6)'];
        $trElem['td:first']->text($this->getVal('credit_kessai', $dispModel));
        //２次広告の場合とくrじっと決済がない場合は場所ごと削除
        if($dispModel->niji_kokoku_jido_kokai_fl == true) {
            $detailTableElem2['tr:eq(6)']->remove();
        }else if(!isset($dispModel->credit_kessai) || $dispModel->credit_kessai == false) {
            $detailTableElem2['tr:eq(6)']->remove();
        }

        // 物件詳細情報　テーブル３
        $detailTableElem3 = $detailElem['table.detail-info-table:eq(2)'];
        //  建物名・部屋番号
        $trElem = $detailTableElem3['tr:eq(0)'];
        $tatemono_nm = $this->getTatemonoName($dataModel, $dispModel);
        $trElem['td:first']->text($tatemono_nm);
        //  特記
        $trElem = $detailTableElem3['tr:eq(1)'];
        $tokki = $this->getTokkiVal($shumoku, $dataModel, $dispModel);
        $trElem['td:first']->text($tokki);
        //  設備
        $trElem = $detailTableElem3['tr:eq(2)'];
        $setsubi = $this->getSetsubis( $dispModel );
        $trElem['td:first']->text($setsubi);
        // 備考
        $trElem = $detailTableElem3['tr:eq(3)'];
        $trElem['td:first']->text('')->append($this->getBikos($dispModel,true));


        // 物件詳細情報　テーブル４
        $i = 0;
        $detailTableElem4 = $detailElem['table.detail-info-table:eq(3)'];
        //  使用部分面積・坪数
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        $trElem['td:last']->text($this->getVal('tatemono_tsubo_su', $dispModel));
        // 土地面積・築年月
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $tochi_shikichi_ms = $this->getVal('tochi_shikichi_ms', $dispModel);
        $trElem['td:first']->text($tochi_shikichi_ms);
        $chikunengetsu = $this->getVal('csite_chikunengetsu', $dispModel);
        $trElem['td:last']->text($chikunengetsu);

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

        // 階建/階  建物構造
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kaidate_kai', $dispModel));
        $tatemono_kozo = $this->getVal('tatemono_kozo', $dispModel);
        $trElem['td:last']->text($tatemono_kozo);
        //  駐車場  バイク置き場
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chushajo', $dispModel));
        $trElem['td:last']->text($this->getVal('bike_okiba', $dispModel));
        //  駐輪場  接道状況
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('churinjo', $dispModel));
        $trElem['td:last']->text($this->getVal('setsudo_jokyo', $dispModel));
        //  用途地域　間取り
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getYotoChiiki($dataModel));
        $trElem['td:last']->text($this->getVal('madori', $dispModel));

        // 物件詳細情報　テーブル５
        $detailTableElem5 = $detailElem['table.detail-info-table:eq(4)'];
        //  契約期間  現況
        $trElem = $detailTableElem5['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_keiyaku_kikan', $dispModel));
        $trElem['td:last']->text($this->getVal('genkyo_nm', $dataModel));
        //  条件等  引渡し
        $trElem = $detailTableElem5['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('csite_jokento', $dispModel));
        $trElem['td:last']->text($this->getVal('hikiwatashi', $dispModel));
        //  更新料  仲介手数料
        $trElem = $detailTableElem5['tr:eq(2)'];
        $trElem['td:first']->text($this->getVal('koshin_ryo', $dispModel));
        $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem['th:last']->text('');
        	$trElem['td:last']->text('');
        } else {
        	$trElem['td:last']->text($chukai);
        }
        //  物件番号 管理番号
        $trElem = $detailTableElem5['tr:eq(3)'];
        $trElem['td:first']->text($this->getVal('bukken_no', $dispModel));
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem['th:last']->text('');
        	$trElem['td:last']->text('');
        } else {
        	$trElem['td:last']->text($this->getVal('kanri_no', $dispModel));
        }
        // 情報公開日  次回更新予定日
        $trElem = $detailTableElem5['tr:eq(4)'];
        $trElem['td:first']->text($this->getVal('csite_kokai_date', $dispModel));
        $trElem['td:last']->text($this->getVal('jikai_koshin_yotei_date', $dispModel));

        /*
         * 情報提供会社
         */
        $companyElem = $itemElem['section.section-company'];
        $this->createSectionCompany($pageInitialSettings, $companyElem, $bukken, $shumoku, $fdp);
    }
}