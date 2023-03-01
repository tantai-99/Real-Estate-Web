<?php
namespace Modules\V1api\Services\Pc\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;

class BukkenListInq
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
     * お問い合わせ物件の物件一覧の１物件の要素を作成して返します。
     *
     * @param $shumoku 物件種目のコード
     * @param $dispModel 物件APIの表示モデル
     * @param $dataModel 物件APIのデータモデル
     * @return 物件一覧の１物件の要素
     */
    public function createElementHachi($shumoku, $dispModel, $dataModel, Params $params)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist_inq.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $bukkenElem = $doc["table." . 'rent'];
                $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $bukkenElem = $doc["table." . 'office'];
                $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $bukkenElem = $doc["table." . 'parking'];
                $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $bukkenElem = $doc["table." . 'others'];
                $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $bukkenElem = $doc["table." . 'mansion'];
                $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $bukkenElem = $doc["table." . 'house'];
                $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $bukkenElem = $doc["table." . 'land'];
                $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $bukkenElem = $doc["table." . 'business'];
                $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params);
                break;
            default:
                throw new Exception('Illegal Argument.');
                break;
        }

        return $bukkenElem['tr'];
    }

    private function createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));

        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  賃料・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihito', $dispModel);
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  敷金・保証金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>' .
            $this->getVal('reikin', $dispModel) ;
        $bukkenElem['tr td:eq(5)']->text('')->append($shiki_hosho_rei);
        //  間取り・面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel);
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
    }

    /**
     * 貸店舗・オフィス
     */
    private function createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        //賃料非表示
        $isKakakuHikokai = false;
        if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
            $isKakakuHikokai = true;
        }


        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));

        //  賃料・管理費
        if( !$isKakakuHikokai ){
            $priceTxt = '<span class="price num">' .
                str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
                . '</span><span class="price">万円</span><br>'
                . $this->getVal('csite_kanrihito', $dispModel);
        }else{
            $priceTxt = '<span class="price num">' .$this->getVal('csite_kakaku', $dispModel).'</span>';
        }
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);

        //  敷金・保証金・礼金
        if( !$isKakakuHikokai ){
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel) . '/<br>' .
                $this->getVal('csite_hoshokin', $dispModel) . '/<br>' .
                $this->getVal('csite_reikin', $dispModel) ;
        }else{
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel);
        }
        $bukkenElem['tr td:eq(5)']->text('')->append($shiki_hosho_rei);


        //  使用部分面積・坪数・坪単価
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>' .
            $this->getVal('tatemono_tsubo_su', $dispModel) . '／' .
            $this->getVal('csite_tsubo_tanka', $dispModel);
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);

        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
    }

    /**
     * 貸駐車場
     */
    private function createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  賃料
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  敷金・保証金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>';
        $bukkenElem['tr td:eq(5)']->text('')->append($shiki_hosho_rei);
        //  礼金
        $bukkenElem['tr td:eq(6)']->text('')->append($this->getVal('reikin', $dispModel));
    }


    // /**
    //  * 貸土地
    //  */
    // private function createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    // {
    //     $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
    //     $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

    //     $bukkenElem->attr('data-bukken-no', $dispModel->id);
    //     $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
    //     $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
    //     // 新着
    //     if ($dispModel->new_mark_fl_for_c) {
    //         $bukkenElem['div.object-header p.object-name']->addClass('new');
    //     }
    //     // 物件タイトル
    //     $title = $this->getVal('csite_bukken_title', $dispModel, true);
    //     $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
    //     $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
    //     // おすすめコメント
    //     $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
    //     if (is_null($procomment)) {
    //         $bukkenElem['div.comment-pro']->remove();
    //     } else {
    //         if (mb_strlen($procomment) > 40) {
    //             $procomment = mb_substr($procomment, 0, 40) . '・・・';
    //         }
    //         $bukkenElem->find('div.comment-pro dd')->text($procomment);
    //     }

    //     /*
    //      * 画像処理
    //      */
    //     $this->createThumbnail($bukkenElem, $dataModel, $img_server, $params);


    //     // object-data
    //     //  交通・所在地
    //     //  @TODO csite_kotsus　csite_shozaichi に入れ替える
    //     $kotsus = (object) $dispModel->kotsus[0];
    //     $kotsusTxt = '<span class="bold">' . $this->getVal('eki_nm', $kotsus)
    //         . '/' . $this->getVal('ensen_nm', $kotsus) . '</span><br>'
    //         . $this->getVal('shozaichi', $dispModel);
    //     $bukkenElem->find('div.object-r td.cell1')->text('')->append($kotsusTxt);
    //     //  徒歩
    //     $bukkenElem->find('div.object-r td.cell2')->text($this->getVal('toho', $dispModel));
    //     //  賃料・坪単価
    //     $priceTxt = '<span class="price num">' .
    //         str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
    //         . '</span><span class="price">万円</span><br>'
    //         . $this->getVal('tsubo_tanka', $dispModel);
    //     $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
    //     //  敷金・保証金・礼金
    //     $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
    //         $this->getVal('hoshokin', $dispModel) . '/<br>' .
    //         $this->getVal('reikin', $dispModel) ;
    //     $bukkenElem->find('div.object-r td.cell4')->text('')->append($shiki_hosho_rei);
    //     //  土地面積・（坪数）・最適用途
    //     $madoriTxt = $this->getVal('tochi_ms', $dispModel)
    //         . '<sup>2</sup>' . '<br>'
    //         . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）<br>' .
    //         $this->getVal('saiteki_yoto_nm', $dispModel);
    //     $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
    //     //  建ぺい率・容積率
    //     $shumokuTxt = $this->getVal('kenpei_ritsu', $dispModel)
    //         . '<br>' . $this->getVal('yoseki_ritsu', $dispModel) . '<br>';
    //     $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

    //     // ボタン　詳細
    //     $bukkenElem->find('div.object-r li.btn-detail a')
    //         ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    //     // ボタン　問い合わせ
    //     $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);
    // }


    /**
     * 貸事業用一括・その他
     */
    private function createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  賃料・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihito', $dispModel);
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  敷金・保証金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/<br>' .
            $this->getVal('hoshokin', $dispModel) . '/<br>' .
            $this->getVal('reikin', $dispModel) ;
        $bukkenElem['tr td:eq(5)']->text('')->append($shiki_hosho_rei);
        //  使用部分面積・土地面積
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>'
            . $this->getVal('tochi_ms', $dispModel)
            . '<br>';
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
    }


    /**
     * 売マンション
     */
    private function createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  価格
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  構造・階建／階4
        $shiki_hosho_rei = $this->getVal('tatemono_kozo', $dispModel) . '<br>' .
            $this->getVal('csite_kaidate_kai', $dispModel) ;
        $bukkenElem['tr td:eq(5)']->text('')->append($shiki_hosho_rei);
        //  間取り・面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel);
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
    }

    /**
     * 売戸建て
     */
    private function createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  価格
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>';
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  間取り・建物面積・土地面積
        $madoriTxt = $this->getVal('madori', $dispModel)
            . '<br>' . $this->getVal('tatemono_ms', $dispModel)
            . '<br>' . $this->getVal('tochi_ms', $dispModel);
        $bukkenElem['tr td:eq(5)']->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(6)']->text('')->append($shumokuTxt);
    }


    /**
     * 売土地
     */
    private function createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  賃料・坪単価
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('tsubo_tanka', $dispModel);
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  土地面積・（坪数）・私道負担面積
        $madoriTxt = $this->getVal('tochi_ms_tochi_tsubo_su', $dispModel) . '<br>'
            . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）<br>' .
            $this->getVal('csite_shido_futan_ms', $dispModel);
        $bukkenElem['tr td:eq(5)']->text('')->append($madoriTxt);
        //  引渡条件・最適用途
        $madoriTxt = $this->getHikiwatashi($dataModel)
            . '<br>'
            . $this->getVal('saiteki_yoto_nm', $dataModel);
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);
        //  建ぺい率・容積率
        $shumokuTxt = $this->getVal('kenpei_ritsu', $dispModel)
            . '<br>' . $this->getVal('yoseki_ritsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
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
    
    // /**
    //  * 売店舗・オフィス
    //  */
    // private function createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    // {
    //     $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
    //     $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

    //     $bukkenElem->attr('data-bukken-no', $dispModel->id);
    //     $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
    //     $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
    //     // 新着
    //     if ($dispModel->new_mark_fl_for_c) {
    //         $bukkenElem['div.object-header p.object-name']->addClass('new');
    //     }
    //     // 物件タイトル
    //     $title = $this->getVal('csite_bukken_title', $dispModel, true);
    //     $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
    //     $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
    //     // おすすめコメント
    //     $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
    //     if (is_null($procomment)) {
    //         $bukkenElem['div.comment-pro']->remove();
    //     } else {
    //         if (mb_strlen($procomment) > 40) {
    //             $procomment = mb_substr($procomment, 0, 40) . '・・・';
    //         }
    //         $bukkenElem->find('div.comment-pro dd')->text($procomment);
    //     }
        
    //     /*
    //      * 画像処理
    //      */
    //     $this->createThumbnail($bukkenElem, $dataModel, $img_server, $params);
        

    //     // object-data
    //     //  交通・所在地
    //     //  @TODO csite_kotsus　csite_shozaichi に入れ替える
    //     $kotsus = (object) $dispModel->kotsus[0];
    //     $kotsusTxt = '<span class="bold">' . $this->getVal('eki_nm', $kotsus)
    //         . '/' . $this->getVal('ensen_nm', $kotsus) . '</span><br>'
    //         . $this->getVal('shozaichi', $dispModel);
    //     $bukkenElem->find('div.object-r td.cell1')->text('')->append($kotsusTxt);
    //     //  徒歩
    //     $bukkenElem->find('div.object-r td.cell2')->text($this->getVal('toho', $dispModel));
    //     //  価格・管理費
    //     $priceTxt = '<span class="price num">' .
    //         str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
    //         . '</span><span class="price">万円</span><br>'
    //         . $this->getVal('kanrihito', $dispModel);
    //     $bukkenElem->find('div.object-r td.cell3')->text('')->append($priceTxt);
    //     //  使用部分面積・土地面積
    //     $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
    //         . '<sup>2</sup>' . '<br>'
    //         . $this->getVal('tochi_ms', $dispModel)
    //         . '<sup>2</sup>' . '<br>';
    //     $bukkenElem->find('div.object-r td.cell4')->text('')->append($madoriTxt);
    //     //  用途地域・建物構造
    //     $madoriTxt = $this->getVal('yoto_chiiki_nm', $dispModel) . '<br>'
    //         . $this->getVal('tatemono_kozo', $dispModel) . '<br>';
    //     $bukkenElem->find('div.object-r td.cell5')->text('')->append($madoriTxt);
    //     //  物件種目・築年月
    //     $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
    //         . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
    //     $bukkenElem->find('div.object-r td.cell6')->text('')->append($shumokuTxt);

    //     // ボタン　詳細
    //     $bukkenElem->find('div.object-r li.btn-detail a')
    //         ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    //     // ボタン　問い合わせ
    //     $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);
    // }


    /**
     * 売事業用一括・その他
     */
    private function createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params)
    {
        // $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        // $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $checkboxElem = $bukkenElem['tr td:eq(0)'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);

        // $bukkenElem->attr('data-bukken-no', $dispModel->id);
        // $bukkenElem->find('div.object-header label input')->attr('id','bk-'. $dispModel->id);
        // $bukkenElem->find('div.object-header label')->attr('for', 'bk-'. $dispModel->id);
        // // 新着
        // if ($dispModel->new_mark_fl_for_c) {
        //     $bukkenElem['div.object-header p.object-name']->addClass('new');
        // }
        // // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        // $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        // $bukkenElem['div.object-header p a']->attr('href', $detail_url)->text($title);
        // // おすすめコメント
        // $procomment = $this->getVal('ippan_kokai_message', $dataModel, true);
        // if (is_null($procomment)) {
        //     $bukkenElem['div.comment-pro']->remove();
        // } else {
        //     if (mb_strlen($procomment) > 40) {
        //         $procomment = mb_substr($procomment, 0, 40) . '・・・';
        //     }
        //     $bukkenElem->find('div.comment-pro dd')->text($procomment);
        // }
        
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);
        

        // object-data
        //  交通・所在地
        $bukkenElem['tr td:eq(2)']->text('')->append($this->getKotsuuShozaichi($dispModel));
        //  徒歩
        $bukkenElem['tr td:eq(3)']->text($this->getVal('toho', $dispModel));
        //  価格・管理費
        $priceTxt = '<span class="price num">' .
            str_replace('万円','', $this->getVal('csite_kakaku', $dispModel))
            . '</span><span class="price">万円</span><br>'
            . $this->getVal('kanrihi', $dispModel);
        $bukkenElem['tr td:eq(4)']->text('')->append($priceTxt);
        //  使用部分面積・土地面積
        $madoriTxt = $this->getVal('tatemono_ms', $dispModel)
            . '<br>'
            . $this->getVal('tochi_ms', $dispModel)
            . '<br>';
        $bukkenElem['tr td:eq(5)']->text('')->append($madoriTxt);
        //  用途地域・建物構造
        $madoriTxt = $this->getYotoChiiki($dataModel) . '<br>'
            . $this->getVal('tatemono_kozo', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(6)']->text('')->append($madoriTxt);
        //  物件種目・築年月
        $shumokuTxt = Services\ServiceUtils::getShumokuDispModel($dispModel)
            . '<br>' . $this->getVal('csite_chikunengetsu', $dispModel) . '<br>';
        $bukkenElem['tr td:eq(7)']->text('')->append($shumokuTxt);
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
        $kotsus = $this->getVal('csite_kotsus', $dispModel);
        $kotsusTxt = '<span class="bold">' . (isset($kotsus[0]) ? $kotsus[0] : '')
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

        $img_domain = $this->getImgDomain($img_server);
        if (!is_null($thumbnail)) {
            $imgElem = $bukkenElem['tr td img'];
            $imgElem->attr('src', $img_domain.$thumbnail->url."?width=100&amp;height=100&amp;margin=true");
        }
        else {
            $imgElem = $bukkenElem['tr td img'];
            $imgElem->attr('src', $img_domain."/image_files/path/no_image");
        }
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    /**
     * img[src]
     * - 環境ごとにプロトコル切り替え
     *
     * @param $img_server
     * @return string
     */
    private function getImgDomain($img_server) {

        if ((boolean)$this->_config->img_can_use_https) {
            return str_replace('http:', '', $img_server);
        }

        return $img_server;
    }

}