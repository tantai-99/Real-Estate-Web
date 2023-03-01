<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App;
use App\Http\Form\Rating\CompanySelect;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\Tag\TagRepositoryInterface;
use Exception;

use Library\Custom\Diacrisis;
use Library\Custom\Assessment;
use Library\Custom\Analysis\General;

use DateTime;
use Carbon\Carbon;
use App\Traits\JsonResponse;
use stdClass;
use Library\Custom\Controller\Action\InitializedCompany;

class DiacrisisController extends InitializedCompany
{
    use JsonResponse;
    private $page;  


    /** 初期化
     *
     *
     */
    public function init($request, $next)
    {   
        $this->view->topicPath('評価・分析');
        
        return parent::init($request, $next);
    }

    public function index()
    {
        return redirect()->route('default.diacrisis.rating');
    }

    public function rating(Request $request)
    {
        $companyTable = App::make(CompanyRepositoryInterface::class);
        $company = getInstanceUser('cms')->getProfile();
        $form = new CompanySelect(['companyRow'=>$company]);
        $hp = getInstanceUser('cms')->getCurrentHp();
        $form->setData($request->all());
        $this->view->companySelectForm = $form;
        if ($form->isValid($request->all()) && is_numeric($form->getElement('company_id')->getValue())) {
            $company = $companyTable->getDataForId($form->getElement('company_id')->getValue());
            $hp = $company->getCurrentHp();
        }
       
        // 評価・分析権限でログインした場合は子会社データを取り直す
        if($company->isAnalyze()){

            $options = $form->getMultiOptions('company_id');
            reset($options);
            $companyId=key($options);
            if (!is_null($companyId)){
                $company = $companyTable->getDataForId($companyId);    
                $hp = $company->getCurrentHp();
            }
        }

        $this->view->company = $company;

        // 有効な加盟店がいない場合
        if(!$hp){
            $this->view->hasValidCompany = false;
        } else {
            $this->view->hasValidCompany = true;
            $assessment = new Assessment($hp);
            $assessment->assess();

            // 機能活用度
            $feature = $assessment->getFeatures();
            $this->view->utilization_functions = $assessment->getTargetFeatureNames();
            $this->view->utilization_point = $assessment->calculateFeaturePoint5steps();
            $this->view->utilization = $feature->assess();
            $this->view->num_utilized = $feature->countUtilized();
            $this->view->num_unutilized = $feature->countUnUtilized();

            // ページ充実度
            $table = App::make(HpPageRepositoryInterface::class);
            $this->view->category_map = $assessment->getPageCategoryMap();
            $this->view->disable_pages	= $assessment->getDisablePages()	;
            
            $this->view->disable_with_standard_page  = $company->cms_plan < config('constants.cms_plan.CMS_PLAN_STANDARD') ? $assessment->getDisableWithStandardPages():[]    ;
            $this->view->page_types = $table->getTypeListJp();

            $pages = $assessment->getPages();
            $this->view->adequacy_counts = $pages->assess();

            // 5460 評価分析（ツール評価）に不動産お役立ち情報（200記事）の項目を追加する
            $total = $pages->getTotalResult();
            $this->view->adequacy_counts_total = $total;

            $this->view->adequacy_point = $assessment->calculatePagePoint5steps();
    
            // ページ更新
            $this->view->site_published = $pages->getTotalResult()['published_at'];
            $this->view->information_published = $pages->assessPublishPage(config('constants.hp_page.TYPE_INFO_DETAIL'));
            $this->view->update_point = $assessment->calculateUpdatePoint5steps();

            // 総合評価
            $this->_analysis = $this->_initAnalysis($company);
            $this->view->pv = $this->_analysis->getPageviewsForPeriod();
            $this->view->total_points = $assessment->fetchTotalPointsIn(date('Y-m', strtotime('-5 months')), date('Y-m'));
            $this->view->max_point = $assessment->getMaxPoint();

            // // 5444
            $this->view->category_map_article = $assessment->getPageArticleCategoryMap();
            $this->view->article_count = $assessment->getCountArticlePage();
        }
        
        return view('diacrisis.rating');
    }

    public function analysis(Request $request) {
        
        $profile = getInstanceUser('cms')->getProfile();
        $company = App::make(CompanyRepositoryInterface::class)->getDataForId($profile->id);

        $form = new CompanySelect(['companyRow' => $company]);
        $form->setData($company);
        $hp = getInstanceUser('cms')->getCurrentHp();
        $this->view->companySelectForm = $form;
        if ($form->isValid($request->all()) && is_numeric($form->getElement('company_id')->getValue())) {
            /** @var $company App\Models\Company */
            $company = App::make(CompanyRepositoryInterface::class)->getDataForId($form->getElement('company_id')->getValue());
            $hp = $company->getCurrentHp();
        }
        $this->view->company = $company;

        // 評価・分析権限でログインした場合は子会社データを取り直す
        if($company->isAnalyze()){
            $options = $form->getMultiOptions('company_id');
            reset($options);
            $companyId=key($options);
            if (!is_null($companyId)){
                $company = App::make(CompanyRepositoryInterface::class)->getDataForId($companyId);    
                $hp = $company->getCurrentHp();
            }
        }

        // 有効な加盟店がいない場合
        if(!$hp){
            $this->view->hasValidCompany = false;
        } else {
            $this->view->hasValidCompany = true;

            $startYear ='2014';
            //$startYear = (new Zend_Date($company->create_date))->toString('yyyy');
            $endYear   = (new Datetime())->format('Y');
            $yearOptions = array();
            for($year=$startYear; $year<=$endYear; $year++){
                $yearOptions[] = $year;
            }
            
            
            $monthStart =1;
            $monthEnd  = 12;
            $monthOptions = array();
            for($month=$monthStart; $month<=$monthEnd; $month++){
                $monthOptions[] = $month;
            }
            $this->view->compnay = $company;
            $this->view->yearOptions = $yearOptions;
            $this->view->monthOptions = $monthOptions;

            // アナリティクスタグが設定されていなければその旨知らせる
            $tag = App::make(TagRepositoryInterface::class)->getDataForCompanyId($company->id);
            $hasAnalyticsTag = true;
            if (is_null($tag)){ 
                $hasAnalyticsTag = false;
            }
            $this->view->hasAnalyticsTag = $hasAnalyticsTag;
        }
        
        return view('diacrisis.analysis');
    }

    /**
     * 解析：サマリー
     *
     */
    public function apiGetAnalysisSummary(Request $request) {

        try {
            $params = $request->all();
			$companyId = $request->companyId;

            $baseYearMonth = $request->baseYearMonth;
            $nowDate  = Carbon::now();
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

	        // サマリ
            $diacrisis = $this->getDiacrisisObj($companyId);
            $summary = new \stdClass();
            $summary->basePeriod = $startDate."〜".$endDate;
            $summary->data = $diacrisis->getSummary($baseYearMonth);
            $items = new \stdClass();
            $items->summary = $summary;

            return $this->success([
                'items' => $items
            ]);

        }
        catch (Exception $e) {
            return $this->error('データ取得に失敗しました。');
        }
	}

    /**
     * 解析：アクセス状況 推移
     *
     */
    public function apiGetAnalysisAccess() {

        try {
            $params = $this->_request->all();
			$companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // アクセス状況
            $diacrisis = $this->getDiacrisisObj($companyId);            
            $access = new \stdClass();
            $access->basePeriod = $startDate."〜".$endDate;
	        $access->data = $diacrisis->getAccess($baseYearMonth);
            return $this->success([
                'items' => ['access' => $access]
            ]);

        }
        catch (Exception $e) {
        	return $this->error('データ取得に失敗しました。');
        }
	}

    /**
     * 解析：デバイス別アクセス情報
     *
     */
    public function apiGetAnalysisAccessDevice() {
        try {

            $params = $this->_request->all();
            $companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // デバイス別アクセス状況
            $diacrisis = $this->getDiacrisisObj($companyId);            
            $accessDevice = new \stdClass();
            $accessDevice->basePeriod = $startDate."〜".$endDate;
            $accessDevice->data = $diacrisis->apiGetAccessByDevice($baseYearMonth);
            return $this->success([
                'items' => ['accessDevice' => $accessDevice]
            ]);


        }
        catch (Exception $e) {
            return $this->error('データ取得に失敗しました。');
        }
	}
    
    /**
     * 解析：メディア別データ
     *
     */
    public function apiGetAnalysisAccessMedia() {

            $params = $this->_request->all();
            $companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // メディア別アクセス状況
            $diacrisis = $this->getDiacrisisObj($companyId);            
            $accessMedia = new \stdClass();
            $accessMedia->baseYearMonth = $baseYearMonth;
            $accessMedia->basePeriod = $startDate."〜".$endDate;
            $accessMedia->data = $diacrisis->apiGetAnalysisAccessByMedia($baseYearMonth);

            return $this->success([
                'items' => ['accessMedia' => $accessMedia]
            ]);
	}
    
    /**
     * 解析：月間キーワードTOP20
     *
     */
    public function apiGetAnalysisAccessKeywordRanking(){

            $companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // 月間キーワードTOP20
            $diacrisis = $this->getDiacrisisObj($companyId); 
            $accessKeywordRanking = new \stdClass();
            $accessKeywordRanking->basePeriod = $startDate."〜".$endDate;
            $limit = 20;
            $accessKeywordRanking->data = $diacrisis->apiGetAnalysisAccessKeywordTop($baseYearMonth, $limit);
        
            return $this->success([
                'items' => ['accessKeywordRanking' => $accessKeywordRanking]
            ]);
	}


    /**
     * 解析：ページ別アクセスTOP20 ⇒ ページ別セッション数 TOP20
     *
     */
    public function apiGetAnalysisAccessPageRanking() {

            $companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // メディア別アクセス状況
            $diacrisis = $this->getDiacrisisObj($companyId); 
            $accessPageRanking = new stdClass();
            $accessPageRanking->basePeriod = $startDate."〜".$endDate;
            $limit = 20;
            $accessPageRanking->data = $diacrisis->apiGetAnalysisAccessPageTop($baseYearMonth, $limit);

            return $this->success([
                'items' => ['accessPageRanking' => $accessPageRanking]
            ]);
	}

    /**
     * 解析：ページ別アクセスTOP20 ⇒ ページ別セッション数 TOP20
     *
     */
    public function apiGetAnalysisAccessPageView() {

            $companyId = $this->_request->companyId;

            $nowDate  = Carbon::now();
            $baseYearMonth = $this->_request->baseYearMonth;
            if(is_null($baseYearMonth)){
                $baseYearMonth = $nowDate->format('Y-m');
            }
            $baseDate = Carbon::parse($baseYearMonth);

            // 基軸月の月初～基軸月の月末)
            $startDate = $baseDate->format('Y年m月01日');
            if($nowDate->format('Y-m') == $baseDate->format('Y-m')){
                $endDate = $nowDate->format('Y年m月d日'); 
            }else{
                $endDate = $baseDate->format('Y年m月d日'); 
            }

            // メディア別アクセス状況
            $diacrisis = $this->getDiacrisisObj($companyId); 
            $accessPageView = new \stdClass();
            $accessPageView->basePeriod = $startDate."〜".$endDate;
            $limit = 20;
            $accessPageView->data = $diacrisis->apiGetAnalysisAccessPageView($baseYearMonth, $limit);

            return $this->success([
                'items' => ['accessPageView' => $accessPageView]
            ]);
	}


    public function getDiacrisisObj($companyId) {
        $diacrisis = new Diacrisis();
        $serviceToken = $diacrisis->init($companyId);
        return $diacrisis;
    }
 
    private function _initAnalysis($company)
    { 
            $analysis = null;
            $nowDate  = new DateTime();
            $baseYearMonth = $nowDate->format('Y-m');
            $analysis = new General();

            try {
                $analysis->init($company->id,null,$baseYearMonth, 6);
                $analysis->initData();
            }catch (Exception $e){
                return $analysis;
            }
            
            
            return $analysis;
        }    
}
