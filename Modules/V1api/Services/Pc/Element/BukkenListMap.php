<?php
namespace Modules\V1api\Services\Pc\Element;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\HighlightItemList;
use phpQuery;

class BukkenListMap
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
        //$this->logger = Registry::get('logger');
        $this->logger = \Log::channel('debug');
    }

    /**
     * 物件一覧の１物件の要素を作成して返します。
     *
     * @param $shumoku 物件種目のコード
     * @param $dispModel 物件APIの表示モデル
     * @param $dataModel 物件APIのデータモデル
     * @param $params 呼び出しパラメータ:Params 
     * @param $isNijiKoukokuJidou
     * @param $highlight
     * @param $pageInitialSettings ページ初期設定値:PageInitialSettings
     * @return 物件一覧の１物件の要素
     */
    public function createElement($shumoku, $dispModel, $dataModel, Params $params, $isNijiKoukokuJidou, $highlight, $pageInitialSettings)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist_map.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);
        $bukkenElem = $doc['div.bl-item'];

        $shumokuCt = Services\ServiceUtils::getShumokuCtByCd($shumoku);

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
                $this->createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
                $this->createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, $isNijiKoukokuJidou);
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
        }

        /**
         * パラメータの$shumokuは検索パラメータ（特集なら一覧描画用に選択された種目）なので、
         * 物件情報を元に種目を判定し、詳細URLを設定する
         */
        // ボタン　詳細
        // ATHOME_HP_DEV-4841 : 第3引数として 利用中の種目一覧を追加
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting));
        if ($highlight) {
            $this->renderHighlight($bukkenElem, $highlight, $dispModel);
        } else {
            $bukkenElem['.highlightsArea']->remove();
        }

        return $bukkenElem;
    }

    private function createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);


        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        // object-data
        //  賃料・管理費
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span>（管理費等%s）</dd>'
            ,'賃料'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
            ,$this->getVal('kanrihito', $dispModel)
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  間取り・（面積）
        $tmp = sprintf('<dt class="area-name">%s</dt><dd class="area-tx">%s（%s）</dd>', '間取り', $this->getVal('madori', $dispModel), $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }

    /**
     * 貸店舗・オフィス
     */
    private function createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);


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


        //  賃料
        if( !$isKakakuHikokai ) {
            $tmp = sprintf(
                '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
                , '賃料'
                , str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
            );
        }else{
            $tmp = sprintf(
                '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span></span></dd>'
                , '賃料'
                , $this->getVal('csite_kakaku', $dispModel)
            );
        }
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  使用部分面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }

    /**
     * 貸駐車場
     */
    private function createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
            ,'賃料'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /**
     * 貸土地
     */
    private function createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);


        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
            ,'賃料'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  土地面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tochi_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /**
     * 貸事業用一括・その他
     */
    private function createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料・管理費
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span>（管理費等%s）</dd>'
            ,'賃料'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
            ,$this->getVal('kanrihito', $dispModel)
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  使用部分面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /**
     * 売マンション
     */
    private function createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
            ,'価格'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  間取り（面積）
        $tmp = sprintf('<dt class="area-name">%s</dt><dd class="area-tx">%s（%s）</dd>', '間取り', $this->getVal('madori', $dispModel), $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }

    /**
     * 売戸建て
     */
    private function createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
            ,'価格'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  間取り・（建物面積）
        $tmp = sprintf('<dt class="area-name">%s</dt><dd class="area-tx">%s（%s）</dd>', '間取り', $this->getVal('madori', $dispModel), $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /**
     * 売土地
     */
    private function createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格・坪単価
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span></dd>'
            ,'価格'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  土地面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tochi_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }

    /**
     * 売店舗・オフィス
     */
    private function createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格・管理費
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span>（管理費等%s）</dd>'
            ,'価格'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
            ,$this->getVal('kanrihito', $dispModel)
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  使用部分面積・土地面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /**
     * 売事業用一括・その他
     */
    private function createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem->attr('data-bukken-no', $dispModel->id);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格・管理費
        $tmp = sprintf(
            '<dt class="price-name">%s</dt><dd class="price-tx"><span class="bl-item__price"><span class="bl-item__price_num">%s</span>万円</span>（管理費等%s）</dd>'
            ,'価格'
            ,str_replace('万円', '', $this->getVal('csite_kakaku', $dispModel))
            ,$this->getVal('kanrihi', $dispModel)
        );
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  交通
        $tmp = $this->getKotsuu($dispModel);
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        //  使用部分面積
        $tmp = sprintf('<dt>%s</dt><dd class="area-tx">%s</dd>', '面積', $this->getVal('tatemono_ms', $dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);
        //  物件種目
        $tmp = sprintf('<dt class="kind-name">%s</dt><dd class="kind-tx">%s</dd>', '種目', Services\ServiceUtils::getShumokuDispModel($dispModel));
        $bukkenElem->find('dl.bl-item__detail')->append($tmp);

        // ボタン　詳細
        $bukkenElem->find('li.bl-item__btn_detail a')
            ->attr('href', "/${type_ct}/detail-" . $dispModel->id . "/");
    }


    /** -------------------------------
     *  以降、サブルーチンメソッド
     */

    /**
     * 交通
     *   csite_kotsus　csite_shozaichi
     */
    private function getKotsuu($dispModel)
    {
        // "沿線名 / 駅名"
        $kotsus = $this->getVal('csite_kotsus', $dispModel);
        if (isset($kotsus[0])) {
            return sprintf('<dt class="traffic-name">%s</dt><dd class="traffic-tx">%s</dd>', '交通', $kotsus[0]);
        } else {
            return sprintf('<dt class="traffic-name">%s</dt><dd class="traffic-tx">%s</dd>', '住所', $this->getVal('csite_shozaichi', $dispModel));
        }
    }

    /**
     * 代表画像処理
     */
    private function createThumbnail($bukkenElem, $dispModel, $img_server, $params)
    {
        $thumbnail = Services\ServiceUtils::getMainImageForPC( $dispModel, $params ) ;
        if (! is_null($thumbnail)) {
        	$bukkenElem->find('p.bl-item__ph img')
                ->attr('src', $img_server . $thumbnail->url . "?width=100&amp;height=100&amp;margin=true");
        } else {
            $bukkenElem->find('p.bl-item__ph img')
                ->attr('src', $img_server . "/image_files/path/no_image?width=100&amp;height=100");
        }
    }

    private function createIcon($bukkenElem, $dispModel, $dataModel, $shumoku)
    {
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['dl.bl-item__detail']->before('<div class="bl-item__ic new">新着</div>');
        }

        $iconElem = pq('<div>');
        // パノラマムービー有り
        $movieStyle = 'border-radius:2px;border:solid 1px #519d64;padding:1px 6px 0px 6px;font-size:11px;color:#519d64;background:#daf5e0;display:inline-block;height:17px;text-indent:0;vertical-align:top;text-align:center;';
        switch( Services\ServiceUtils::getPanoramaType($dispModel) ) {
            case Services\ServiceUtils::PANORAMA_TYPE_MOVIE:
               $iconElem->append('<div class="bl-item__ic movie" style="' . $movieStyle . '">パノラマ</div>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_PHOTO:
               $iconElem->append('<div class="bl-item__ic movie" style="' . $movieStyle . '">フォトムービー</div>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_VR:
               $iconElem->append('<div class="bl-item__ic movie" style="' . $movieStyle . '">VR/パノラマ</div>');
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
            $iconElem->append('<div class="bl-item__ic new-article">新築</div>');
        }
        // 写真充実　（物件件数１０点以上）
        if ($this->getVal('csite_shashin_jujitsu_fl', $dispModel, true)) {
            $iconElem->append('<div class="bl-item__ic photo">写真充実</div>');
        }
        // 未入居
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)
            &&  (
            $shumoku == Services\ServiceUtils::TYPE_CHINTAI ||
            $shumoku == Services\ServiceUtils::TYPE_MANSION ||
            $shumoku == Services\ServiceUtils::TYPE_KODATE
            ))
        {
            $iconElem->append('<div class="bl-item__ic not-person">未入居</div>');
        }
        // 建築条件付き土地アイコン
        if ($this->getVal('kenchiku_joken_tsuki_fl', $dataModel, true)) {
            $iconElem->append('<div class="bl-item__ic land">建築条件付き土地</div>');
        }
        $bukkenElem['dl.bl-item__detail']->after($iconElem['div']);
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    private function renderHighlight($bukkenElem, $highlight,$dispModel = null) {
        $result = '';
        if (isset($highlight->staff_comment)) {
            $result .= '<dl class="articlelist-side-heading2">'.Services\ServiceUtils::replaceStringHighlight($highlight->staff_comment, '…', '・・・').'</dl>';
        }
        foreach ($highlight as $key=>$value) {
            if ($key == 'staff_comment') continue;
            if ($key == 'images') {
                $result .= $this->getHighlightImageCaption($value);
            } elseif ($key == 'shuhen_kankyos') {
                $result .= $this->getHighlightShuhenKankyos($value);
            } else {
                $tilte = HighlightItemList::getInstance()->get($key);
                if($tilte){
                    if ($key == 'pet') {
                        if($value == '<em>あり</em>') {
                            $result .= '<dl class="articlelist-side-heading2">ペット可</dl>';
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
                        $text = Services\ServiceUtils::replaceStringHighlight($text, '…', '・・・');
                    }
                    $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.$text.'</dl>';
                }
            }
        }
        $bukkenElem->find('.highlightsArea .grad-item')->html($result);
    }

    private function renderChild($value) {
        $child = '';
        foreach ($value as $key=>$val) {
            if (is_array($val)) {
                $child = implode('  ', $val);
            } else {
                $child = $val;
            }
        }
        return $child;
    }
    private function getHighlightImageCaption($images) {
        $caption = array();
        $tilte = HighlightItemList::getInstance()->get('images.caption');
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
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.caption');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $caption).'</dl>';
        }
        if ($nm) {
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.nm');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $nm).'</dl>';
        }
        if ($shubetsu_nm) {
            $tilte = HighlightItemList::getInstance()->get('shuhen_kankyos.shubetsu_nm');
            $result .= '<dl class="articlelist-side-heading2"> 【'.$tilte.'】'.implode("　", $shubetsu_nm).'</dl>';
        }
        return $result;
    }

}