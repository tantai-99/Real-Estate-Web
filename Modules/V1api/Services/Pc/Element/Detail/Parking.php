<?php
namespace Modules\V1api\Services\Pc\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class Parking extends DetailAbstract
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

        //  賃料
        $trElem = $mainTableElem['tr:eq(0)'];
        $priceTxt = str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '<span>万円</span>';
        $trElem['td:eq(0)']->text('')->append($priceTxt);

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
        $trElem['td:first']->text($this->getVal('shikikin', $dispModel) . '／' . $this->getVal('hoshokin', $dispModel));
        $trElem['td:last']->text($this->getVal('reikin', $dispModel));
        // 敷引　保証金償却
        $trElem = $detailTableElem2['tr:eq(2)'];
        $shikibiki = $this->getVal('shikibiki', $dispModel);
        $trElem['td:first']->text($shikibiki);
        $hoshokin_shokyaku = $this->getVal('hoshokin_shokyaku', $dispModel);
        $trElem['td:last']->text($hoshokin_shokyaku);
        // その他一時金　保険等
        $trElem = $detailTableElem2['tr:eq(3)'];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);
        $trElem['td:last']->text($this->getVal('hokento', $dispModel));
        // 維持費等 クレジットカード決済
        $trElem = $detailTableElem2['tr:eq(4)'];
        $trElem['td:first']->text($this->getVal('ijihito', $dispModel));
        $trElem['td:last']->text($this->getVal('credit_kessai', $dispModel));
        //２次広告の場合とクレジット決済がない場合は場所ごと削除
        if($dispModel->niji_kokoku_jido_kokai_fl == true) {
            $trElem['th:eq(1)']->remove();
            $trElem['td:eq(1)']->remove();
            $trElem['td:first']->attr('colspan','3');
        }else if(!isset($dispModel->credit_kessai) || $dispModel->credit_kessai == false) {
            $trElem['th:eq(1)']->remove();
            $trElem['td:eq(1)']->remove();
            $trElem['td:first']->attr('colspan','3');
        }


        // 物件詳細情報　テーブル３
        $detailTableElem3 = $detailElem['table.detail-info-table:eq(2)'];
        //  駐車場名
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
        $detailTableElem4 = $detailElem['table.detail-info-table:eq(3)'];
        //  契約期間  現況
        $trElem = $detailTableElem4['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_keiyaku_kikan', $dispModel));
        $trElem['td:last']->text($this->getVal('genkyo_nm', $dataModel));
        //  引渡し　更新料
        $trElem = $detailTableElem4['tr:eq(1)'];
        $trElem['td:first']->text($this->getVal('hikiwatashi', $dispModel));
        $trElem['td:last']->text($this->getVal('koshin_ryo', $dispModel));
        //  仲介手数料
        $trElem = $detailTableElem4['tr:eq(2)'];
            $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
//         	$trElem['th:first']->text('');
//         	$trElem['td:first']->text('');
			$trElem->remove();
			$trNum = 2;
        } else {
        	$trElem['td:first']->text($chukai);
			$trNum = 3;
        }
        //  物件番号 管理番号
        $trElem = $detailTableElem4['tr:eq('. $trNum .')'];
        $trElem['td:first']->text($this->getVal('bukken_no', $dispModel));
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem['th:last']->text('');
        	$trElem['td:last']->text('');
        } else {
        	$trElem['td:last']->text($this->getVal('kanri_no', $dispModel));
        }
        $trNum++;
        // 情報公開日  次回更新予定日
        $trElem = $detailTableElem4['tr:eq('. $trNum .')'];
        $trElem['td:first']->text($this->getVal('csite_kokai_date', $dispModel));
        $trElem['td:last']->text($this->getVal('jikai_koshin_yotei_date', $dispModel));

        /*
         * 情報提供会社
         */
        $companyElem = $itemElem['section.section-company'];
        $this->createSectionCompany($pageInitialSettings, $companyElem, $bukken, $shumoku, $fdp);
    }
}