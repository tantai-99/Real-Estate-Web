<?php
namespace Modules\V1api\Services\Sp\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

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
     * @param $pageInitialSettings ページ初期設定値:PageInitialSettings
     * @return 物件一覧の１物件の要素
     */
    public function createElement($shumoku, $dispModel, $dataModel, Params $params, $isNijiKoukokuJidou, $pageInitialSettings)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/bukkenlist_map.sp.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        $bukkenElem = $doc;

        // 環境によるイメージサーバの切り替え
        $img_server = $this->_config->img_server;

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $this->createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $this->createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $this->createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
                $this->createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $this->createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $this->createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $this->createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $this->createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
                $this->createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
                break;
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $this->createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, $params, false, $isNijiKoukokuJidou);
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
        $bukkenElem['div.bl-item a:first']
            ->attr('href', Services\ServiceUtils::getDetailURL($dispModel, $dataModel, $pageInitialSettings->searchSetting));

        return $bukkenElem;
    }

    private function createChintai($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));

        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");
        //  間取り・（面積）
        $dlElem->append('<dt>間取り：</dt>');
        $tmp = sprintf('%s（%s）', $this->getVal('madori', $dispModel) ,$this->getVal('tatemono_ms', $dispModel));
        $dlElem->append("<dd>". $tmp ."</dd>");
        //  種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }

    /**
     * 貸店舗・オフィス
     */
    private function createKasiTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

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
            $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        }else{
            $bukkenElem['.object-price']->remove();
            $dlElem = $bukkenElem['.object-data'];
            $dlElem->append('<dt>賃料：</dt>');
            $dlElem->append('<dd><p class="object-price">' . $this->getVal('csite_kakaku', $dispModel) . '</p></dd>');
        }


        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('csite_kanrihito', $dispModel) ."</dd>");
        //  使用部分面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }

    /**
     * 貸駐車場
     */
    private function createParking($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));

        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }


    /**
     * 貸土地
     */
    private function createKasiTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        //  土地面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tochi_ms', $dispModel) ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }


    /**
     * 貸事業用一括・その他
     */
    private function createKasiOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-r']->append($this->createKotsu($dispModel, $bukkenElem));

        $dlElem = $bukkenElem['.object-data'];
        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");
        //  使用部分面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }

    /**
     * 売マンション
     */
    private function createMansion($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $tmp = sprintf('%s（%s）', $this->getVal('madori', $dispModel) ,$this->getVal('tatemono_ms', $dispModel));
        $dlElem->append("<dd>". $tmp ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }

    /**
     * 売戸建て
     */
    private function createKodate($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        //  間取り
        $dlElem->append('<dt>間取り：</dt>');
        $tmp = sprintf('%s（%s）', $this->getVal('madori', $dispModel), $this->getVal('tatemono_ms', $dispModel));
        $dlElem->append("<dd>". $tmp ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }


    /**
     * 売土地
     */
    private function createUriTochi($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  賃料
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }


    /**
     * 売店舗・オフィス
     */
    private function createUriTenpoOffice($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);

        //  価格
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");
        //  使用部分面積・土地面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel));
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }

    /**
     * 売事業用一括・その他
     */
    private function createUriOther($bukkenElem, $dispModel, $dataModel, $img_server, $shumoku, Params $params, $fromInq = false, $isNijiKoukokuJidou)
    {
        $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
        $inquiryURL = Services\ServiceUtils::getInquiryURL($shumoku);

        $bukkenElem['div.bl-item']->attr('data-bukken-no', $dispModel->id);

        $detail_url = "/${type_ct}/detail-" . $dispModel->id . "/";
        $bukkenElem['div.bl-item a:first']->attr('href', $detail_url);

        /*
         * 画像処理
         */
        $this->createThumbnail($bukkenElem, $dispModel, $img_server, $params);

        /*
         * アイコン
         */
        $this->createIcon($bukkenElem, $dispModel, $dataModel, $shumoku);


        //  価格
        $bukkenElem['.object-price']->text($this->getVal('csite_kakaku', $dispModel));
        //  交通・所在地
        $bukkenElem['.object-data']->after($this->createKotsu($dispModel));

        $dlElem = $bukkenElem['.object-data'];
        // 管理費等
        $dlElem->append('<dt>管理費等：</dt>');
        $dlElem->append("<dd>". $this->getVal('kanrihito', $dispModel) ."</dd>");
        //  使用部分面積
        $dlElem->append('<dt>面積：</dt>');
        $dlElem->append("<dd>". $this->getVal('tatemono_ms', $dispModel) ."</dd>");
        //  物件種目
        $dlElem->append('<dt>種目：</dt>');
        $dlElem->append("<dd>". Services\ServiceUtils::getShumokuDispModel($dispModel) ."</dd>");
    }


    /** -------------------------------
     *  以降、サブルーチンメソッド
     */

    private function createKotsu($dispModel)
    {
        // "沿線名 / 駅名"
        $kotsus = $this->getVal('csite_kotsus', $dispModel);
        if (isset($kotsus[0])) {
            return sprintf('<p class="object-traffic">%s</p>', $kotsus[0]);
        } else {
            return sprintf('<p class="object-address">%s</p>', $this->getVal('csite_shozaichi', $dispModel));
        }
    }

    private function createIcon($bukkenElem, $dispModel, $dataModel, $shumoku)
    {
        // 新着
        if ($dispModel->new_mark_fl_for_c) {
            $bukkenElem['ul.icon-condition']->append('<li><img src="/sp/imgs/icon_new.png" alt=""></li>');
        }

        // パノラマムービー有り
        $movieStyle = 'border-radius:2px;border:solid 1px #519d64;padding:1px 0 0 0;font-size:9px;color:#519d64;background:#daf5e0;text-align:center;';
        switch( Services\ServiceUtils::getPanoramaType($dispModel) ) {
            case Services\ServiceUtils::PANORAMA_TYPE_MOVIE:
               $bukkenElem['ul.icon-condition']->append('<li><div style="'. $movieStyle . '">パノラマ</div></li>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_PHOTO:
               $bukkenElem['ul.icon-condition']->append('<li><div style="'. $movieStyle . '">フォトムービー</div></li>');
               break;
            case Services\ServiceUtils::PANORAMA_TYPE_VR:
               $bukkenElem['ul.icon-condition']->append('<li><div style="'. $movieStyle . '">VR/パノラマ</div></li>');
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
            $bukkenElem['ul.icon-condition']->append('<li><img src="/sp/imgs/icon_new_article.png" alt=""></li>');
        }
        // 写真充実　（物件件数１０点以上）
        if ($this->getVal('csite_shashin_jujitsu_fl', $dispModel, true)) {
            $bukkenElem['ul.icon-condition']->append('<li><img src="/sp/imgs/icon_photo_many.png" alt=""></li>');
        }
        // 未入居
        if ($this->getVal('chikugo_minyukyo_fl', $dataModel, true)
            &&  (
            $shumoku == Services\ServiceUtils::TYPE_CHINTAI ||
            $shumoku == Services\ServiceUtils::TYPE_MANSION ||
            $shumoku == Services\ServiceUtils::TYPE_KODATE
            ))
        {
            $bukkenElem['ul.icon-condition']->append('<li><img src="/sp/imgs/icon_not_person.png" alt=""></li>');
        }
        // 建築条件付き土地アイコン
        if ($this->getVal('kenchiku_joken_tsuki_fl', $dataModel, true)) {
            $bukkenElem['ul.icon-condition']->append('<li><img src="/sp/imgs/icon_land.png" alt=""></li>');
        }
    }

    /**
     * 代表画像処理
     */
    private function createThumbnail($bukkenElem, $dispModel, $img_server, $params)
    {
        $thumbnail = Services\ServiceUtils::getMainImageForSP($dispModel, $params);
        if (! is_null($thumbnail)) {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', $this->getImgDomain($img_server) . $thumbnail->url. '?width=100&amp;height=100');
        } else {
            $bukkenElem->find('figure.object-thumb img')
                ->attr('src', $this->getImgDomain($img_server) . "/image_files/path/no_image?width=100&amp;height=100");
        }
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

    private function getImgDomain($img_server) {

        if ((boolean)$this->_config->img_can_use_https) {
            return str_replace('http:', '', $img_server);
        }

        return $img_server;
    }

}