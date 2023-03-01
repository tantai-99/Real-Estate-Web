<?php
namespace Modules\V1api\Services\Sp\Element\Detail;

use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
class UriTochi extends DetailAbstract
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
        // 現地販売会
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
        // //  価格　最適用途
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('saiteki_yoto_nm', $dataModel));
        // //  交通　最初のひとつ
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($dispModel->csite_kotsus[0][0]);
        // // 所在地
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_shozaichi', $dispModel));
        // // 土地面積・坪数 坪単価
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('tochi_shikichi_ms', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('tochi_tsubo_su', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('tsubo_tanka_manen', $dispModel));
        // // 私道負担面積　引渡条件　建ぺい率／容積率
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getVal('csite_shido_futan_ms', $dispModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $trElem['td:first']->text($this->getHikiwatashi($dataModel));
        // $trElem = $detailTableElem1[sprintf('tr:eq(%d)',$i++)];
        // $tuboTxt = $this->getVal('kenpei_ritsu', $dispModel) . '／' . $this->getVal('yoseki_ritsu', $dispModel);
        // $trElem['td:first']->text($tuboTxt);

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
        // 価格　坪単価
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kakaku', $dispModel));
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tsubo_tanka_manen', $dispModel));
        // 借地期間・地代(月額)　権利金
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $csite_keiyaku_kikan = $this->getVal('shakuchi_kikan', $dispModel) . '（' . $this->getVal('chidai', $dispModel) . '）';
        $trElem['td:first']->text($csite_keiyaku_kikan);
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kenrikin', $dispModel));
        //  敷金 / 補償金　維持費等
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('shikikin', $dispModel) . '／' . $this->getVal('hoshokin', $dispModel));
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('ijihito', $dispModel));
        // その他一時金
        $trElem = $detailTableElem3[sprintf('tr:eq(%d)',$i++)];
        $sonota = $this->getSonota($dispModel);
        $trElem['td:first']->text(empty($sonota) ? '-' : $sonota);

        /*
         * アピールポイント
         */
        $this->createIppanMessageShosai($bukken, $contentElem);
        
        // 物件詳細情報　テーブル4
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
        // 備考
        $trElem = $detailTableElem4[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text('')->append($this->getBikos($dispModel));


        // 物件詳細情報　テーブル5
        $detailTableElem5 = $contentElem['div.table-article-info:eq(1) table:eq(4)'];
        $i = 0;
        //  土地面積・坪数
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $tochi_shikichi_ms = $this->getVal('tochi_shikichi_ms', $dispModel);
        $trElem['td:first']->text($tochi_shikichi_ms);
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tochi_tsubo_su', $dispModel));
        // 最適用途 私道負担面積
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('saiteki_yoto_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_shido_futan_ms', $dispModel));
        // 土地権利　都市計画
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('tochi_kenri_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('toshi_keikaku_nm', $dataModel));
        // 用途地域　地勢
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getYotoChiiki($dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chisei', $dispModel));
        // 建ぺい率　容積率
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kenpei_ritsu', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('yoseki_ritsu', $dispModel));
        // 接道状況　地目
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('setsudo_jokyo', $dispModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('chimoku_nm', $dataModel));
        // 国土法届出　セットバック
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('kokudoho_nm', $dataModel));
        $trElem = $detailTableElem5[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('setback', $dispModel));

        // 物件詳細情報　テーブル6
        $detailTableElem6 = $contentElem['div.table-article-info:eq(1) table:eq(5)'];
        $i = 0;
        //  条件等
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $jokento = [];
        if (isset($dataModel->teiki_shakka_fl) && $dataModel->teiki_shakka_fl) {
            $jokento[] = '定期借家';
        }
        if (isset($dataModel->kenchiku_joken_tsuki_fl) && $dataModel->kenchiku_joken_tsuki_fl) {
            $jokento[] = '<a class="modal-commission-building-conditions" href="#">建築条件付き<span class="tooltip"><i class="tooltip-icon"></i></span></a>';
        }
        if (isset($dataModel->owner_change_fl) && $dataModel->owner_change_fl) {
            $jokento[] = 'オーナーチェンジ';
        }
        if (count($jokento) > 0) {
            $jokento = implode("・", $jokento);
        } else {
            $jokento = '-';
        }
        $trElem['td:first']->text('')->append($jokento);
        //  現況  引渡し
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('genkyo_nm', $dataModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('hikiwatashi', $dispModel));
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
        //  仲介手数料
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $chukai = $this->getVal('chukai_tesuryo', $dispModel, true);
        if (empty($chukai) || $dispModel->niji_kokoku_jido_kokai_fl) {
            $trElem->remove();
            $i--;
        } else {
            $trElem['td:first']->text($chukai);
        }
        // 情報公開日  次回更新予定日
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('csite_kokai_date', $dispModel));
        $trElem = $detailTableElem6[sprintf('tr:eq(%d)',$i++)];
        $trElem['td:first']->text($this->getVal('jikai_koshin_yotei_date', $dispModel));

        //重複項目削除
        $contentElem['.table-article-info.main']->remove();

    }
    private function getHikiwatashi($dataModel) {
        $result = '-';
        if (isset($dataModel->hikiwatashi_joken_nm) && !empty($dataModel->hikiwatashi_joken_nm)) {
            $result = null;
            foreach ($dataModel->hikiwatashi_joken_nm as $joken) {
                $result .= $joken . ' ';
            }
        }
        return $result;
    }
}