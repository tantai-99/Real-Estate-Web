<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Library\Custom\Model\Estate;
use phpQuery;
use Modules\V1api\Models;

class BukkenList
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;

    public function __construct()
    {
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    /**
     * 物件一覧の１物件の要素を作成して返します。
     *
     * @param $shumoku 物件種目のコード
     * @param $dispModel 物件APIの表示モデル
     * @param $dataModel 物件APIのデータモデル
     * @return 物件一覧の１物件の要素
     */
    public function createElement($shumoku, $dispModel, $dataModel, Params $params, $isNijiKoukokuJidou, $highlight, $fdp, $pageInitialSettings)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist.tpl";
        $html = file_get_contents($template_file);
        $doc = phpQuery::newDocument($html);

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        if (is_array($shumoku) && count($shumoku) === 1) {
            $shumoku = $shumoku[0];
        }

        if (!is_array($shumoku)) {
            // 単一種目

            $shumokuCt = Services\ServiceUtils::getShumokuCtByCd($shumoku);
            $bukkenElem = $doc["div." . $shumokuCt];

            switch ($shumoku)
            {
                case Services\ServiceUtils::TYPE_CHINTAI:
                    $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_KASI_TENPO:
                case Services\ServiceUtils::TYPE_KASI_OFFICE:
                    $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_PARKING:
                    $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_KASI_TOCHI:
                    $this->createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_KASI_OTHER:
                    $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_MANSION:
                    $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_KODATE:
                    $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_URI_TOCHI:
                    $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_URI_TENPO:
                case Services\ServiceUtils::TYPE_URI_OFFICE:
                    $this->createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Services\ServiceUtils::TYPE_URI_OTHER:
                    $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                default:
                    throw new \Exception('Illegal Argument.');
                    break;
            }

        } else {
            // 複合種目
            $compositeType = Estate\TypeList::getInstance()->getComopsiteTypeByShumokuCd($shumoku);
            switch ($compositeType) {
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_2:
                    // 貸店舗と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_TENPO);
                    $bukkenElem = $doc["div." . $shumokuCt];
                    $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, Services\ServiceUtils::TYPE_KASI_TENPO, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3:
                    // 貸その他と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_OTHER);
                    $bukkenElem = $doc["div." . $shumokuCt];
                    $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, Services\ServiceUtils::TYPE_KASI_OTHER, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2:
                    $bukkenElem = $doc["div.baibai-kyoju"];
                    $this->createBaibaiKyoju($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2:
                    // 売り店舗と同じ
                    $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_URI_TENPO);
                    $bukkenElem = $doc["div." . $shumokuCt];
                    $this->createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, Services\ServiceUtils::TYPE_URI_TENPO, $params, $isNijiKoukokuJidou, $highlight);
                    break;
            }
        }

        /**
         * パラメータの$shumokuは検索パラメータ（特集なら一覧描画用に選択された種目）なので、
         * 物件情報を元に種目を判定し、詳細URLを設定する
         */

        // ATHOME_HP_DEV-4841 : 第3引数として 利用中の種目一覧を追加
        // $detailUrl = Services\ServiceUtils::getDetailURL($dispModel, $dataModel);
        $detailUrl = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting);
        // ボタン　詳細
        // 4593: check anchor link detail
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', $detailUrl);
        // タイトル
        $bukkenElem['div.object-header p a']->attr('href', $detailUrl);

        $this->setUrlDetailFdp($bukkenElem, $dispModel, $dataModel, $detailUrl, $shumoku, $fdp, $pageInitialSettings);

        return $bukkenElem;
    }

    /**
     * お気に入り・最近見た物件の物件一覧の１物件の要素を作成して返します。
     *
     * @param $shumoku 物件種目のコード
     * @param $dispModel 物件APIの表示モデル
     * @param $dataModel 物件APIのデータモデル
     * @return 物件一覧の１物件の要素
     */
    public function createElementHachi($shumoku, $dispModel, $dataModel, Params $params, $isNijiKoukokuJidou, $pageInitialSettings, $searchSettings)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist.tpl";
        $html = file_get_contents($template_file);
        $doc = phpQuery::newDocument($html);

        $shumokuCt = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $bukkenElem = $doc["div." . $shumokuCt];

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_TENPO);
                $bukkenElem = $doc["div." . $shumokuCt];
                $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_KASI_OTHER);
                $bukkenElem = $doc["div." . $shumokuCt];
                $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $shumokuCt = Services\ServiceUtils::getShumokuCtByCd(Services\ServiceUtils::TYPE_URI_OTHER);
                $bukkenElem = $doc["div." . $shumokuCt];
                $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou, null, null ) ;
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
        }

        // 4676: FDPコラボページを表示できないリンクがある
        // ATHOME_HP_DEV-4841 : 第3引数として 利用中の種目一覧を追加
        $detailUrl = Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting);
        $fdp = $this->getFdpSettings($shumoku, $searchSettings);

        $this->setUrlDetailFdp($bukkenElem, $dispModel, $dataModel, $detailUrl, $shumoku, $fdp, $pageInitialSettings);

        return $bukkenElem;
    }


    private function createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  賃料・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihito', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  敷金・保証金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>' .
            $this->getVal('reikin', $dispModel) ;
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  間取り・面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);
        
        $notDisplayButHighlight = array('csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 貸店舗・オフィス
     */
    private function createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);

        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $title = Services\ServiceUtils::replaceSsiteBukkenTitle($title);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //賃料非表示
        $isKakakuHikokai = false;
        if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
            $isKakakuHikokai = true;
        }

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));

        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));


        //  賃料・管理費
        if( !$isKakakuHikokai ){
            $priceTxt = '<span class="price num">' .
                str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
                . '</span><span class="price">万円</span><br>'
                . $this->getVal('csite_kanrihito', $dispModel);
        }else{
            $priceTxt = '<span class="price num">' .$this->getVal('csite_kakaku', $dispModel).'</span>';
        }
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);

        //  敷金・保証金・礼金
        if( !$isKakakuHikokai ){
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel) . '/<br>' .
                $this->getVal('csite_hoshokin', $dispModel) . '/<br>' .
                $this->getVal('csite_reikin', $dispModel) ;
        }else {
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel);
        }
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);

        //  使用部分面積・坪数・坪単価
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>' .
            $this->getVal('tatemono_tsubo_su', $dispModel) . '／' .
            $this->getVal('csite_tsubo_tanka', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);

        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");

        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 貸駐車場
     */
    private function createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text($this->getVal('toho', $dispModel));
        //  賃料
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  敷金・保証金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  礼金
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($this->getVal('reikin', $dispModel));

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 貸土地
     */
    private function createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  賃料・坪単価
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('tsubo_tanka', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  敷金・保証金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>' .
            $this->getVal('reikin', $dispModel) ;
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  土地面積・（坪数）・最適用途
        $madoriTxt = $this->getVal('tochi_ms', $dispModel)
            . '<br>'
            . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）<br>' .
            $this->getVal('saiteki_yoto_nm', $dataModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  建ぺい率・容積率
        $shumokuTxt = $this->getVal('kenpei_ritsu', $dispModel)
            . '<br>' . $this->getVal('yoseki_ritsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 貸事業用一括・その他
     */
    private function createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  賃料・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihito', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  敷金・保証金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>' .
            $this->getVal('reikin', $dispModel) ;
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  使用部分面積・土地面積
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>'
            . $this->getVal('tochi_ms', $dispModel)
            . '<br>';
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 売マンション
     */
    private function createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));        
        //  価格
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  構造・階建／階
        $shiki_hosho_rei = $this->getVal('tatemono_kozo', $dispModel) . '<br>' .
            $this->getVal('csite_kaidate_kai', $dispModel) ;
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  間取り・面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 売戸建て
     */
    private function createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text($this->getVal('toho', $dispModel));
        //  価格
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  間取り・建物面積・土地面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel)
            . '<br>' . $this->getVal('tochi_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 売土地
     */
    private function createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));        
        //  賃料・坪単価
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('tsubo_tanka', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  土地面積・（坪数）・私道負担面積
        $madoriTxt = $this->getVal('tochi_ms', $dispModel) . '<br>'
            . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）<br>' .
            $this->getVal('csite_shido_futan_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($madoriTxt);
        //  引渡条件・最適用途
        $madoriTxt = $this->getHikiwatashi($dataModel)
            . '<br>'
            . $this->getVal('saiteki_yoto_nm', $dataModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  建ぺい率・容積率
        $shumokuTxt = $this->getVal('kenpei_ritsu', $dispModel)
            . '<br>' . $this->getVal('yoseki_ritsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
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

    /**
     * 売店舗・オフィス
     */
    private function createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $title = Services\ServiceUtils::replaceSsiteBukkenTitle($title);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  価格・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihi', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  使用部分面積・土地面積
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>'
            . $this->getVal('tochi_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($madoriTxt);
        //  用途地域・建物構造
        $madoriTxt = $this->getYotoChiiki($dataModel) . '<br>'
            . $this->getVal('tatemono_kozo', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 売事業用一括・その他
     */
    private function createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $title = Services\ServiceUtils::replaceSsiteBukkenTitle($title);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  価格・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihi', $dispModel);
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  使用部分面積・土地面積
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>'
            . $this->getVal('tochi_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($madoriTxt);
        //  用途地域・建物構造
        $madoriTxt = $this->getYotoChiiki($dataModel) . '<br>'
            . $this->getVal('tatemono_kozo', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel);
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　詳細
        $bukkenElem->find('div.object-r li.btn-detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 複合：売買居住
     */
    private function createBaibaiKyoju($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou, $highlight)
    {
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        // 物件データの種目
        $bukkenShumoku = Services\ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);
        $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['div.object-header p.object-name']->addClass('new');
        }
        // 物件タイトル
        $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $title = Services\ServiceUtils::replaceSsiteBukkenTitle($title);
        $bukkenElem['div.object-header p a']->text($title);
        // おすすめコメント
        $this->setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $bukkenShumoku);

        // object-data
        //  交通・所在地
        $bukkenElem->find('div.object-r td.cell1')->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem->find('div.object-r td.cell2')->text('')->append($this->getVal('toho', $dispModel));
        //  価格
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
        //  構造・階建／階
        $shiki_hosho_rei = $this->getVal('tatemono_kozo', $dispModel) . '<br>' .
            $this->getVal('csite_kaidate_kai', $dispModel) ;
        $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
        //  間取り・建物面積・土地面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel)
            . '<br>' . $this->getVal('tochi_ms', $dispModel);
        $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

        // ボタン　問い合わせ
        $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /** -------------------------------
     *  以降、サブルーチンメソッド
     */

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

    /**
     * 交通・所在地
     *   csite_kotsus　csite_shozaichi
     */
    private function getKotsuuShozaichi($dispModel)
    {
        // "沿線名 / 駅名"
        $kotsus = $this->getVal('csite_kotsus', $dispModel);
        $kotsusTxt = '<span class="bold">' . (isset($kotsus[0]) ? $kotsus[0] : '')
            . (isset($kotsus[1]) ? ' ' . $kotsus[1] : '')
            . '</span><br>'
            . $this->getVal('csite_shozaichi', $dispModel);
        return $kotsusTxt;
    }

    /**
     * 代表画像処理
     */
    private function createThumbnail($bukkenElem, $dispModel, $img_server, $params)
    {
        $thumbnail = Services\ServiceUtils::getMainImageForPC( $dispModel, $params ) ;
        if ( ( $thumbnail !== null ) && ( isset( $thumbnail->url ) ) ) {
        	$bukkenElem->find('figure.object-thumb img')
                ->attr('src', "")
                ->attr('data-original', $img_server . $thumbnail->url . "?width=100&amp;height=100&amp;margin=true");
            $bukkenElem->find('p.object-thumb-zoom img')
                ->attr('src', "")
                ->attr('data-original', $img_server . $thumbnail->url . "?width=320&amp;height=320&amp;margin=true");
        } else {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', "")
                ->attr('data-original', $img_server . "/image_files/path/no_image");
                // ->attr('data-original', "/pc/imgs/img_nophoto.gif");
            // remove zoom
            $bukkenElem->find('p.object-thumb-zoom')->remove();
        }
        $s_type = $params->getSearchType();
        if ($s_type == $params::SEARCH_TYPE_MAP) {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', $bukkenElem->find('figure.object-thumb img')->attr('data-original'));
        }
    }

    private function createIcon($bukkenElem, $dispModel, $dataModel, $shumoku)
    {
        $iconElem = $bukkenElem['div.object-l ul.icon-condition'];
        // パノラマムービー有り

		$movieStyle = 'border-radius:2px;border:solid 1px #519d64;padding:0px 6px;font-size:11px;color:#519d64;background:#daf5e0;display:inline;';
        switch( Services\ServiceUtils::getPanoramaType($dispModel) ) {
            case Services\ServiceUtils::PANORAMA_TYPE_MOVIE:
               $iconElem->append('<li><div style="'. $movieStyle . '">パノラマ</div></li>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_PHOTO:
               $iconElem->append('<li><div style="'. $movieStyle . '">フォトムービー</div></li>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_VR:
               $iconElem->append('<li><div style="'. $movieStyle . '">VR/パノラマ</div></li>');
               break;
            default:
               break;
        }

        // 新築
        if ($this->getVal('shinchiku_chuko_cd', $dispModel, true) == '1'
        		&&  (
        				$shumoku == Services\ServiceUtils::TYPE_CHINTAI ||
        				$shumoku == Services\ServiceUtils::TYPE_MANSION ||
        				$shumoku == Services\ServiceUtils::TYPE_KODATE
        				))
        {
        	$iconElem->append('<li><img src="/pc/imgs/icon_new_article.png" alt=""></li>');
        }
        // 写真充実　（物件件数１０点以上）
        if ($this->getVal('csite_shashin_jujitsu_fl', $dispModel, true)) {
            $iconElem->append('<li><img src="/pc/imgs/icon_photo_many.png" alt=""></li>');
        }
        // 未入居
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)
        		&&  (
        				$shumoku == Services\ServiceUtils::TYPE_CHINTAI ||
        				$shumoku == Services\ServiceUtils::TYPE_MANSION ||
        				$shumoku == Services\ServiceUtils::TYPE_KODATE
        				))
        {
        	$iconElem->append('<li><img src="/pc/imgs/icon_not_person.png" alt=""></li>');
        }
        // 建築条件付き土地アイコン
        if ($this->getVal('kenchiku_joken_tsuki_fl', $dataModel, true)) {
            $iconElem->append('<li><img src="/pc/imgs/icon_land.png" alt=""></li>');
        }
    }

    public static function renderAsideIcon($doc, $shumoku)
    {
    	$i = 0;
    	$iconElem = $doc['section.article-icon-explain ul.icon-list'];
    	$shumoku = (array) $shumoku;

        // 新着
    	if (true) {}
    	// 新築
    	if (
    			in_array(Services\ServiceUtils::TYPE_CHINTAI, $shumoku) ||
                in_array(Services\ServiceUtils::TYPE_MANSION, $shumoku) ||
                in_array(Services\ServiceUtils::TYPE_KODATE, $shumoku)
    		)
    	{
    	} else {
    		$iconElem['li.icon-new-article']->remove();
    	}
        // 未入居
    	if (
                in_array(Services\ServiceUtils::TYPE_CHINTAI, $shumoku) ||
                in_array(Services\ServiceUtils::TYPE_MANSION, $shumoku) ||
                in_array(Services\ServiceUtils::TYPE_KODATE, $shumoku)
    		)
    	{
    	} else {
    		$iconElem['li.icon-not-person']->remove();
    	}
        // パノラマムービー有り
        if (true) {}
    	// 写真充実　（物件件数１０点以上）
        if (true) {}
    }

    private function setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight) {
    	// おすすめコメント
        if ($highlight && isset($highlight->staff_comment)) {
            $bukkenElem->find('div.comment-pro dd')->text('')->append(Services\ServiceUtils::replaceStringHighlight($highlight->staff_comment, '…', '・・・'));
        } else {
            $procomment = $this->getVal('staff_comment', $dispModel, true);
            // ２次広告もしくは自動公開の場合は、おすすめコメントは表示しない。
            if ($isNijiKoukokuJidou) {
                $procomment = null;
            }
            if (is_null($procomment)) {
                $bukkenElem['div.comment-pro']->remove();
            } else {
                if (mb_strlen($procomment) > 40) {
                    $procomment = mb_substr($procomment, 0, 40) . '・・・';
                }
                $bukkenElem->find('div.comment-pro dd')->text($procomment);
            }
        }
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    private function renderHighlight($bukkenElem, $highlight, $dispModel = null, $notDisplayButHighlight = null) {
        $result = '';
        if ($dispModel && $notDisplayButHighlight) {
            $result .= $this->getNotDisplayButHighlight($dispModel, $notDisplayButHighlight);
        }
        foreach ($highlight as $key=>$value) {
            if ($key == 'images') {
                $result .= $this->getHighlightImageCaption($value);
            } elseif ($key == 'shuhen_kankyos') {
                $result .= $this->getHighlightShuhenKankyos($value);
            } else {
                $tilte = Models\HighlightItemList::getInstance()->get($key);
                if($tilte){
                    if ($key == 'pet') {
                        if($value == '<em>あり</em>') {
                            $result .= '<dl class="articlelist-side-heading2">ペット可</dl>';
                            continue;
                        }
                    }
                    else if ($key == 'hikiwatashi' && isset($dispModel->joi_shumoku_cd)) {
                        if ($dispModel->joi_shumoku_cd === '01') {
                            $tilte = '引渡し（可能時期/方法）';
                        } else if ($dispModel->joi_shumoku_cd === '06') {
                            $tilte = '入居可能時期';
                        }
                    }
                    if (is_array($value)) {
                        $text = $this->renderChild($value);
                    } else {
                        $text = $value;
                    }
                    if ($key == 'ippan_message_shosai') {
                        $text = Services\ServiceUtils::replaceStringHighlight($text, '…', '・・・');
                    }
                    $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.$text.'</dl>';
                }
            }
        }
        $bukkenElem->find('.highlightsArea .grad-item')->html($result);
    }

    private function renderChild($value) {
        $child = [];
        foreach ($value as $key=>$val) {
            if (is_array($val)) {
                $child[] = implode('　', $val);
            } else {
                $child[] = $val;
            }
        }
        return implode('　', $child);
    }
    private function getHighlightImageCaption($images) {
        $caption = array();
        $tilte = Models\HighlightItemList::getInstance()->get('images.caption');
        foreach ($images as $image) {
            if ($image) {
                $caption[] = $image['caption']; 
            }
        }
        return '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $caption).'</dl>';
    }
    private function getHighlightShuhenKankyos($shuhenKankyoss) {
        $caption = array();
        $nm = array();
        $shubetsu_nm = array();
        $result = '';
        foreach ($shuhenKankyoss as $shuhenKankyos) {
            if ($shuhenKankyos) {
                if(isset($shuhenKankyos['caption'])) {
                    $caption[] = $shuhenKankyos['caption'];
                }
                if(isset($shuhenKankyos['nm'])) {
                    $nm[] =  $shuhenKankyos['nm'];
                }
                if(isset($shuhenKankyos['shubetsu_nm'])) {
                    $shubetsu_nm[] = $shuhenKankyos['shubetsu_nm'];
                }
            }
        }
        if ($caption) {
            $tilte = Models\HighlightItemList::getInstance()->get('shuhen_kankyos.caption');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $caption).'</dl>';
        }
        if ($nm) {
            $tilte = Models\HighlightItemList::getInstance()->get('shuhen_kankyos.nm');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $nm).'</dl>';
        }
        if ($shubetsu_nm) {
            $tilte = Models\HighlightItemList::getInstance()->get('shuhen_kankyos.shubetsu_nm');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $shubetsu_nm).'</dl>';
        }
        return $result;
    }

    public function getNotDisplayButHighlight($dispModel, $notDisplayButHighlight) {
        $result = '';
        foreach ($notDisplayButHighlight as $item) {
            $text = $this->getVal($item, $dispModel);
            if (strpos($text, '<em>') > -1) {
                $tilte = Models\HighlightItemList::getInstance()->get($item);
                $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.$text.'</dl>';
            }
        }
        return $result;
    }

    private function setUrlDetailFdp($bukkenElem, $dispModel, $dataModel, $detailUrl, $shumoku, $fdp, $pageInitialSettings) {
        // 4689: Check lat, lon exist
        if (Services\ServiceUtils::isFDP($pageInitialSettings) && $fdp && (isset($dispModel->ido) && isset($dispModel->keido) && $dispModel->ido && $dispModel->keido)) {
            // 周辺情報を見る
            $bukken['data_model'] = $dataModel;
            $bukken['display_model'] = $dispModel;
            if ((Services\ServiceUtils::canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp) 
            && Services\ServiceUtils::canDisplayMap($pageInitialSettings,$bukken, $shumoku)) 
            || Services\ServiceUtils::canDisplayFdp(Estate\FdpType::ELEVATION_TYPE, $fdp)) {
                $bukkenElem['.links-list-fdp .btn-near-info a']->attr('href', "${detailUrl}map.html");
            } else {
                $bukkenElem['.links-list-fdp .btn-near-info']->remove();
            }
            // 街のこと（統計）を見る
            if (Services\ServiceUtils::canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp)) {
                $bukkenElem['.links-list-fdp .btn-chart-town a']->attr('href', "${detailUrl}townstats.html");
            } else {
                $bukkenElem['.links-list-fdp .btn-chart-town']->remove();
            }
        } else {
            $bukkenElem['.links-list-fdp']->remove();
        }
    }

    // 4676: FDPコラボページを表示できないリンクがある
    private function getFdpSettings($shumoku, $searchSettings) {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        if (is_array($type_ct)) {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
        } else {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
        }
        return json_decode($settingRow->display_fdp);
    }

}