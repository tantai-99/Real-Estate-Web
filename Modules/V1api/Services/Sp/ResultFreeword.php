<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;

class ResultFreeword extends Services\AbstractElementService
{
	public $head;
	public $header;
	public $content;
    public $info;
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

        $pNames = $datas->getParamNames();
        // 種目名称の取得
        $shumoku_nm = $pNames->getShumokuName();

        // 都道府県名の取得
        $ken_nm = $pNames->getKenName();
        // 沿線名の取得
        $ensen_nm = $pNames->getEnsenName();
        // 市区町村名の取得
        $shikugun_nm = $pNames->getShikugunName();
        // 政令指定都市名の取得
        $locate_nm = $pNames->getLocateName();
        // 駅名の取得
        $eki_nm = $pNames->getEkiName();

        $pSearchFilter = $params->getSearchFilter();

        // 検索タイプ
        $s_type = $params->getSearchType();
        $type_ct = $params->getTypeCt();
        $type_id = array();
        if (is_array($type_ct)) {
            foreach ($type_ct as $ct) {
                $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
            }
        } else {
            $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        }
        $class = Estate\TypeList::getInstance()->getClassByType($type_id[0]);
        $tilteClass = Estate\ClassList::getInstance()->get($class);
        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $tilteClass = implode(' ', $freewords);
        }

        $title_txt   = "${tilteClass}を検索｜${siteName}";
        $keyword_txt = "${tilteClass},検索,${keyword}";
        $desc_txt   = "${tilteClass}の検索結果一覧。${description}";

        // ページID
        if ($params->getPage(1) != 1) {
            $page = $params->getPage();
            $page_txt = "【{$page}ページ目】";
            $title_txt .= $page_txt;
            $desc_txt  .= $page_txt;
        }

		$head = new Services\Head();
		$head->title = $title_txt;
		$head->keywords = $keyword_txt;
		$head->description = $desc_txt;

		return $head->html();
	}

	private function header(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $pSearchFilter = $params->getSearchFilter();

        $pNames = $datas->getParamNames();
        // ページ
        $page = $params->getPage(1);

        // 検索タイプ
        $s_type = $params->getSearchType();

        $type_ct = $params->getTypeCt();
        if (is_array($type_ct)) {
            $type_ct = $type_ct[0];
        }
        $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        $class = Estate\TypeList::getInstance()->getClassByType($type_id);
        $tilteClass = Estate\ClassList::getInstance()->get($class);
        $h1_txt = "${tilteClass}情報を検索";
        if (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"])) {
            $freewords = explode('　', htmlspecialchars($pSearchFilter["fulltext_fields"]));
            $h1_txt = implode(' ', $freewords)."の${tilteClass}情報を検索";
        }

        if ($page > 1) {
            $h1_txt = $h1_txt . "　${page}ページ目";
        }

		return "<h1 class='tx-explain'>$h1_txt</h1>";
	}

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".sp.tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/result/content.sp.tpl");
        $pSearchFilter = $params->getSearchFilter();

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
		$pNames = $datas->getParamNames();
        // 検索タイプ
        $s_type = $params->getSearchType();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $type_id = array();
        if (is_array($type_ct)) {
            foreach ($type_ct as $ct) {
                $type_ct = $ct;
                $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($ct);
            }
        } else {
            $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        }
        $class = Estate\TypeList::getInstance()->getClassByType($type_id[0]);
        $classTilte = Estate\ClassList::getInstance()->get($class);

        // こだわり条件
        $searchFilter = $datas->getSearchFilter();

        //物件リクエストリンクの表示設定 #####
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
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
        if($settingRow->display_freeword && (isset($pSearchFilter["fulltext_fields"]) && !empty($pSearchFilter["fulltext_fields"]))){
            $doc['input[name="search_filter[fulltext_fields]"]']->val(htmlspecialchars(trim($pSearchFilter["fulltext_fields"])));
            $freewordText = $pSearchFilter["fulltext_fields"];
            $stringslipt=preg_split('/(.{20})/us', $freewordText,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
            if(count($stringslipt)>1){
                $freewordText = $stringslipt[0].'...';
            }
        }else{
            $doc[".element-input-search-result"]->remove();
        }
        // ########

        /*
         * パンくず作成
         * 検索タイプによって、作成するパンくずは異なる。
         */
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [
            // URL = NAME
            "/${top}"      => $this::BREAD_CRUMB_1ST_SHUMOKU
        ];
        // 1ページ目か
        $pageID = $params->getPage(1);
        $isFirstPage = $pageID == 1;
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_FREEWORD:
                $levels = array('' => "${classTilte}一覧");
                break;
        }
        // breadcrum freeword
        if ($freewordText != '') {
            $freewordText = htmlspecialchars($freewordText);
            $levels[''] = "${freewordText}一覧";
        }
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);

        /*
         * 見出し処理
         */
        // 検索タイプによって、テキストは異なる。
        $doc['section.articlelist-inner div:first']->remove(); // 特集用テキストの削除

        $h2Text = '';
        $lead_keyword = '';
        switch ($s_type)
        {
            case $params::SEARCH_TYPE_FREEWORD:
                $h2Text = "${classTilte}の物件一覧";
                break;
        }
        if ($freewordText != '') {
            $h2Text = "${freewordText} の物件一覧";
            $lead_keyword = $freewordText;
        }
        // $lead_sentence = "${lead_keyword}の検索結果（${classTilte}）ページです。ご希望の条件で更に絞り込むことも可能です。".
        //             "また、ご希望に合った物件が見つからない場合は、絞り込み条件を変更して検索してみてはいかがでしょうか。" .
        //             "${lead_keyword}で${classTilte}の不動産情報をお探しなら、${comName}におまかせ！";

        $doc['h2.article-heading span']->text($h2Text);

        $resultMaker = new Element\Result();
        $resultMaker->createElement($params->getTypeCt(), $doc, $datas, $params, $settings->special, false, $settings->page, $settings->search);

        //物件リクエスト
        $bukkenList = $datas->getBukkenList();
        $total_count = $bukkenList['total_count'];
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            if ($total_count == 0 && $get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['span.btn-request']->append($estateRequest['requestUrl']);
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if($total_count == 0 && $hpPageRow) {
                $requestUrl = "<a href='/". $hpPageRow->public_path ."' target='_blank'>リクエストはこちらから</a>";
                $doc['span.btn-request']->append($requestUrl);
                $doc['div.btn-request-txt']->remove();
            }
        }

        return $doc->html();
    }

    private function info(
			Params $params,
			Settings $settings,
			Datas $datas)
    {
        $bukkenList = $datas->getBukkenList();
    	return [
    			'current_page' => $bukkenList['current_page'],
    			'total_page' => $bukkenList['total_pages'],
    			'total_count' => $bukkenList['total_count']
    	];
    }
}