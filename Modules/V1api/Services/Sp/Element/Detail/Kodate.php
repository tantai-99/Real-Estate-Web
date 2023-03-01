<?php
namespace Modules\V1api\Services\Sp\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class Kodate extends DetailAbstract
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
        // オープンハウス
        $this->setOpenHouse($contentElem, $dispModel);
        
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
        // $i = 0;
        // //  価格　間取り
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('madori', $dispModel));
        // //  駅路線
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($dispModel->csite_kotsus[0][0]);
        // // 所在地
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_shozaichi', $dispModel));
        // // 築年・建物面積・土地面積
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $chikunenElem = $this->getVal('csite_chikunengetsu', $dispModel);
        // $trElem['td:first']->text('')->append($chikunenElem);
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('tochi_shikichi_ms', $dispModel));
        // // 私道負担面積
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_shido_futan_ms', $dispModel));

        /*
         * 物件詳細情報2
         */
        $detailTableElem2 = $contentElem['div.table-article-info:eq(1) table:eq(0)'];
        $i = 0;
        //  交通
        $trElem = $detailTableElem2[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text('')->append($this->getKotsusValue($dispModel->csite_kotsus[0]));
        //  その他交通
        $trElem = $detailTableElem2[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text('')->append($this->getKotsus($dispModel));
        // 所在地
        $trElem = $detailTableElem2[sprintf('tr:eq(%d)',$i++)];
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
        $trElem = $detailTableElem2[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text(Services\ServiceUtils::getShumokuDispModel($dispModel));

        // 物件詳細情報　テーブル３
        $detailTableElem3 = $contentElem['div.table-article-info:eq(1) table:eq(1)'];
        $i = 0;
        //  価格　借地期間・地代(月額)
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $csite_keiyaku_kikan = $this->getVal('shakuchi_kikan', $dispModel) . '（' . $this->getVal('chidai', $dispModel) . '）';
        $trElem['td:first']->text($csite_keiyaku_kikan);
        // 権利金 敷金/保証金　
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kenrikin', $dispModel));
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $shikikin = $this->getVal('shikikin', $dispModel) . '／' . $this->getVal('hoshokin', $dispModel);
        $trElem['td:first']->text($shikikin);
        //  維持費等 その他一時金
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('ijihito', $dispModel));
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);

        /*
         * アピールポイント
         */
        $this->createIppanMessageShosai($bukken, $contentElem);

        // 物件詳細情報　テーブル３
        $detailTableElem4 = $contentElem['div.table-article-info:eq(1) table:eq(3)'];
        $i = 0;
        //  設備
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $setsubi = $this->getSetsubis( $dispModel );
        $trElem['td:first']->text($setsubi);

        //  特記
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        //$tokki = $this->getVal('tokki', $dispModel);
        $tokki = $this->getTokkiVal($shumoku, $dataModel, $dispModel);
        $trElem['td:first']->text($tokki);

        //瑕疵保証
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $kashi_hosho      = $this->getKashihosho($dispModel);
        $trElem['td:first']->text('')->append(empty($kashi_hosho) ? '-' : $kashi_hosho);
                
        //瑕疵保険
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $kashi_hoken        = $this->getKashihoken($dispModel);
        $trElem['td:first']->text('')->append(empty($kashi_hoken) ? '-' : $kashi_hoken);
        
        //評価・証明書
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text('')->append($this->getHyoukaSyoumeisyo($dispModel));

        // 備考
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text('')->append($this->getBikos($dispModel));


        // 物件詳細情報　テーブル４
        $detailTableElem5 = $contentElem['div.table-article-info:eq(1) table:eq(4)'];
        $i = 0;
        //  建物名・部屋番号
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $tatemono_nm = $this->getTatemonoName($dataModel, $dispModel);
        $trElem['td:first']->text($tatemono_nm);
        //  間取り 建物面積
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getMadoriWithUchiwake($dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tatemono_ms', $dispModel));
        //  土地面積　私道負担面積
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tochi_shikichi_ms', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_shido_futan_ms', $dispModel));
        // 築年月 階建/階
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $chikunengetsu = $this->getVal('csite_chikunengetsu', $dispModel);
          // 新築アイコン
        if ($this->getVal('shinchiku_chuko_cd', $dispModel, true)  == '1') {
            $chikunengetsu .= '<br><img src="/sp/imgs/icon_new_article.png" alt="新築" class="icon-status">';
        }
          // 未入居アイコン
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)) {
            $chikunengetsu .= '<br><img src="/sp/imgs/icon_not_person.png" alt="未入居" class="icon-status">';
        }
        $trElem['td:first']->text('')->append($chikunengetsu);
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kaidate_kai', $dispModel));


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


        //  駐車場  建物構造
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chushajo', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $tatemono_kozo = $this->getVal('tatemono_kozo', $dispModel);
        $trElem['td:first']->text($tatemono_kozo);
        //  土地権利　都市計画
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tochi_kenri_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('toshi_keikaku_nm', $dataModel));
        // 用途地域　接道状況
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getYotoChiiki($dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('setsudo_jokyo', $dispModel));
        // 建ぺい率　容積率
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kenpei_ritsu', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('yoseki_ritsu', $dispModel));
        // 地目　地勢
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chimoku_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chisei', $dispModel));
        // 国土法届出　セットバック
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kokudoho_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('setback', $dispModel));
        // 建築確認番号
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kenchiku_kakunin_no', $dataModel));


        // 物件詳細情報　テーブル５
        $detailTableElem6 = $contentElem['div.table-article-info:eq(1) table:eq(5)'];
        $i = 0;
        //  現況 引渡し
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('genkyo_nm', $dataModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('hikiwatashi', $dispModel));
        //  仲介手数料
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
            $trElem->remove();
            $i--;
        } else {
            $trElem['td:first']->text($chukai);
        }
        //  物件番号　管理番号
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