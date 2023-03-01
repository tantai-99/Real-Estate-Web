<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;

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
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
		$this->content = $this->content($params, $settings, $datas);
		$this->info = $this->info($params, $settings, $datas);
        $this->directResult = true;
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}

	private function head(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
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
			Datas $datas)
	{
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
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/result/content.tpl");
	
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
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        
        $freewordText = '';
        if($specialRow->display_freeword){
                $asidetElem = $doc['div.articlelist-side.contents-left'];
                $searchFilterSection = $asidetElem->find('.articlelist-side-section:last');
                $searchElement = $asidetElem->find(".element-input-search-result");
                if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
                    $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
                    $freewordText = $pSearchFilter["fulltext_fields"];
                    $stringslipt=preg_split('/(.{20})/us', $freewordText,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
                    if(count($stringslipt)>1){
                        $freewordText = $stringslipt[0].'...';
                    }
                } else {
                    $searchElement->before($searchFilterSection);
                }
        }else{
            $doc[".element-input-search-result"]->remove();
        }

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
        }
        else {
            $levels["/{$specialRow->filename}/"] = "物件一覧";
            $levels[""] = "{$pageID}ページ目";
        }
        $this->breadCrumb = $this->createSpecialBreadCrum($doc['div.breadcrumb'], $levels, $specialRow->title);
        $h2Text = "{$specialRow->title}の物件一覧";
        $lead_sentence = "{$specialRow->title}｜物件の一覧ページです。ご希望の条件で更に絞り込むことも可能です。また、ご希望に合った物件が見つからない場合は、絞り込み条件を変更して検索してみてはいかがでしょうか。不動産情報をお探しなら、${comName}におまかせ！";
        
         // breadcrum freeword
         if ($freewordText != '') {
            $freewordText = htmlspecialchars($freewordText);
            $levels[""] = "${freewordText}一覧";
            $h2Text = "${freewordText} の物件一覧";
            $lead_keyword = $freewordText;
        }

        $doc['div.contents-right h2:first']->text($h2Text);
        if ($isFirstPage) {
            $doc['div.contents-right div:last p']->text($lead_sentence);
        }
        else {
        	$doc['div.contents-right div:last p']->remove();
        }
        // 特集用テキスト
        if ($specialRow->comment) {
        	$doc['section.articlelist-inner div:first']->html(nl2br(htmlspecialchars($specialRow->comment)));
        }
        else {
        	$doc['section.articlelist-inner div:first']->remove();
        }

        //物件リクエストリンクの表示設定 #####
        $class = Estate\TypeList::getInstance()->getClassByType($specialSetting->enabled_estate_type[0]);
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['p.btn-request']->append($estateRequest['requestUrl']);
            } else {
                $doc['p.request']->remove();
            }
        } else {
            if(isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $class);
                if($hpPageRow) {
                    $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                    $doc['p.btn-request']->append($requestUrl);
                }else{
                    $doc['p.request']->remove();
                }
            }
        }
        // ########

        // こだわり条件
        $searchFilter = $datas->getSearchFilter();
        $frontSearchFilter = $datas->getFrontSearchFilter();

        // 検索
        $resultMaker = new Element\Result();
        $bukkenList = $datas->getBukkenList();
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, true, $settings->page, $settings->search);

        if($params->getParam('no_search_page') == 1) {
			$doc['section.change-area']->remove();
        }
        
        $facet = new Services\BApi\SearchFilterFacetTranslator();
        $facet->setFacets($bukkenList['facets']);

        $total_count = $bukkenList['total_count'];

        //物件リクエスト
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            if ($total_count == 0 && $get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['span.btn-request']->append($estateRequest['requestUrl']);
            }
        } else {
            if($total_count == 0 && $hpPageRow) {
                $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                $doc['span.btn-request']->append($requestUrl);
            }
        }

        // アサイド
        $searchFilterElement = new Element\SearchFilter( $frontSearchFilter );
        $searchFilterElement->setSearchFilterSplCms(  $specialSetting->search_filter );
        $searchFilterElement->renderAside($specialSetting->enabled_estate_type, $total_count, $facet, $doc);
        
        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->createElementDirectResult(
        		$doc, $params, $settings, $datas);

        return $doc->html();
    }
        
    private function info(
			Params $params,
			Settings $settings,
			Datas $datas)
    {
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