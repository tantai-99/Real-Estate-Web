<?php
namespace Modules\V1api\Services\Sp\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class KasiOther extends DetailAbstract
{

    public function createElement($kaiinNo, $contentElem, $bukken, $shumoku, Params $params, $codeList, $pageInitialSettings, $searchCond)
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

        $mainInfoElem = $contentElem['section.article-main-info'];
        // 新着
        if (! $this->getVal('new_mark_fl_for_c', $dispModel, true)) {
            $mainInfoElem['h2.article-heading span.new']->remove();
        }
        $mainInfoElem['h2.article-heading span.article-name']->text($bukken_name);

        /*
         * フォトギャラリー
         */
        $this->createGallery($bukken, $mainInfoElem, $codeList);

        // パノラマ
        $this->createPanorama($bukken, $contentElem);
        // おすすめコメント
        $this->setProComment($contentElem, $dataModel, $dispModel);
        
        /*
         * 物件情報詳細
         */
        // 物件情報
        $this->createBukkenTabChintai($pageInitialSettings, $bukken, $contentElem, $kaiinNo, $shumoku);
        // 周辺情報
        $this->createShuhenTab($bukken, $contentElem, $kaiinNo, $pageInitialSettings, $shumoku, $params, $searchCond);
        // 情報提供会社
        $this->createSectionCompany($pageInitialSettings, $contentElem, $bukken, $shumoku);

        // お問い合わせボタン
        $contentElem['p.btn-mail-contact a']->attr('href', $inquiryURL);
        $settingRow = $searchCond->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if (!$this->isFDP($pageInitialSettings) || count($fdp->fdp_type) == 0) {
        // END #4692
            $contentElem['p.btn-mail-contact a .btn-contact-fdp']->remove();
        }
    }

    private function createBukkenTabChintai($pageInitialSettings, $bukken, $contentElem, $kaiinNo, $shumoku)
    {
        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        /*
         * 物件詳細情報
         */
        // 物件詳細情報　テーブル1-1
        // $detailTableElem1 = $contentElem['div.table-article-info:eq(0) table:eq(0)'];
        // //  賃料
        // $trElem = $detailTableElem1['tr:eq(0)'];
        // $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        // // 管理費等
        // $trElem = $detailTableElem1['tr:eq(1)'];
        // $kanrihito = $this->getVal('kanrihito', $dispModel);
        // $trElem['td:first']->text($kanrihito);
        // //  敷金 / 保証金
        // $trElem = $detailTableElem1['tr:eq(2)'];
        // $trElem['td:first']->text($this->getVal('shikikin', $dispModel) . '/' . $this->getVal('hoshokin', $dispModel));
        // // 礼金
        // $trElem = $detailTableElem1['tr:eq(3)'];
        // $trElem['td:first']->text($this->getVal('reikin', $dispModel));
        // //  交通
        // $trElem = $detailTableElem1['tr:eq(4)'];
        // $trElem['td:first']->text($dispModel->csite_kotsus[0][0]);
        // // 所在地
        // $trElem = $detailTableElem1['tr:eq(5)'];
        // $trElem['td:first']->text($this->getVal('csite_shozaichi', $dispModel));
        // // 築年月
        // $trElem = $detailTableElem1['tr:eq(6)'];
        // $chikunengetsu = $this->getVal('csite_chikunengetsu', $dispModel);
        // $trElem['td:first']->text($chikunengetsu);
        // //  専有面積
        // $trElem = $detailTableElem1['tr:eq(7)'];
        // $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        // // 坪数／坪単価
        // $trElem = $detailTableElem1['tr:eq(8)'];
        // $tuboTxt = $this->getVal('tatemono_tsubo_su', $dispModel) . '／' . $this->getVal('tsubo_tanka_manen', $dispModel);
        // $trElem['td:first']->text($tuboTxt);

        // 物件詳細情報　テーブル2-1
        $detailTableElem2 = $contentElem['div.table-article-info:eq(1) table:eq(0)'];
        //  交通
        $trElem = $detailTableElem2['tr:eq(0)'];
        $trElem['td:first']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        //  その他交通
        $trElem = $detailTableElem2['tr:eq(1)'];
        $trElem['td:first']->text('')->append($this->getKotsus($dispModel));
        // 所在地
        $trElem = $detailTableElem2['tr:eq(2)'];
        $csite_shozaichi = $this->getVal('csite_ken_shozaichi', $dispModel);
        if ($this->canDisplayMap( $pageInitialSettings, $bukken, $shumoku ))
        {
            // 4689: Check lat, lon exist
            if (Services\ServiceUtils::checkLatLon($bukken)) {
                $csite_shozaichi = sprintf('<a href="map.html" class="link-map">%s</a>', $csite_shozaichi);
            }
        }
        $trElem['td:first']->html($csite_shozaichi);
        // 物件種目
        $trElem = $detailTableElem2['tr:eq(3)'];
        $trElem['td:first']->text(Services\ServiceUtils::getShumokuDispModel($dispModel));


        // 物件詳細情報　テーブル2-2
        $detailTableElem3 = $contentElem['div.table-article-info:eq(1) table:eq(1)'];
        //  賃料
        $trElem = $detailTableElem3['tr:eq(0)'];
        $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        // 管理費等
        $trElem = $detailTableElem3['tr:eq(1)'];
        $kanrihito = $this->getVal('kanrihito', $dispModel);
        $trElem['td:first']->text($kanrihito);
        //  敷金 / 保証金
        $trElem = $detailTableElem3['tr:eq(2)'];
        $trElem['td:first']->text($this->getVal('shikikin', $dispModel) . '/' . $this->getVal('hoshokin', $dispModel));
        // 礼金
        $trElem = $detailTableElem3['tr:eq(3)'];
        $trElem['td:first']->text($this->getVal('reikin', $dispModel));
        // 敷引
        $trElem = $detailTableElem3['tr:eq(4)'];
        $shikibiki = $this->getVal('shikibiki', $dispModel);
        $trElem['td:first']->text($shikibiki);
        // 保証金償却
        $trElem = $detailTableElem3['tr:eq(5)'];
        $hoshokin_shokyaku = $this->getVal('hoshokin_shokyaku', $dispModel);
        $trElem['td:first']->text($hoshokin_shokyaku);
        // その他一時金
        $trElem = $detailTableElem3['tr:eq(6)'];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);
        // 造作譲渡
        $trElem = $detailTableElem3['tr:eq(7)'];
        $trElem['td:first']->text($this->getVal('zosaku_joto', $dispModel));
        // 維持費等・保険等
        $trElem = $detailTableElem3['tr:eq(8)'];
        $trElem['td:first']->text($this->getVal('ijihito', $dispModel));
        $trElem = $detailTableElem3['tr:eq(9)'];
        $trElem['td:first']->text($this->getVal('hokento', $dispModel));

        // 権利金・坪単価
        $trElem = $detailTableElem3['tr:eq(10)'];
        $trElem['td:first']->text($this->getVal('kenrikin', $dispModel));
        $trElem = $detailTableElem3['tr:eq(11)'];
        $trElem['td:first']->text($this->getVal('tsubo_tanka_manen', $dispModel));

        // クレジットカード決済
        $trElem = $detailTableElem3['tr:eq(12)'];
        $trElem['td:first']->text($this->getVal('credit_kessai', $dispModel));

        //２次広告の場合とくrじっと決済がない場合は場所ごと削除
        if($dispModel->niji_kokoku_jido_kokai_fl == true) {
            $detailTableElem3['tr:eq(12)']->remove();
        }else if(!isset($dispModel->credit_kessai) || $dispModel->credit_kessai == false) {
            $detailTableElem3['tr:eq(12)']->remove();
        }

        /*
         * アピールポイント
         */
        $this->createIppanMessageShosai($bukken, $contentElem);

        // 物件詳細情報　テーブル2-3
        $detailTableElem4 = $contentElem['div.table-article-info:eq(1) table:eq(3)'];
        //  建物名・部屋番号
        $trElem = $detailTableElem4['tr:eq(0)'];
        $tatemono_nm = $this->getTatemonoName($dataModel, $dispModel);
        $trElem['td:first']->text($tatemono_nm);
        //  特記
        $trElem = $detailTableElem4['tr:eq(1)'];
        $tokki = $this->getTokkiVal($shumoku, $dataModel, $dispModel);
        $trElem['td:first']->text($tokki);
        //  設備
        $trElem = $detailTableElem4['tr:eq(2)'];
        $setsubi = $this->getSetsubis( $dispModel );
        $trElem['td:first']->text($setsubi);
        // 備考
        $trElem = $detailTableElem4['tr:eq(3)'];
        $trElem['td:first']->text('')->append($this->getBikos($dispModel,true));


        // 物件詳細情報　テーブル2-4
        $i = 0;
        $detailTableElem5 = $contentElem['div.table-article-info:eq(1) table:eq(4)'];
        //  使用部分面積
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        //  坪数
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tatemono_tsubo_su', $dispModel));
        // 土地面積・築年月
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $tochi_shikichi_ms = $this->getVal('tochi_shikichi_ms', $dispModel);
        $trElem['td:first']->text($tochi_shikichi_ms);
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $chikunengetsu = $this->getVal('csite_chikunengetsu', $dispModel);

        // 新築アイコン
        //if ($this->getVal('shinchiku_chuko_cd', $dispModel, true)  == '1') {
        //    $chikunengetsu .= '<br><img src="/sp/imgs/icon_new_article.png" alt="新築" class="icon-status">';
        //}
        // 未入居アイコン
        //if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)) {
        //    $chikunengetsu .= '<br><img src="/sp/imgs/icon_not_person.png" alt="未入居" class="icon-status">';
        //}

        $trElem['td:first']->text('')->append($chikunengetsu);

        // リフォーム
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $reform = $this->getVal('reform', $dispModel,true);
        if($reform){
            $trElem['td:first']->addClass('article-accordion');
            $trElem['td:first']->text('')->append(nl2br($reform));
        }else{
            $trElem->remove();
            $i--;
        }

        // リノベーション
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $renovation = $this->getVal('renovation', $dispModel,true);
        if($renovation){
            $trElem['td:first']->text('')->append(nl2br($renovation));
        }else{
            $trElem->remove();
            $i--;
        }

        // 階建/階  建物構造
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kaidate_kai', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $tatemono_kozo = $this->getVal('tatemono_kozo', $dispModel);
        $trElem['td:first']->text($tatemono_kozo);
        //  駐車場  バイク置き場
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chushajo', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('bike_okiba', $dispModel));
        //  駐輪場  接道状況
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('churinjo', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('setsudo_jokyo', $dispModel));
        //  用途地域　間取り
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getYotoChiiki($dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('madori', $dispModel));

        // 物件詳細情報　テーブル５
        $detailTableElem6 = $contentElem['div.table-article-info:eq(1) table:eq(5)'];
        $i = 0;
        //  契約期間  現況
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_keiyaku_kikan', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('genkyo_nm', $dataModel));
        //  条件等  引渡し
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_jokento', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('hikiwatashi', $dispModel));
        //  更新料  仲介手数料
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('koshin_ryo', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
            $trElem->remove();
            $i--;
        } else {
            $trElem['td:first']->text($chukai);
        }
        //  物件番号 管理番号
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('bukken_no', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        if ($dispModel->niji_kokoku_jido_kokai_fl) {
        	$trElem->remove();
            $i--;
        } else {
        	$trElem['td:first']->text($this->getVal('kanri_no', $dispModel));
        }
        // 情報公開日  次回更新予定日
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kokai_date', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('jikai_koshin_yotei_date', $dispModel));

        //重複項目削除
        $contentElem['.table-article-info.main']->remove();
    }
}