<?php
namespace Modules\V1api\Services\Pc\Element;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\SpecialSettings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Models\PageInitialSettings;;
use Library\Custom\Model\Estate;
class ResultMap
{

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

    public function createElement($type_ct, $doc, Datas $datas, Params $params, SpecialSettings $specialSettings, $isSpecial = false, PageInitialSettings $pageInitialSettings)
    {
    	$bukkenList = $datas->getBukkenList();
        // 特集を取得
        $specialRow = $isSpecial ? $specialSettings->getCurrentPagesSpecialRow() : null;
        $specialSetting = $isSpecial ? $specialRow->toSettingObject() : null;
        // 検索タイプ
        $s_type = $params->getSearchType();

        $pNames = $datas->getParamNames();
        // 種目情報の取得
        // $type_ct = $params->getTypeCt();

        // 特集複数種目対応
        if (is_array($type_ct) && count($type_ct) === 1) {
            $type_ct = $type_ct[0];
        }
        if (is_array($type_ct)) {
            $shumoku = [];
            foreach ($type_ct as $ct) {
                $type_id = Estate\TypeList::getInstance()->getTypeByUrl($ct);
                $shumoku[] = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
            }

            // 一覧レンダリング用種目コード
            $compositeType = Estate\TypeList::getInstance()->getComopsiteTypeByShumokuCd($shumoku);
            switch ($compositeType) {
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_2:
                    $renderListShumoku = Services\ServiceUtils::TYPE_KASI_TENPO;
                    break;
                case Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3:
                    $renderListShumoku = Services\ServiceUtils::TYPE_KASI_OTHER;
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2:
                    $renderListShumoku = Services\ServiceUtils::TYPE_KODATE;
                    break;
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1:
                case Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2:
                    $renderListShumoku = Services\ServiceUtils::TYPE_URI_TENPO;
                    break;
            }

        } else {
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
            $shumoku    = Estate\TypeList::getInstance()->getShumokuCode( $type_id );
            $shumoku_nm = Services\ServiceUtils::getShumokuNameByConst($type_ct);

            // 一覧レンダリング用種目コード
            $renderListShumoku = $shumoku;
        }

        // 都道府県の取得
        $ken_ct = $params->getKenCt();
        $ken_cd  = $pNames->getKenCd();
        $ken_nm  = $pNames->getKenName();

        // 市区町村の取得（複数指定の場合は使用できない）
        $shikugun_ct = $params->getShikugunCt(); // 単数or複数
        $shikugun_cd = $pNames->getShikugunCd();
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市の取得（複数指定の場合は使用できない）
        $locate_ct = $params->getLocateCt(); // 単数or複数
        $locate_cd = $pNames->getLocateCd();
        $locate_nm = $pNames->getLocateName();

        /*
         * ページング処理
         */
        $page        = $bukkenList['current_page'];
        $total_page  = $bukkenList['total_pages'];
        $per_page    = $bukkenList['per_page'];
        $total_count = $bukkenList['total_count'];

        $pagingElem = $doc['.map-bl-list__inner'];

        // カウント部分
        if ($total_count === 0)
        {
            $first = 0;
            $last = 0;
        } else {
            $first = $per_page * ($page - 1) + 1;
            $last  = $per_page * $page;
            if ($last > $total_count) $last = $total_count;
        }

        $countTxt = "<span>".number_format($total_count)."</span>件中 ${first}〜${last}件を表示";
        $pagingElem['p.total-count']->empty()->append($countTxt);

        // ページング
        $base_url = '';
        $base_file = $isSpecial ? $specialRow->filename : $type_ct;
        $base_url = "/${base_file}/${ken_ct}/result/${shikugun_ct}-map.html";

        $articlePagerElem      = $pagingElem['ul.map__pager']->empty();

        $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
        $pager_prev  = "<li class='link__prev'><a data-page=${prev_page} href='#'>前へ</a></li>";
        $articlePagerElem->append($pager_prev);

        // ページャー　最大５
        if ($page <= 3) {
            $i = 1;
        } else  if (($total_page - $page) <= 2) {
            $i = $total_page -4;
        } else {
            $i = $page -2;
        }
        if ($i <= 0) {
            $i = 1;
        }
        $lastPage = $i +4;
        for ($i; $i <= $total_page && $i <= $lastPage; $i++)
        {
            if ($i === $page) {
                $pager = "<li><span>${i}</span></li>";
            } else {
                //$pager = "<li><a href='${base_url}?page=${i}'>${i}</a></li>";
                $pager = "<li><a a data-page=${i} href='#'>${i}</a></li>";

            }
            $articlePagerElem->append($pager);
        }

        $next_page = ($page == 0 || $page == $total_page) ? $total_page : $page +1;
        //$pager_next = "<li class='link__next'><a href='${base_url}?page=${next_page}'>次へ</a></li>";
        $pager_next = "<li class='link__next'><a data-page=${next_page} href='#'>次へ</a></li>";

        $articlePagerElem->append($pager_next);

        // 物件要素の生成モジュール
        $bukkenMaker = new BukkenListMap();
        // 必要要素の初期化とテンプレ化
        $innerElem = $doc['p.top'];
        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
            $highlight = array();
            if(isset($bukken['highlight'])) {
                $highlight = (object) array_merge($bukken['highlight'], $bukken['data_model_highlight']);
            }

            // $bukkenElemに物件APIから取得した情報を設定
            // ATHOME_HP_DEV-4841 : 第7引数として、PageInitialSettings を追加
            $bukkenElem = $bukkenMaker->createElement($renderListShumoku, $dispModel, $dataModel, $params, $dispModel->niji_kokoku_jido_kokai_fl, $highlight, $pageInitialSettings);
            $innerElem->append($bukkenElem);
        }
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }
}