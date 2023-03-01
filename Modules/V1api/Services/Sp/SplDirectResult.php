<?php

namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;

class SplDirectResult extends Services\AbstractElementService
{

    public $head;
    public $header;
    public $content;
    public $info;
    public $directResult;
    public $breadCrumb;

    public function create(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $this->head = $this->head($params, $settings, $datas);
        $this->header = $this->header($params, $settings, $datas);
        $this->content = $this->content($params, $settings, $datas);
        $this->info = $this->info($params, $settings, $datas);
        $this->directResult = true;
    }

    public function check(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
    }

    private function head(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $pageInitialSettings = $settings->page;
        $siteName = $pageInitialSettings->getSiteName();
        $keyword = $pageInitialSettings->getKeyword();
        $comName = $pageInitialSettings->getCompanyName();
        $description = $pageInitialSettings->getDescription();
        // 種目名称の取得
        $shumoku_nm = $datas->getParamNames()->getShumokuName();
        // 特集の取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();

        // ページID
        $page_txt = '';
        if ($params->getPage(1) != 1) {
            $page = $params->getPage();
            $page_txt = "【{$page}ページ目】";
        }

        $head = new Services\Head();
        $head->title = "{$specialRow->title}の物件一覧｜${siteName}{$page_txt}";
        $head->keywords = "{$specialRow->title},検索,${keyword}";
        $head->description = "{$specialRow->title}：【${comName}】${description}{$page_txt}";
        return $head->html();
    }

    private function header(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // ページID
        $page_txt = '';
        if ($params->getPage(1) != 1) {
            // 　{$pageID}ページ目
            $page = $params->getPage();
            $page_txt = "　{$page}ページ目";
        }

        // 特集の取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();

        return "<h1 class='tx-explain'>{$specialRow->title}の物件情報の一覧{$page_txt}</h1>";
    }

    private function content(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/" . Services\ServiceUtils::checkDateMaitain() . ".sp.tpl");
            return $doc->html();
        }
        $doc = $this->getTemplateDoc("/result/content.sp.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;

        $pSearchFilter = $params->getSearchFilter();

        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();

        $pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();

        // 検索タイプ
        $s_type = $params->getSearchType();
        // 都道府県の取得
        $ken_ct = $params->getKenCt();

        //物件リクエストリンクの表示設定 #####
        $class = Estate\TypeList::getInstance()->getClassByType($specialSetting->enabled_estate_type[0]);
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['div.btn-request-txt']->append($estateRequest['requestUrl']);
            } else {
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if (isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $class);
                if ($hpPageRow) {
                    $requestUrl = "<a href='/" . $hpPageRow->public_path . "' target='_blank'>物件リクエストはこちら</a>";
                    $doc['div.btn-request-txt']->append($requestUrl);
                } else {
                    $doc['div.btn-request-txt']->remove();
                }
            }
        }

        $freewordText = '';
        if ($specialSetting->display_freeword && isset($pSearchFilter["fulltext_fields"]) && !empty(trim($pSearchFilter["fulltext_fields"]))) {
            $freewordText = $pSearchFilter["fulltext_fields"];
            $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars($freewordText));
            $stringslipt = preg_split('/(.{20})/us', $freewordText, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (count($stringslipt) > 1) {
                $freewordText = $stringslipt[0] . '...';
            }
        } else {
            $doc[".element-input-search-result"]->remove();
        }

        // ########

        /*
         * パンくず、H2、リード分作成
         */
        $levels = [];
        $h2Text = '';
        $lead_keyword = '';

        // 1ページ目か
        $pageID = $params->getPage(1);
        $isFirstPage = $pageID == 1;

        if ($isFirstPage) {
            $levels[""] = "物件一覧";
        } else {
            $levels["/{$specialRow->filename}/"] = "物件一覧";
            $levels[""] = "{$pageID}ページ目";
        }
        $this->breadCrumb = $this->createSpecialBreadCrum($doc['div.breadcrumb'], $levels, $specialRow->title);
        $h2Text = "{$specialRow->title}の物件一覧";
        // breadcrum freeword
        if ($freewordText != '') {
            $freewordText =  htmlspecialchars($freewordText);
            $levels[""] = "${freewordText}一覧";
            $h2Text = "${freewordText} の物件一覧";
        }
        $doc['h2.article-heading span']->text($h2Text);

        $resultMaker = new Element\Result();
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, true, $settings->page, $settings->search);

        $doc['.btn-narrow-down li:eq(1) a']->remove();
        $doc['.btn-term-change li:eq(1) a']->remove();

        //物件リクエスト
        $bukkenList = $datas->getBukkenList();
        $total_count = $bukkenList['total_count'];
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            if ($total_count == 0 && $get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['span.btn-request']->append($estateRequest['requestUrl']);
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if ($total_count == 0 && $hpPageRow) {
                $requestUrl = "<a href='/" . $hpPageRow->public_path . "' target='_blank'>リクエストはこちらから</a>";
                $doc['span.btn-request']->append($requestUrl);
                $doc['div.btn-request-txt']->remove();
            }
        }


        return $doc->html();
    }

    private function info(
        Params $params,
        Settings $settings,
        Datas $datas
    ) {
        $bukkenList = $datas->getBukkenList();
        // 特集を取得
        $specialRow = $settings->special->getCurrentPagesSpecialRow();
        $specialSetting = $specialRow->toSettingObject();
        $type_id = $specialSetting->enabled_estate_type[0];
        $type_ct = Estate\TypeList::getInstance()->getUrl($type_id);
        return [
            'type' => $type_ct,
            'current_page' => $bukkenList['current_page'],
            'total_page' => $bukkenList['total_pages'],
            'total_count' => $bukkenList['total_count']
        ];
    }
}
