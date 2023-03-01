<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\Information\InformationRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Assessment;
use Library\Custom\Analysis\General;
use Exception;
use Carbon\Carbon;
use Library\Custom\Controller\Action\InitializedCompany;

class IndexController extends InitializedCompany
{
    public function init($request, $next)
    {
        // 診断のみアカウントは診断画面へ飛ばす
        $profile = getInstanceUser('cms')->getProfile();
        if ($profile && $profile->isAnalyze()) {
            return Redirect::to('/diacrisis');
        }
        // 初期設定未完了の場合は状況ごとに表示先振り分け
        $hp = getUser()->getCurrentHp();
        if (!$hp || !$hp->isInitialized()) {
            return $this->_thenNotInitialized($request, $next, $hp);
        }
        $this->_analysis = $this->_initAnalysis();

        return $next($request);
    }

    public function index()
    {    
        //アットホームからのお知らせを取得
        $infoObj = App::make(InformationRepositoryInterface::class);
        $rows = $infoObj->getLoginafterData();
        $this->view->cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
        $this->view->information = $rows;
        $this->_processAssessment();
        $this->_processAnalysis();

        return view('index.index');
    }

    private function _processAssessment()
    {
        /** @var $hp App\Models\Hp */
        $hp = getInstanceUser('cms')->getCurrentHp();

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
        $pages = $assessment->getPages();
        $this->view->adequacy_counts = $pages->assess();
        $total = $pages->getTotalResult();
        $this->view->adequacy_counts_total = $total;
        // $totalArticle = $pages->getTotalResultArticle();
        // $this->view->adequacy_counts_total = array(
        //     'public' => $total['public'] + $totalArticle['public'], 
        //     'draft' => $total['draft'] + $totalArticle['draft'], 
        //     'new' => $total['new'] + $totalArticle['new'],
        //     'published_at' => max($total['published_at'], $totalArticle['published_at'])
        // );
        $this->view->adequacy_point = $assessment->calculatePagePoint5steps();

        // ページ更新
        $this->view->site_published = $pages->getTotalResult()['published_at'];
        $this->view->information_published = $pages->assess()[config('constants.hp_page.TYPE_INFO_DETAIL')]['published_at'];
        $this->view->update_point = $assessment->calculateUpdatePoint5steps();

        // 総合評価
        $pv = [];
        for ($i = 0; $i < 3; $i++) {
            $m = 2 + $i;
            $pv['2015-' . $m] = rand(30, 200);
        }


        $this->view->pv = $this->_analysis->getPageviewsForPeriod();
        $this->view->total_points = $assessment->fetchTotalPointsIn(date('Y-m', strtotime('-2 months')), date('Y-m'));
        $this->view->max_point = $assessment->getMaxPoint();
    }

    private function _processAnalysis()
    {

         //総合評価フラグ
        $this->view->uniquePageViews = $this->_analysis->getUniqueVisitors3month();
        $this->view->contactCount = $this->_analysis->getContactCount3month();
        $this->view->getDateList = $this->_analysis->getDateList();

        // アクセス数
        $this->view->pageviews = $this->_analysis->getPageviews();
        $this->view->visits = $this->_analysis->getVisits();
        $this->view->uniquePageviews = $this->_analysis->getVisitors();

        // 問い合わせ数
        $this->view->generalContactCount = $this->_analysis->getGeneralContactCount();
        $this->view->assesmentContactCount = $this->_analysis->getAssesmentContactCount();
        $this->view->documentContactCount = $this->_analysis->getDocumentContactCount();
		
		$this->view->estateContactCountForLivingLease			= $this->_analysis->getEstateContactCountMoM( config('constants.hp_page.TYPE_FORM_LIVINGLEASE')			) ;
		$this->view->estateContactCountForOfficeLease			= $this->_analysis->getEstateContactCountMoM( config('constants.hp_page.TYPE_FORM_OFFICELEASE')			) ;
		$this->view->estateContactCountForLivingBuy				= $this->_analysis->getEstateContactCountMoM( config('constants.hp_page.TYPE_FORM_LIVINGBUY')			) ;
		$this->view->estateContactCountForOfficeBuy				= $this->_analysis->getEstateContactCountMoM( config('constants.hp_page.TYPE_FORM_OFFICEBUY')			) ;

		$this->view->estateContactCountForRequestLivingLease	= $this->_analysis->getEstateRequestCountMoM( config('constants.hp_page.TYPE_FORM_REQUEST_LIVINGLEASE')	) ;
		$this->view->estateContactCountForRequestOfficeLease	= $this->_analysis->getEstateRequestCountMoM( config('constants.hp_page.TYPE_FORM_REQUEST_OFFICELEASE')	) ;
		$this->view->estateContactCountForRequestLivingBuy		= $this->_analysis->getEstateRequestCountMoM( config('constants.hp_page.TYPE_FORM_REQUEST_LIVINGBUY')		) ;
		$this->view->estateContactCountForRequestOfficeBuy		= $this->_analysis->getEstateRequestCountMoM( config('constants.hp_page.TYPE_FORM_REQUEST_OFFICEBUY')		) ;
    }

    private function _initAnalysis()
    {
        $analysis = null;
        $hp = getInstanceUser('cms')->getCurrentHp();
        $company = App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hp->id);
        $nowDate  = Carbon::now();
        $baseYearMonth = $nowDate->format('Y-m');
        $analysis = new General();

        try {
            $analysis->init($company->id, null, $baseYearMonth);
            $analysis->initData();
        } catch (Exception $e) {
            return $analysis;
        }
        return $analysis;
    }

    public function confirmCapacity()
    {
        // $this->_helper->viewRenderer->setNoRender();
        // $layout = $this->_helper->layout;
        // $layout->disableLayout();

        $hp = getInstanceUser('cms')->getCurrentHp();
        $nowCapacity = $hp->capacityCalculation();
        $maxCapacity = config('constants.hp.SITE_OBER_CAPASITY_DATAMAX');
        echo "最大容量:" . $maxCapacity . "MB\n 現在の容量:" . $nowCapacity . "MB";
    }
}
