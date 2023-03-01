<?php
namespace Modules\V1api\Services\Sp\Element;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\HighlightItemList;
use Modules\V1api\Services\ServiceUtils;
use Library\Custom\Model\Estate;
use phpQuery;
class BukkenList
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

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
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist.sp.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        $bukkenElem = $doc;

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        if (is_array($shumoku) && count($shumoku) === 1) {
            $shumoku = $shumoku[0];
        }

        if (!is_array($shumoku)) {
            // 単一種目
            switch ($shumoku)
            {
                case ServiceUtils::TYPE_CHINTAI:
                    $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_KASI_TENPO:
                case ServiceUtils::TYPE_KASI_OFFICE:
                    $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_PARKING:
                    $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_KASI_TOCHI:
                    $this->createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_KASI_OTHER:
                    $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_MANSION:
                    $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_KODATE:
                    $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_URI_TOCHI:
                    $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_URI_TENPO:
                case ServiceUtils::TYPE_URI_OFFICE:
                    $this->createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case ServiceUtils::TYPE_URI_OTHER:
                    $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
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
                    // 貸店舗＋種目名
                    $this->createChintaiJigyo12($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3:
                    // 貸その他＋土地面積
                    $this->createChintaiJigyo3($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2:
                    // 戸建て＋種目名
                    $this->createBaibaiKyoju($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2:
                    // 売り店舗＋種目名
                    $this->createBaibaiJigyo($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou, $highlight);
                    break;
            }
        }


        /**
         * パラメータの$shumokuは検索パラメータ（特集なら一覧描画用に選択された種目）なので、
         * 物件情報を元に種目を判定し、詳細URLを設定する
         */
        // ボタン　詳細
        // ATHOME_HP_DEV-4841 : 第3引数として 利用中の種目一覧を追加
        $bukkenElem['div.object-body a:first']
            ->attr('href', ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting));

        /**
         * Icon FDP
         */
        $this->createIconFDP($bukkenElem, $dispModel, $dataModel, $shumoku, $fdp, $pageInitialSettings);

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
    public function createElementHachi($shumoku, $dispModel, $dataModel, Params $params, $fromInq = false, $isNijiKoukokuJidou, $pageInitialSettings, $searchSettings)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist.sp.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        $bukkenElem = $doc;

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        switch ($shumoku)
        {
            case ServiceUtils::TYPE_CHINTAI:
                $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null, null ) ;
                break;
            case ServiceUtils::TYPE_KASI_TENPO:
            case ServiceUtils::TYPE_KASI_OFFICE:
                $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_PARKING:
                $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_KASI_TOCHI:
            case ServiceUtils::TYPE_KASI_OTHER:
                $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_MANSION:
                $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_KODATE:
                $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_URI_TOCHI:
                $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            case ServiceUtils::TYPE_URI_TENPO:
            case ServiceUtils::TYPE_URI_OFFICE:
            case ServiceUtils::TYPE_URI_OTHER:
                $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $fromInq, $isNijiKoukokuJidou, null) ;
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
        }

        // 4676: FDPコラボページを表示できないリンクがある
        if($searchSettings){
            $fdp = $this->getFdpSettings($shumoku, $searchSettings);
            $this->createIconFDP($bukkenElem, $dispModel, $dataModel, $shumoku, $fdp, $pageInitialSettings);
        }

        return $bukkenElem;
    }

    private function createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");
        //  敷金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/' .
            $this->getVal('reikin', $dispModel) ;
        $dlElem->append('<dt>敷/礼：</dt>');
        $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $dlElem->append("<dd>". $this->getVal('madori', $dispModel) ."</dd>");
        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 貸店舗・オフィス
     */
    private function createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {

        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        if( !$isKakakuHikokai ) {
            $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
            $objectElem->append($priceTxt);
        }else{
            $dlElem = pq('<dl class="object-data" />');
            $dlElem->append('<dt>賃料：</dt>');
            $dlElem->append('<dd class="object-price" >'. $this->getVal('csite_kakaku', $dispModel) . '</dd>');
            $objectElem->append($dlElem);
        }


        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);
        //  管理費
        $kanrihito = $this->getVal('csite_kanrihito', $dispModel) ;
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $kanrihito ."</dd>");



        //  敷金・礼金
        if( !$isKakakuHikokai ) {
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel) . '/' .
                $this->getVal('csite_reikin', $dispModel) ;
            $dlElem->append('<dt>敷/礼：</dt>');
            $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        }else{
            $dlElem->append('<dt>敷/礼：</dt>');
            $dlElem->append("<dd>". $this->getVal('csite_shikikin', $dispModel) ."</dd>");

        }



        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 貸駐車場
     */
    private function createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  敷金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/' .
            $this->getVal('reikin', $dispModel) ;
        $dlElem->append('<dt>敷/礼：</dt>');
        $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);
        
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
    private function createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  敷金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/' .
            $this->getVal('reikin', $dispModel) ;
        $dlElem->append('<dt>敷/礼：</dt>');
        $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel)
            . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）</dd>');
        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 貸事業用一括・その他
     */
    private function createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");

        //  敷金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/' .
            $this->getVal('reikin', $dispModel) ;
        $dlElem->append('<dt>敷/礼：</dt>');
        $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 売マンション
     */
    private function createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $dlElem->append("<dd>". $this->getVal('madori', $dispModel) ."</dd>");
        // 専有
        $dlElem->append('<dt>専有：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel)."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori');
        if ($highlight) {
            $bukkenElem['.highlightsArea']->addClass('articlelist-side-section');
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 売戸建て
     */
    private function createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $dlElem->append("<dd>". $this->getVal('madori', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 建物
        $dlElem->append('<dt>建物：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 土地
        $dlElem->append('<dt>土地：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);
        
        $notDisplayButHighlight = array('csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 売土地
     */
    private function createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  坪単価
        $dlElem->append('<dt>坪単価：</dt>');
        $dlElem->append("<dd>". $this->getVal('tsubo_tanka', $dispModel) ."</dd>");
        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel)
            . '（' .$this->getVal('tochi_tsubo_su', $dispModel) . '）' ."</dd>");
        // 用途
        $dlElem->append('<dt>用途：</dt>');
        $dlElem->append("<dd>". $this->getVal('saiteki_yoto_nm', $dataModel) . "</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /**
     * 売店舗・オフィス
     */
    private function createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 使用部分
        $dlElem->append('<dt>使用部分：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel)."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 売事業用一括・その他
     */
    private function createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);
        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.object-body a:first']->attr('href', $detail_url);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 使用部分
        $dlElem->append('<dt>使用部分：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        // // ボタン　詳細
        // $bukkenElem->find('div.object-r li.btn-detail a')
        //     ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
        // // ボタン　問い合わせ
        // $bukkenElem['ul.btn li.btn-contact a']->attr('href', $inquiryURL);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 複合：賃貸事業①、②
     */
    private function createChintaiJigyo12($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        // 物件データの種目
        $bukkenShumoku = ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
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

        //賃料非表示
        $isKakakuHikokai = false;
        if ($this->getVal('kakaku_hikokai_fl', $dataModel, true)) {
            $isKakakuHikokai = true;
        }


        // object-data
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        if( !$isKakakuHikokai ) {
            $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
            $objectElem->append($priceTxt);
        }else{
            $dlElem = pq('<dl class="object-data" />');
            $dlElem->append('<dt>賃料：</dt>');
            $dlElem->append('<dd class="object-price" >'. $this->getVal('csite_kakaku', $dispModel) . '</dd>');
            $objectElem->append($dlElem);
        }


        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);
        //  管理費
        $kanrihito = $this->getVal('csite_kanrihito', $dispModel) ;
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $kanrihito ."</dd>");



        //  敷金・礼金
        if( !$isKakakuHikokai ) {
            $shiki_hosho_rei = $this->getVal('csite_shikikin', $dispModel) . '/' .
                $this->getVal('csite_reikin', $dispModel) ;
            $dlElem->append('<dt>敷/礼：</dt>');
            $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        }else{
            $dlElem->append('<dt>敷/礼：</dt>');
            $dlElem->append("<dd>". $this->getVal('csite_shikikin', $dispModel) ."</dd>");

        }



        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);


        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 複合：賃貸事業③
     */
    private function createChintaiJigyo3($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        // 物件データの種目
        $bukkenShumoku = ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");

        //  敷金・礼金
        $shiki_hosho_rei = $this->getVal('shikikin', $dispModel) . '/' .
            $this->getVal('reikin', $dispModel) ;
        $dlElem->append('<dt>敷/礼：</dt>');
        $dlElem->append("<dd>". $shiki_hosho_rei ."</dd>");
        // 面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 土地
        $dlElem->append('<dt>土地面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel) ."</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 複合：売買居住
     */
    private function createBaibaiKyoju($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        // 物件データの種目
        $bukkenShumoku = ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
        // 物件タイトル
        // $title = $this->getVal('csite_bukken_title', $dispModel, true);

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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $dlElem->append("<dd>". $this->getVal('madori', $dispModel) ."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 建物
        $dlElem->append('<dt>建物：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        // 土地
        $dlElem->append('<dt>土地：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel) ."</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }

    /**
     * 複合：売買事業
     */
    private function createBaibaiJigyo($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou, $highlight)
    {
        // 物件データの種目
        $bukkenShumoku = ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel);

        $bukkenElem['div.article-object']->attr('data-bukken-no', $dispModel->id);

        $checkboxElem = $bukkenElem['label.object-check'];
        $checkboxElem['input']->attr('name', 'bukken_id[]')->attr('value', $dispModel->id);
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
        $objectElem = pq('<div class="object-r" />');
        //  賃料
        $priceTxt = '<p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p>';
        $objectElem->append($priceTxt);

        $dlElem = pq('<dl class="object-data" />');
        $objectElem->append($dlElem);

        // 使用部分
        $dlElem->append('<dt>使用部分：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel)."</dd>");
        // 築年月
        $dlElem->append('<dt>築年月：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_chikunengetsu', $dispModel) . "</dd>");
        // 種目名
        $dlElem->append('<dt>種目名：</dt>');
        $dlElem->append("<dd>". ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");

        //  交通・所在地
        $this->createKotsuShozaichi($dispModel, $objectElem);

        $bukkenElem['div.object-r']->replaceWith($objectElem);

        $notDisplayButHighlight = array('madori', 'csite_kaidate_kai');
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel, $notDisplayButHighlight);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }
    }


    /** -------------------------------
     *  以降、サブルーチンメソッド
     */

    private function createKotsuShozaichi($dispModel, $objectElem)
    {
    	// NHP-897 スマホ版の表示は特殊仕様
    	// display_model：csite_kotsuのarray(0) に沿線・駅名
    	// display_model:toho に徒歩時間
    	// display_model：csite_kotsuのarray(1) にバス情報
    	$cKotsus = $dispModel->csite_kotsus;
    	$lineTxt = isset($cKotsus[0]) ? $cKotsus[0] : '';
//     	$kotsus = isset($dispModel->kotsus) ? $dispModel->kotsus : null;
//     	if ($kotsus) {
//     		$tohoTxt = isset($kotsus[0]) && isset($kotsus[0]['toho']) ?
//     		' ' .$kotsus[0]['toho'] : '';
//     	} else {
//     		$tohoTxt = null;
//     	}
    	$tohoTxt = isset($dispModel->toho) ? ' ' .$this->getVal('toho', $dispModel, true) : null;

    	$busTxt = isset($cKotsus[1]) ? ' ' . $cKotsus[1] : '';

    	$kotsusTxt = $lineTxt . $tohoTxt . $busTxt;
        $kotsusElem = '<p class="object-traffic">' . $kotsusTxt . '</p>';
        $objectElem->append($kotsusElem);
        $shozaiTxt = '<p class="object-address">' . $this->getVal('csite_shozaichi', $dispModel) . '</p>';
        $objectElem->append($shozaiTxt);
    }

    private function createIcon($bukkenElem, $dispModel, $dataModel, $shumoku)
    {
        $iconElem = $bukkenElem['div.object-l ul.icon-condition'];
        // 新着
        if ($this->getVal('new_mark_fl_for_c', $dispModel, true)) {
            $iconElem->append('<li><img src="/sp/imgs/icon_new.png" alt=""></li>');
        }
        // パノラマムービー有り
        $movieStyle = 'border-radius:2px;border:solid 1px #519d64;padding:2px auto;font-size:10px;color:#519d64;background:#daf5e0;text-align:center;';
        switch( ServiceUtils::getPanoramaType($dispModel) ) {
            case ServiceUtils::PANORAMA_TYPE_MOVIE:
               $iconElem->append('<li><div style="'. $movieStyle . '">パノラマ</div></li>');
               break;
            case ServiceUtils::PANORAMA_TYPE_PHOTO:
               $iconElem->append('<li><div style="'. $movieStyle . '">フォトムービー</div></li>');
               break;
            case ServiceUtils::PANORAMA_TYPE_VR:
               $iconElem->append('<li><div style="'. $movieStyle . '">VR/パノラマ</div></li>');
               break;
            default:
               break;
        }

        // 新築
        if ($this->getVal('shinchiku_chuko_cd', $dispModel, true) == '1'
        		&&  (
        				$shumoku == ServiceUtils::TYPE_CHINTAI ||
        				$shumoku == ServiceUtils::TYPE_MANSION ||
        				$shumoku == ServiceUtils::TYPE_KODATE
        				))
        {
        	$iconElem->append('<li><img src="/sp/imgs/icon_new_article.png" alt=""></li>');
        }
        // 写真充実　（物件件数１０点以上）
        if ($this->getVal('csite_shashin_jujitsu_fl', $dispModel, true)) {
        	$iconElem->append('<li><img src="/sp/imgs/icon_photo_many.png" alt=""></li>');
        }
        // 未入居
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)
        		&&  (
        				$shumoku == ServiceUtils::TYPE_CHINTAI ||
        				$shumoku == ServiceUtils::TYPE_MANSION ||
        				$shumoku == ServiceUtils::TYPE_KODATE
        				))
        {
        	$iconElem->append('<li><img src="/sp/imgs/icon_not_person.png" alt=""></li>');
        }
        // 建築条件付き土地アイコン
        if ($this->getVal('kenchiku_joken_tsuki_fl', $dataModel, true)) {
            $iconElem->append('<li><img src="/sp/imgs/icon_land.png" alt=""></li>');
        }
    }

    /**
     * 代表画像処理
     */
    private function createThumbnail($bukkenElem, $dispModel, $img_server, $params)
    {
        $thumbnail = ServiceUtils::getMainImageForSP($dispModel, $params);
        if (! is_null($thumbnail)) {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', $this->getImgDomain($img_server) . $thumbnail->url. '?width=320&amp;height=320');
        } else {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', $this->getImgDomain($img_server) . "/image_files/path/no_image?width=320&amp;height=320");
        }
    }

    private function setProComment($bukkenElem, $dispModel, $isNijiKoukokuJidou, $highlight) {
        // おすすめコメント
        if ($highlight && isset($highlight->staff_comment)) {
            $bukkenElem->find('div.comment-pro dd')->text('')->append(ServiceUtils::replaceStringHighlight($highlight->staff_comment, '…', '・・・'));
        } else {
            $procomment = $this->getVal('staff_comment', $dispModel, true);
            // ２次広告の場合は、おすすめコメントは表示しない。
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
        return ServiceUtils::getVal($name, $stdClass, $null);
    }

    private function getImgDomain($img_server) {

        if ((boolean)$this->_config->img_can_use_https) {
            return str_replace('http:', '', $img_server);
        }

        return $img_server;
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
                $tilte = HighlightItemList::getInstance()->get($key);
                if($tilte){
                    if ($key == 'pet') {
                        if($value == '<em>あり</em>') {
                            $result .= '<dl class="heading-article-lv2">ペット可</dl>';
                            continue;
                        }
                    } else if ($key == 'hikiwatashi' && isset($dispModel->joi_shumoku_cd)) {
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
                        $text = ServiceUtils::replaceStringHighlight($text, '…', '・・・');
                    }
                    $result .= '<dl class="heading-article-lv2"> 【'.$tilte.'】'.$text.'</dl>';
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
        $tilte = HighlightItemList::getInstance()->get('images.caption');
        foreach ($images as $image) {
            if ($image) {
                $caption[] = $image['caption']; 
            }
        }
        return '<dl class="heading-article-lv2"> 【'.$tilte.'】'.implode('　', $caption).'</dl>';
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
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.caption');
            $result .= '<dl class="heading-article-lv2"> 【'.$tilte.'】'.implode('　', $caption).'</dl>';
        }
        if ($nm) {
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.nm');
            $result .= '<dl class="heading-article-lv2"> 【'.$tilte.'】'.implode('　', $nm).'</dl>';
        }
        if ($shubetsu_nm) {
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.shubetsu_nm');
            $result .= '<dl class="heading-article-lv2"> 【'.$tilte.'】'.implode('　', $shubetsu_nm).'</dl>';
        }
        return $result;
    }

    public function getNotDisplayButHighlight($dispModel, $notDisplayButHighlight) {
        $result = '';
        foreach ($notDisplayButHighlight as $item) {
            $text = $this->getVal($item, $dispModel);
            if (strpos($text, '<em>') > -1) {
                $tilte = HighlightItemList::getInstance()->get($item);
                $result .= '<dl class="heading-article-lv2"> 【'.$tilte.'】'.$text.'</dl>';
            }
        }
        return $result;
    }

    private function createIconFDP($bukkenElem, $dispModel, $dataModel, $shumoku, $fdp, $pageInitialSettings) {
        // fdp
        // 4689: Check lat, lon exist
        if (ServiceUtils::isFDP($pageInitialSettings) && $fdp && (isset($dispModel->ido) && isset($dispModel->keido) && $dispModel->ido && $dispModel->keido)) {
            $iconElem = $bukkenElem['div.object-l ul.icon-condition'];
            $bukken['data_model'] = $dataModel;
            $bukken['display_model'] = $dispModel;

            if (ServiceUtils::canDisplayFdp(Estate\FdpType::FACILITY_INFORMATION_TYPE, $fdp)
            && ServiceUtils::canDisplayMap($pageInitialSettings, $bukken, $shumoku)) {
                $iconElem->append('<li class="fdp-list">周辺情報あり</li>');
            }

            if (ServiceUtils::canDisplayFdp(Estate\FdpType::ELEVATION_TYPE, $fdp)) {
                $iconElem->append('<li class="fdp-list">道のり情報あり</li>');
            }

            if (ServiceUtils::canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp)) {
                $iconElem->append('<li class="fdp-list">統計情報あり</li>');
            }
        }
    }

    // 4676: FDPコラボページを表示できないリンクがある
    private function getFdpSettings($shumoku, $searchSettings) {
        $type_ct = ServiceUtils::getShumokuCtByCd($shumoku);
        if (is_array($type_ct)) {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct[0])->toSettingObject();
        } else {
            $settingRow = $searchSettings->getSearchSettingRowByTypeCt($type_ct)->toSettingObject();
        }
        return json_decode($settingRow->display_fdp);
    }
}