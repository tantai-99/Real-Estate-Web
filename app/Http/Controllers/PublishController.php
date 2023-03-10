<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\Traits\JsonResponse;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\PublishProgress\PublishProgressRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Http\Form;
use Library\Custom\Plan;
use Library\Custom\Model\Lists;
use Library\Custom\Model\Estate;
use Library\Custom\Publish;
use Library\Custom\Publish\Prepare\Page;
use Library\Custom\Publish\Render\Content;
use Library\Custom\Publish\Special\Prepare\Simple as Special_Prepare_Simple;
use Library\Custom\Publish\Special\Prepare\Detail as Special_Prepare_Detail;
use Library\Custom\Publish\Special\Prepare\Api as Special_Prepare_Api;
use Library\Custom\Publish\Special\Prepare\Testsite as Special_Prepare_Testsite;
use Library\Custom\Publish\Estate\Prepare\Simple as Estate_Prepare_Simple;
use Library\Custom\Publish\Estate\Prepare\Detail as Estate_Prepare_Detail;
use Library\Custom\Logger;
use Library\Custom\ProgressBar\Adapter\JsPush;
use Laminas\ProgressBar\ProgressBar;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use Library\Custom\Controller\Action\InitializedCompany;

class PublishController extends InitializedCompany {

    use JsonResponse;

    public $hp;

    /**
     * @var Library\Custom\Publish\Prepare\Page
     */
    public $page;

    /**
     * @var boolean
     */
    public $substitute;

    private $logger;

    private $topOriginal;

    private $isFDP;

    private $companyRow;

    public function init($request, $next) {
        ini_set('memory_limit', '256M');
        set_time_limit(60);
        $this->hp         = getUser()->getCurrentHp();
        if($this->hp){
            $this->page       = new Page($this->hp->id, $request);
            $this->substitute = getUser()->isCreator();
            $this->view->topicPath('??????????????????/??????');

            // ???????????? && ???????????????????????? => ????????????????????????
            if (getUser()->isCreator() && getUser()->hasBackupData() && !$request->has('company_id')) {
                if ($this->getLink() == null) {
                    return  redirect('/');
                }
            }

            $company    = App::make(CompanyRepositoryInterface::class);
            $companyRow = $company->find(getUser()->getProfile()->id);
            $this->companyRow = $companyRow;

            $this->topOriginal = $companyRow->checkTopOriginal();
            $this->isFDP = Estate\FdpType::getInstance()->isFDP($companyRow);
            $this->logger = Logger\Publish::getInstance();
            $this->logger->init($this->hp,$companyRow);
            return $next($request);
        }
        return parent::init($request, $next);

    }

    protected function _thenNotInitialized($request, $next, $hp) {

        if (getActionName() == 'previewPage' && $hp) {
            return $next($request);
        }

        return parent::_thenNotInitialized($request, $next, $hp);
    }

    /**
     * ????????????
     *
     */
    public function simple(Request $request) {

        $this->view->topicPath('????????????(????????????)');

        // ??????????????????
        $user = getUser()->getProfile();
        if ( $user->reserve_start_date )
        {
        	$this->view->plan_date		= strftime( '%Y???%m???%d???', strtotime( $user->reserve_start_date ) ) ;
        }
        
        // ?????????????????????
        $pages = $this->page->getList();

        if($user->checkTopOriginal()){
            if(is_array($pages)){
                foreach($pages as $key=>$page){
                    if($page['page_type_code'] == HpPageRepository::TYPE_TOP){
                        $pages[$key]['title'] = config('constants.original.TOP_CONTENT');
                        break;
                    }
                }
            }
        }

        // ATHOME_HP_DEV-5444 200?????????????????????????????????????????????????????????????????????????????????-???????????????
        $hpPage = App::make(HpPageRepositoryInterface::class);
        $articlePage = null;
        $articleKey = false;
        if ($pages) {
            foreach($pages as $key=>$page) {
                if ($page['page_type_code'] == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) {
                    $articlePage = $page;
                    $articlePage['label'] = [$page['label']];
                    $articleKey = $key;
                    break;
                }
            }
            $articlePage['public_flg_ar'] = false;
            foreach($pages as $page) {
                if (in_array($page['page_category_code'], $hpPage->getCategoryCodeArticle()) || $hpPage->isLinkArticle($page)) {
                    if ($articlePage) {
                        if (!in_array($page['label'], $articlePage['label'])) {
                            $articlePage['label'][] = $page['label'];
                        }
                        if (!$articlePage['public_flg_ar'] && $page['label'] == 'update' && $page['public_flg']) {
                            $articlePage['public_flg_ar'] = true;
                        }
                    }
                }
            }
        }
        $this->view->notArticleDisplay = $hpPage->getCategoryCodeArticle();
        // ATHOME_HP_DEV-5444 200?????????????????????????????????????????????????????????????????????????????????-???????????????

            // ??????????????????????????????
        $this->view->form = new Form\Publish(['hpId' => $this->hp->id, 'params' => $request->all()]);

        $mustPublishArticle = false;
        if ($articlePage && isset($articlePage['public_flg']) && $articlePage['public_flg']) {
            $mustPublishArticle = !$this->page->checkCanPublishArticle($this->view->form, false);
        }
        $errorArticle = false;
        if (!$this->page->checkCanPublishArticle($this->view->form)) {
            $errorArticle = true;
        }
        $this->view->errorArticle = $errorArticle;
        if ($articlePage && isset($articlePage['label']) && $articlePage['label'] == ['check', 'no_diff'] && !$errorArticle && $articleKey) {
            $pages[$articleKey]['label'] = 'no_diff';
            $notDiff = array_filter($pages, function($page) {
                return $page['label'] != 'no_diff';
            });
            if (count($notDiff) == 0) {
                $pages = false;
            }
        }

        $this->view->articlePage = $articlePage;
        $this->view->pages = $pages;
        $this->view->mustPublishArticle = $mustPublishArticle;

        // ????????????????????????
        $special                   = new Special_Prepare_Simple($this->hp);
        $this->view->specialRowset = $special->fetchSpecialRowset();

        // ?????????????????????
        $this->view->form->addSubForm(new Form\PublishSpecial(['specialRowset' => $this->view->specialRowset]), 'special');

        // hp object
        $this->view->hp = $this->hp;

        // ??????????????????
        $this->view->subsutitute = $this->substitute;

        // ??????????????????
        $this->view->hasPrereserved = //
            App::make(ReleaseScheduleRepositoryInterface::class)->checkHasPreserve($this->hp->id) || // ???????????????
            App::make(ReleaseScheduleSpecialRepositoryInterface::class)->checkHasPrereserve($this->hp->id); // ???????????????

        // ??????????????????????????????
        $this->view->hasAutoUpdatePage = $this->page->hasAutoUpdatePage();

        // ????????????????????? or ?????????
        $this->view->displayEstateSettingFlg = (new Estate_Prepare_Simple($this->hp))->isDisplayEstateSetting();

        //?????????????????????
        //????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
        $this->view->displayEstateRequestFlg = false;
        if($this->view->displayEstateSettingFlg) {
            $settingCms = (new Estate_Prepare_Simple($this->hp))->settingCms;
            if($settingCms) {
                $classSearch = App::make(EstateClassSearchRepositoryInterface::class);
                foreach ([1,2,3,4] as $key => $class) {
                    $classSearchRow = $classSearch->getSetting($this->hp->id, $settingCms->id, $class);
                    if($classSearchRow && $classSearchRow->estate_request_flg == 1) {
                        $select = $hpPage->model()->select();
                        $select->where('hp_id', $this->hp->id);
                        switch ($class) {
                            // ????????????????????? ?????????????????????????????????
                            case 1:
                                $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE);
                                break;
                            // ????????????????????? ????????????????????????????????????
                            case 2:
                                $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE);
                                break;
                            // ????????????????????? ?????????????????????????????????
                            case 3:
                                $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY);
                                break;
                            // ????????????????????? ????????????????????????????????????
                            case 4:
                                $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY);
                                break;
                        }
                        $select->where('new_flg', 0);
                        $hpPageRow = $select->first();
                        if(!$hpPageRow) {
                            $this->view->displayEstateRequestFlg = true;
                        }
                    }
                }
            }
        }


        // ?????????????????????
        if ($this->view->pages || count($this->view->specialRowset) > 0) {
            $this->view->hpPageRepository = $hpPage;
            return view('publish.simple.default');
        }

        // ?????????????????????

        // ??????????????????????????????
        $this->view->allupload = false;
        if (($this->hp->all_upload_flg || $this->view->displayEstateSettingFlg)) {
            $params = array_merge($special->generateParams(), $this->page->generateParams());
            $this->view->form->setParams($params);
            $this->view->allupload = $this->view->form->isValid($params);
        }

        // ????????????????????????
        if ($this->view->allupload) {
            return view('publish.simple.allupload');
        }

        // ????????????
        return view('publish.simple.none');
    }

    /**
     * ????????????
     */
    public function detail(Request $request) {

        $this->view->topicPath('????????????(????????????)');

        // ??????????????????
        $user = getInstanceUser('cms')->getProfile();
        if ( $user->reserve_start_date )
        {
        	$this->view->plan_date		= strftime( '%Y???%m???%d???', strtotime( $user->reserve_start_date ) ) ;
        }
        
        // ?????????????????????
        $pages = $this->page->getList();
        $hpPage = App::make(HpPageRepositoryInterface::class);
        $this->view->hpPage = $hpPage;
        $articlePage = null;
        $articleKey = null;
        if ($pages) {
            foreach($pages as $key=>$page) {
                if ($page['page_type_code'] == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) {
                    $articlePage = $page;
                    $articlePage['label'] = [$page['label']];
                    $articleKey = $key;
                    break;
                }
            }
            foreach($pages as $page) {
                if (in_array($page['page_category_code'], $hpPage->getCategoryCodeArticle()) || $hpPage->isLinkArticle($page)) {
                    if ($articlePage && !in_array($page['label'], $articlePage['label']) && $page['label'] != 'no_diff') {
                        $articlePage['label'][] = $page['label'];
                    }
                }
            }
        }
        $this->view->notArticleDisplay = $hpPage->getCategoryCodeArticle();
        $plan =	Plan::factory(Lists\CmsPlan::getCmsPLanName($user->cms_plan));
        $this->view->pageMapArticle = $plan->pageMapArticle;
        $this->view->largeCategoryAllPage = $hpPage->getPageArticleByCategory(HpPageRepository::CATEGORY_LARGE);
        $this->view->categories = $hpPage->getCategoryList();

        // ???????????????????????????
        $this->view->form = new Form\Publish(['hpId' => $this->hp->id, 'params' => $request->all()]);

        $errorArticle = false;
        if ($articlePage && $articlePage['public_flg']) {
            $errorArticle = !$this->page->checkCanPublishArticle($this->view->form, false);
        }
        if ($errorArticle || !$this->page->checkCanPublishArticle($this->view->form)) {
            $errorArticle = true;
        }

        if (isset($articlePage) &&  $articlePage['label'] == ['check'] && !$errorArticle && $articleKey) {
            $pages[$articleKey]['label'] = ['no_diff'];
            $articlePage['label'] = ['no_diff'];
        }
        $this->view->articlePage = $articlePage;
        $this->view->pages = $pages;
        $this->view->errorArticle = $errorArticle;

        // ?????????????????????
        $this->view->specialRowset = (new Special_Prepare_Detail($this->hp))->fetchSpecialRowset();

        // ???????????????????????????
        $this->view->form->addSubForm(new Form\PublishSpecial(['specialRowset' => $this->view->specialRowset]), 'special');

        // hp object
        $this->view->hp = $this->hp;

        // ??????????????????
        $this->view->subsutitute = $this->substitute;

        // ????????????????????????
        $this->view->hasUpdate = $this->page->hasUpdateForDetail();

        // ????????????????????? or ?????????
        $this->view->displayEstateSettingFlg = (new Estate_Prepare_Detail($this->hp))->isDisplayEstateSetting();

        //?????????????????????
        //????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
        $this->view->displayEstateRequestFlg = false;
        $settingCms = (new Estate_Prepare_Simple($this->hp))->settingCms;
        if($settingCms) {
            $classSearch =  App::make(EstateClassSearchRepositoryInterface::class);
            foreach ([1,2,3,4] as $key => $class) {

                $classSearchRow = $classSearch->getSetting($this->hp->id, $settingCms->id, $class);
                if($classSearchRow && $classSearchRow->estate_request_flg == 1) {
                    $select = $hpPage->model()->select();
                    $select->where('hp_id', $this->hp->id);
                    switch ($class) {
                        // ????????????????????? ?????????????????????????????????
                        case 1:
                            $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE);
                            $this->view->hasFormRequestLivinglease = true;
                            break;
                        // ????????????????????? ????????????????????????????????????
                        case 2:
                            $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE);
                            $this->view->hasFormRequestOfficelease = true;
                            break;
                        // ????????????????????? ?????????????????????????????????
                        case 3:
                            $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY);
                            $this->view->hasFormRequestLivingbuy = true;
                            break;
                        // ????????????????????? ????????????????????????????????????
                        case 4:
                            $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY);
                            $this->view->hasFormRequestOfficebuy = true;
                            break;
                    }
                    $select->where('new_flg', 1);
                    $hpPageRow = $select->first();
                    if($hpPageRow) {
                        $this->view->displayEstateRequestFlg = true;
                    }
                }
            }
        }

        // ??????????????????
        $this->view->hasPrereserved =
            App::make(ReleaseScheduleRepositoryInterface::class)->checkHasPreserve($this->hp->id) || // ???????????????
            App::make(ReleaseScheduleSpecialRepositoryInterface::class)->checkHasPrereserve($this->hp->id); // ???????????????

        $prereservedPages = null;
        $this->view->form->setData( // ????????? or ????????????????????????
            $this->view->hasPrereserved && $request->has('testsite') ? // ??????
                array_merge($prereservedPages = $this->page->getReserve()->getPrereserved(), App::make(ReleaseScheduleSpecialRepositoryInterface::class)->fetchAllPrereserve($this->hp->id)->parseToParamsPre()) : // ?????????
                array_merge($this->page->getReserve()->getReserved(), App::make(ReleaseScheduleSpecialRepositoryInterface::class)->fetchAllReserve($this->hp->id)->parseToParams()) // ??????
        );

        $this->view->prereservedPages = $prereservedPages;

        // ??????????????????????????????
        $this->view->hasAutoUpdatePage = $this->page->hasAutoUpdatePage();

        return view('publish.detail');
    }

    /**
     * ??????????????????
     */
    public function testsite(Request $request) {

        if (!$this->page->isValid()) {
            return redirect()->route('default.publish.simple');
        }

        // $this->view->headTitle('??????????????????');
        $this->view->topicPath('??????????????????');

        // ???????????????????????????
        $reserveListPage = $this->page->getList();

        // ???????????????????????????
        $reserveListSpecial = (new Special_Prepare_Testsite($this->hp))->reserveList();

        // ??????????????????
        $releaseAtList = array_unique(array_merge(array_keys($reserveListPage), array_keys($reserveListSpecial)));
        asort($releaseAtList);

        $this->view->form = new Form\Testsite(['releaseAtList' => $releaseAtList]);

        // ?????????
        $list = array_replace_recursive($reserveListPage, $reserveListSpecial);
        ksort($list);

        $this->view->pages = $list;

        return view('publish.testsite');
    }

    public function getLink()
    {
        $type = '';
        if (app('request')->has('top')) {
            $type = 'top';
        } else if (app('request')->has('js')) {
            $type = 'js';
        } else if (app('request')->has('css')) {
            $type = 'css';
        } else if (app('request')->has('images')) {
            $type = 'images';
        }
        
        if ('' == $type) {
            return null;
        }

        if ($this->has('top')) {
            return simpleUrl('src', 'source');
            
        } else {
            $parts = explode('/', parse_url(urldecode(urldecode($_SERVER['REQUEST_URI'])))['path']);
            $paths = array();
        
            do {
                $part = array_pop($parts);
                array_unshift($paths, $part);
            } while ($type != $part);
        
            $data = array('controller' => 'source', 'action' => 'src', 'path' => urlencode(implode('/', $paths)));
            return simpleUrl('src', 'source', ['path' => urlencode(implode('/', $paths))]);
        }
        
        return null;
    }
    /**
     * ????????????????????????
     */
    public function previewPage(Request $request) {
        $redirecTo = $this->getLink();

        if (null != $redirecTo) {
            return redirect($redirecTo);
        }
        
        if (!$this->page->isValid()) {
            $this->_forward404();
        }

		// ATHOME_HP_DEV-5070 : token????????????????????????????????????
        // if($this->getRequest()->isPost()) {
        //     $this->_helper->csrfToken();
        // }
        
        $page_id        = $request->id;
        $parent_page_id = $request->parent_id;

        $pages = $this->page->getAfterPagesForPreview($page_id);

        $page   = null;
        $parent = null;
        if (!$page_id && $parent_page_id) {
            $parent = \App::make(HpPageRepositoryInterface::class)->fetchRow([
                                                                      ['hp_id', $this->hp->id],
                                                                      ['id'   , $parent_page_id],
                                                                  ]);
            $_page  = \Library\Custom\Hp\Page::factory($this->hp, $parent->createDetailRow(), null);
            $_page->init();
            if (!$_page->isValid($request->all())) {
                $this->_forward404();
            }

            $page                              = $_page->getRow();
            $pages[$page_id]                   = $page->toArray();
            $pages[$page_id]['new_path']       = '';
            $pages[$page_id]['id']             = $page_id;
            $pages[$page_id]['public_flg']     = true;
            $pages[$page_id]['parent_page_id'] = $parent_page_id;

            if ($request->tdk) {

                $param = $request->tdk;

                $title = '';
                if (isset($param['title'])) {
                    $title = $param['title'];
                }

                $keywords = '';
                for ($i = 1; $i <= 3; $i++) {
                    if (isset($param['keyword'.$i]) && $param['keyword'.$i] != '') {
                        $keywords .= $param['keyword'.$i].',';
                    }
                }
                $keywords = substr($keywords, 0, -1);

                $description = '';
                if (isset($param['description'])) {
                    $description = $param['description'];
                }

                $filename = '';
                if (isset($param['filename'])) {
                    $filename = $param['filename'];
                }

                $pages[$page_id]['title']       = $title;
                $pages[$page_id]['keywords']    = $keywords;
                $pages[$page_id]['description'] = $description;
                $pages[$page_id]['filename'] = $filename;
            }
        }

        $render = new Content($this->hp->id, config('constants.publish_type.TYPE_PREVIEW'), $this->page);

        /*
         * ATHOME_HP_DEV-4866
         * Top???????????????????????????????????????????????????????????????
         */
        $pubTopSrcPath = null;
        // topOriginal???????????????company.member_no ??????????????????
        if($this->topOriginal && is_null(getUser()->getAdminProfile())) {

            $ds = DIRECTORY_SEPARATOR;

            // Top????????????????????????(company??????)?????????
            $companyId = getUser()->getProfile()->id;
            $pubTopSrcPath = Lists\Original::getOriginalImportPath($companyId);
            if(preg_match("/\/" . $companyId . "$/", $pubTopSrcPath)) {
                $pubTopSrcPath = $pubTopSrcPath . "_published";
            } else if(preg_match("/^(.*)\/" . $companyId . "\/$/", $pubTopSrcPath, $match)) {
                $pubTopSrcPath = $match[1] . $ds . $companyId . "_published";
            } else {
                $pubTopSrcPath = null;
            }

            if(!is_null($pubTopSrcPath) && is_dir($pubTopSrcPath)) {
                $render->setUsePubTop($pubTopSrcPath);
            }
        }

        $render->setPages($pages);

        // ????????????
        \Library\Custom\Publish\Estate\Make\Preview::getInstance()->init($this->hp);

        header('Content-type: text/html');
        header('X-XSS-Protection: 0');
        echo $render->preview($page_id, $request->device, $parent, $request->all());
    }

    /**
     * ajax
     */
    public function apiPublish(Request $request) {

        $this->logger->info('publish-apiPublishAction-start');
        //validation
        if (!$this->page->isValid()) {
            return $this->success([]);
        }
        $from = $request->clickBtn;

        // set params
        $params = $request->all();
        if (strstr($from, 'allupload')) {
            $params = array_merge((new Special_Prepare_Api($this->hp))->generateParams(), $this->page->generateParams());
        }

        // form
        if (preg_match('/^publish-testsite-/', $from)) {

            // from test site
            $form = new Form\Testsite();
        }
        else {
            $form = new Form\Publish(['hpId' => $this->hp->id, 'params' => $params,]);
            $form->addSubForm(new Form\PublishSpecial(['specialRowset' => (new Special_Prepare_Api($this->hp))->fetchSpecialRowset()]), 'special');
        }

        // validation
        $form->setData($params);
        if (!$form->isValid($params)) {
            return $this->success(['errors' => $form->_errors]);
        }

        // save in session
        if (preg_match('/^publish-testsite-/', $from)) {

            $releaseAt = $from === 'publish-testsite-now' ?
                Form\Publish::NOW:
                $params['releaseAt'];
            $this->page->getNamespace('publish')->releaseAt = $releaseAt;
        }
        else {
            $this->page->getNamespace('publish')->params = $params;
        }


        // return
        $data = [];
        if ($from != 'setting-publish-article') {
            switch ($from) {

                case 'publish':
                case 'allupload':
                    $this->page->getNamespace('publish')->publishType = config('constants.publish_type.TYPE_PUBLIC');
                    $data['publish']                     = 'true';
                    break;
    
                case 'publish-testsite-now':
                case 'publish-testsite-reserve':
                    $this->page->getNamespace('publish')->publishType = config('constants.publish_type.TYPE_TESTSITE');
                    $data['publish']                     = 'true';
                    break;
    
                case 'publish-subsutitute':
                case 'publish-allupload-subsutitute':
                    $this->page->getNamespace('publish')->publishType = config('constants.publish_type.TYPE_SUBSTITUTE');
                    $data['publish']                     = 'true';
                    break;
    
                case 'testsite':
                case 'publish-allupload-testsite':
                    $data['url'] = DIRECTORY_SEPARATOR.getControllerName().DIRECTORY_SEPARATOR.'testsite';
                    break;
    
                default:
                    $this->_forward404();
                    break;
            }
        }
        $this->page->setNamespace('publish',  $this->page->getNamespace('publish'));
        $this->logger->info('publish-apiPublishAction-end');
        return $this->success($data);

    }

    
    /**
     *
     * ?????????????????????, ????????????????????????
     *
     */
    public function progress() {
$this->logger->info('publish-progressAction-start');

$this->logger->info('publish-progressAction-validation-start');
        // validation
        if (!$this->page->isValid()) {
            $this->logger->info('publish-progressAction-validation-error');
            $this->_redirectSimple('simple');
        };
$this->logger->info('publish-progressAction-validation-end');
$this->logger->info('publish-progressAction-init-start');

        // init
        set_time_limit(0);
        session_write_close();

        $publishType = $this->page->getNamespace('publish')->publishType;

        $params = $this->page->getNamespace('publish')->params;

        // ????????????(NHP-4617) --??????--
        // $login_id = $this->companyRow->member_no;
        $login_id = '-';
        $cms = getInstanceUser('cms');
		if ($cms->isAgent()) {
			$login_id = $cms->getTantoCD();
		} else if ($cms->isCreator()) {
			$login_id = $cms->getAdminProfile()->login_id;
		}

        $progressTable   = \App::make(PublishProgressRepositoryInterface::class);
        $progressId = $progressTable->createProgress([
            'publish_type' => $publishType,
            'company_id' => $this->companyRow->id,
            'hp_id' => $this->hp->id,
            'login_id' => $login_id,
            'success_notify' => $this->companyRow->publish_notify,
            'all_upload_flg' => $this->hp->all_upload_flg
        ], $this->companyRow->company_name);

        // ?????????????????????
        $finish   = mb_substr_count(file_get_contents(__FILE__), '$progress->update') - 5;
        $progress = $this->progressBar($finish);
        register_shutdown_function([$this, 'shutdownPublish'], $this->logger, $progress, $finish, $progressId  );
        
        $render = new Content($this->hp->id, $publishType, $this->page);
        $render->setProgressAdapter($progress->getAdapter());
        
        $ftp    = new Publish\Ftp($this->hp->id, $publishType);
        
        $cnt = 0;
        $progress->update(++$cnt);

        // ????????????????????????????????????
        $newPages = $this->page->getAfterPages($publishType, $params);
        $render->setPages($newPages);
        $progress->update(++$cnt);

        // ??????
        $currentAt   = isset($this->page->getNamespace('publish')->releaseAt) ? $this->page->getNamespace('publish')->releaseAt : \App\Http\Form\Publish::NOW;
        $reserveList = (new Publish\Special\Prepare\Testsite($this->hp))->reserveList($params);
        $special     = Publish\Special\Make\Rowset::getInstance();
        $special->init($this->hp, $params, $currentAt, $reserveList);
        $progress->update(++$cnt);

        // ????????????
        $estate = Publish\Estate\Make\Publish::getInstance();
        $estate->init($this->hp);
        $progress->update(++$cnt);

$this->logger->info('publish-progressAction-init-end');
        
		$hpPage   = \App::make(HpPageRepositoryInterface::class);

		// NHP-2801 ????????????????????????????????????????????????:?????????????????????Lock????????????
		// ?????????????????? company.full_path ???????????????????????????????????????hp.id????????????compnay.id ??????????????????
        $publishConfig = getConfigs('publish')->publish;
		$getLockKey = sprintf("%s_%d", $publishConfig->lock_key_prefix, $this->companyRow->id);

        try {

            // ATHOME_HP_DEV-5329: ???????????????&?????????????????????????????????
            switch ($publishType) {
                case config('constants.publish_type.TYPE_PUBLIC'):
                    Logger\CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.PUBLISH'));
                    break;
                case config('constants.publish_type.TYPE_TESTSITE'):
                    Logger\CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.PUBLISH_TEST'));
                    break;
                case config('constants.publish_type.TYPE_SUBSTITUTE'):
                    Logger\CmsOperation::getInstance()->creatorLog(config('constants.log_edit_type.CREATOR_TEST'));
                    break;
                default:
                    break;
            }

            // NHP-2801 ????????????????????????????????????????????????:??????????????????
            $row = \DB::select(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
			if(!$row[0]->LOCK_RES) {	// ????????????????????????
				throw new \Exception($publishConfig->exclusive_error_msg);
			}
			// NHP-2801 ????????????????????????????????????????????????:???????????????
			$row = \DB::select(sprintf("SELECT GET_LOCK('%s', %d) AS LOCK_RES", $getLockKey, $publishConfig->lock_wait));
			if(!$row[0]->LOCK_RES) {	// ??????????????????
				throw new \Exception($publishConfig->exclusive_error_msg);
			}

$this->logger->info('publish-progressAction-putHtmlCurrent-start');
            \DB::beginTransaction();

            // db??????html???????????????
            $render->putHtmlFiles();
            $progress->update(++$cnt);

$this->logger->info('publish-progressAction-putHtmlCurrent-end');


            //??????????????????GMO??????????????????
            if ($publishType == config('constants.publish_type.TYPE_PUBLIC')) {
                //?????????function???????????????
                $topPageRow = $hpPage->getTopPageData($this->hp->id);

                //TOP????????????1???????????????????????????????????????www.domain???????????????
                if ($topPageRow->public_flg === 0 && $topPageRow->republish_flg === 0) {
$this->logger->info('publish-progressAction-deleteDomainFiles-start');
                    $cftp = new \Library\Custom\Ftp($ftp->getCompany()->ftp_server_name);
$this->logger->info('publish-progressAction-FTP-SESSION-start');
                    //??????????????????
                    $cftp->login($ftp->getCompany()->ftp_user_id, $ftp->getCompany()->ftp_password);
                    //??????????????????????????????
                    if ($ftp->getCompany()->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE')) $cftp->pasv(true);
                    //?????????????????????????????????????????????
                    $cftp->deleteFolderBelow($ftp->getCompany()->ftp_directory);
                    $cftp->close();
$this->logger->info('publish-progressAction-FTP-SESSION-end');
$this->logger->info('publish-progressAction-deleteDomainFiles-end');
                }
                $progress->update(++$cnt);
            }
$this->logger->info('publish-progressAction-FTP-SESSION-start');
            // ????????????
            $ftp->login();
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-fullpath-start');

            // ????????????
            $ftp->fullPath($publishType);
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-fullpath-end');

            $ftp->close();
$this->logger->info('publish-progressAction-FTP-SESSION-end');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, 'fullpath.php??????');


$this->logger->info('publish-progressAction-render-start');
$progress->getAdapter()->renderingStart();

            // ?????????
            $render->init();
$progress->getAdapter()->polling();

            /*
             * ATHOME_HP_DEV-4866
             * Top???????????????????????????????????????????????????????????????
             */
            $pubTopSrcPath = null;
            // topOriginal???????????????company.member_no ??????????????????
            if($this->topOriginal && is_null(getUser()->getAdminProfile())) {

                $ds = DIRECTORY_SEPARATOR;

                // Top????????????????????????(company??????)?????????
                $companyId = getUser()->getProfile()->id;
                $pubTopSrcPath = Lists\Original::getOriginalImportPath($companyId);
                if(preg_match("/\/" . $companyId . "$/", $pubTopSrcPath)) {
                    $pubTopSrcPath = $pubTopSrcPath . "_published";
                } else if(preg_match("/^(.*)\/" . $companyId . "\/$/", $pubTopSrcPath, $match)) {
                    $pubTopSrcPath = $match[1] . $ds . $companyId . "_published";
                } else {
                    $pubTopSrcPath = null;
                }

                if(!is_null($pubTopSrcPath) && is_dir($pubTopSrcPath)) {
                    $render->setUsePubTop($pubTopSrcPath);
                }
            }
            
            // ???????????????
            $this->logger->infoRender('view::start');
            $render->view();
            $this->logger->infoRender('view::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('script::start');
            $render->script();
            $this->logger->infoRender('script::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('setting::start');
            $render->setting();
            $this->logger->infoRender('setting::end');
            $progress->update(++$cnt);

            // ????????????
            $this->logger->infoRender('directPublic::start');
            $render->directPublic();
            $this->logger->infoRender('directPublic::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('images::start');
            $newImageIds = $render->images();
            $this->logger->infoRender('images::end');
            $progress->update(++$cnt);

            $this->logger->infoRender( 'file2s::start'	) ;
            $newFile2Ids = $render->file2s() ;
            $this->logger->infoRender( 'file2s::end'	) ;
            $progress->update( ++$cnt ) ;
            
            $this->logger->infoRender('files::start');
            $newFileIds = $render->files();
            $this->logger->infoRender('files::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('qrcode::start');
            $render->qrcode();
            $this->logger->infoRender('qrcode::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('logo::start');
            $render->logo();
            $this->logger->infoRender('logo::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('favicon::start');
            $render->favicon();
            $this->logger->infoRender('favicon::end');
            $progress->update(++$cnt);

            //if ($publishType != config('constants.publish_type.TYPE_PUBLIC') || $this->hp->all_upload_flg) {
            $this->logger->infoRender('webclip::start');
            $render->webclip();
            $this->logger->infoRender('webclip::end');
            $progress->update(++$cnt);

            $this->logger->infoRender('js-css-imags-fonts::start');
                $render->js();
                $progress->update(++$cnt);

                $render->css();
                $progress->update(++$cnt);

                $render->imgs();
                $progress->update(++$cnt);

                

                $render->fonts();
                $progress->update(++$cnt);
            $this->logger->infoRender('js-css-imags-fonts::end');

            //}
            if($this->topOriginal){
                $this->logger->infoRender('root-topOrignial::start');
                $render->rootTopOriginal();
                $this->logger->infoRender('root-topOrignial::end');
                $this->logger->infoRender('js-css-imags-topOrignial::start');
                $render->imgsTopOriginal();
                $progress->update(++$cnt);
                $render->cssTopOriginal();
                $progress->update(++$cnt);
                $render->jsTopOriginal();
                $progress->update(++$cnt);
                $this->logger->infoRender('js-css-imags-topOrignial::end');
            }

            if($this->isFDP && getUser()->getProfile()->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')){
                $this->logger->infoRender('js-css-imgs-FDP::start');
                $render->imgsFDP();
                $progress->update(++$cnt);
                $render->cssFDP();
                $progress->update(++$cnt);
                $render->jsFDP();
                $progress->update(++$cnt);
                $this->logger->infoRender('js-css-imgs-FDP::end');
            }
$progress->getAdapter()->renderingEnd();
$this->logger->info('publish-progressAction-render-end');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, '????????????????????????');

            // ????????????????????????????????????(NHP-4617)
            $progressTable->countPages($progressId, $this->hp->id, $publishType);

$this->logger->info('publish-progressAction-zip-start');
            // zip
    
            $zips = $render->getZip();
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-zip-end');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, 'Zip????????????');

$this->logger->info('publish-progressAction-FTP-SESSION-start');
            // ??????????????????
            $ftp->login();
$this->logger->info('publish-progressAction-uploadZip-start');

            foreach ($zips as $zip) {
            list($uploadres, $remoteFile) = $ftp->uploadZip($zip);

            if(!$uploadres) {
                // ??????FTP?????????????????????
                $ftp->close(true);	// ????????????
                $ftp->login();

                exec("ls -l $zip | awk '{print $5}'", $res);
                $localSize = $res[0];
                $remoteSize = $ftp->getSize($remoteFile);

                if($localSize != $remoteSize) {
                    $msg = '??????????????????????????????????????????';
                    throw new \Exception($msg);
                }
            }
$progress->getAdapter()->polling();
            }
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-uploadZip-end');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, 'Zip????????????????????????');

$this->logger->info('publish-progressAction-commitZip-start');
//            exit;
            // ??????
            $ftp->commit($publishType);
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-commitZip-end');

            // ???????????????
            $ftp->close();
            $progress->update(++$cnt);
$this->logger->info('publish-progressAction-FTP-SESSION-end');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, 'commit.php????????????');

$this->logger->info('publish-progressAction-after-start');

            // ??????
            switch ($publishType) {
                case config('constants.publish_type.TYPE_PUBLIC'):
                case config('constants.publish_type.TYPE_SUBSTITUTE'):
$this->logger->info('publish-progressAction-after-----1');

                    // html????????????
                    $render->updateHtmlFiles();
                    $progress->update(++$cnt);

$this->logger->info('publish-progressAction-after-----2');
                    $render->zipHtml();

                    $progress->update(++$cnt);

$this->logger->info('publish-progressAction-after-----3');
                    // db??????
                    $updatePageIds = $this->page->getUpdatedPageIds();

$this->logger->info('publish-progressAction-after-----4');

                    $release = isset($updatePageIds['release']) ? $updatePageIds['release'] : [];
                    $close   = isset($updatePageIds['close']) ? $updatePageIds['close'] : [];

                    $this->page->updatePage($newPages, $release, $close);
                    $progress->update(++$cnt);
$this->logger->info('publish-progressAction-after-----5');

                    // ?????????????????????
                    if ($estate->estateSetting instanceof \App\Models\HpEstateSetting) {
$this->logger->info('publish-progressAction-after-----6');

                        // ????????????????????????????????????????????????
                        \App::make(HpPageRepositoryInterface::class)->updateStatuseEstateContactPageAll($this->hp->id);

                        $ids = [];
                        if (isset($params['special'])) {
                            foreach ($params['special'] as $id => $value) {

                                if (!$value['update']) {
                                    continue;
                                }

                                // simple
                                if (!isset($value['new_release_flg'])) {
                                    $ids[] = $id;
                                    continue;
                                }

                                // detail release
                                if ($value['new_release_flg'] && !$value['new_release_at']) {
                                    $ids[] = $id;
                                    continue;
                                }

                                // close release
                                if ($value['new_close_flg'] && !$value['new_close_at']) {
                                    $ids[] = $id;
                                    continue;
                                }
                            }
                        }
                        \App::make(SpecialEstateRepositoryInterface::class)->updatePublishedAt($ids);
                        $estate->estateSetting->copyToPublic($special->filterPublicIds());
                    }
                    $progress->update(++$cnt);
$this->logger->info('publish-progressAction-after-----7');
                    // ??????
                    $this->page->getReserve()->updateReserve();
                    $progress->update(++$cnt);
$this->logger->info('publish-progressAction-after-----8');
                    // ??????????????????
                    \App::make(ReleaseScheduleSpecialRepositoryInterface::class)->saveReserve($this->hp, isset($params['special']) ? $params['special'] : []);
                    $progress->update(++$cnt);
$this->logger->info('publish-progressAction-after-----9');
					$this->page->updateHp( $newImageIds, $newFile2Ids, $newFileIds );
                    $progress->update(++$cnt);
$this->logger->info('publish-progressAction-after-----10');
                    break;

                case config('constants.publish_type.TYPE_TESTSITE'):
                    // ???????????????
                    $this->page->getReserve()->savePrereserved();
                    $progress->update(++$cnt);

                    // ?????????????????????
                    \App::make(ReleaseScheduleSpecialRepositoryInterface::class)->savePrereserve($this->hp, isset($params['special']) ? $params['special'] : []);
                    $progress->update(++$cnt);

                    // ?????????????????????
                    if ($estate->estateSetting instanceof \App\Models\HpEstateSetting) {
                        $estate->estateSetting->copyToTest($special->filterPublicIds(), $special->reserveList);
                    }
                    break;
            }

            $progress->update(++$cnt);
            $this->logger->info('publish-progressAction-after-----11');

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, '?????????????????????????????????');

            // ?????????
            $render->afterPublish();
            $progress->update(++$cnt);

            // ??????????????????(NHP-4617)
            $progressTable->updateProgress($progressId, '???????????????');

            $progress->update(++$cnt);
            $this->logger->info('publish-progressAction-after-----12');

            // ??????
            $progress->update(++$cnt, $this->page->hpUrl());
            $progress->finish();

            //?????????????????????
            $company = \App::make(CompanyRepositoryInterface::class);
            $company->registFirstPublish($publishType, getUser()->getProfile()->id);

            $this->hp->updateChangeSetLink(0);

            $this->page->unsetNamespace('publish');

			\DB::commit();

            // ?????????????????????????????????=1 (NHP-4617)
            $progressTable->publishFinish($progressId, 1);

			// NHP-2801 ????????????????????????????????????????????????:???????????????
			$stmt = \DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));

        } catch (\Exception $e) {
        	error_log(print_r($e->getMessage(),1));

            // ???????????????????????????????????????????????????(NHP-4617)
            $progRow = $progressTable->find($progressId);

        	\DB::rollback();

            // ?????????????????????????????????=0 (NHP-4617)
            // $progressTable->publishFinish($progressId, 0, $e->getMessage());
            // ???????????????????????????????????????
            $date = new \DateTime();
            $progRow->status = 0;
            $progRow->progress.= sprintf("[%s] %s\n", $date->format('Y-m-d H:i:s'), '????????????');
            $progRow->finish_time = $date->format('Y-m-d H:i:s');
            $progRow->exception_msg = $e->getMessage();
            $progRow->save();

            // NHP-2801 ????????????????????????????????????????????????:???????????????     
			$stmt = \DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));

        	$exception = array(
        				'message' => $e->getMessage(),
        				'file' => $e->getFile(),
        				'line' => $e->getLine(),
        				'trace' => $e->getTraceAsString()
            );
        	error_log(mb_convert_encoding(print_r($exception,1), 'UTF-8', 'UTF-8,eucJP-win,SJIS-win'));
            $this->logger->error(print_r($exception,1));
        	if ($ftp->isLogin) {
                $ftp->close();
            };

            $progress->update($finish, '[error_msg]'.$e->getMessage());
            $progress->update($finish, 'error');
            // $progress->finish();
        }
$this->logger->info('publish-progressAction-after-end');
$this->logger->info('publish-progressAction-end');
$this->logger->setStatus(	Logger\Publish::PUBLISH_NORMALY	)	;
    }

    private function progressBar($finish) {

        $methods = [
            'updateMethodName' => 'progressUpdate',
            'finishMethodName' => 'progressFinish',
        ];
        $adapter = new JsPush($methods);
        return new ProgressBar($adapter, 0, $finish);
    }

    /**
     * ????????????????????????????????????????????????????????????
     *
     * @param $toppageRow
     *
     * @return bool
     */
    private function notPublishYet($toppageRow) {

        return $toppageRow->public_flg === 0 && $toppageRow->republish_flg === 0;
    }
    /**
     * ????????????????????? ajax
     * ???????????????????????????
     * - ?????????????????????
     * - ?????????????????? ON
     * HTML?????????
     */
    public function siteDelete() {

        // init
        set_time_limit(0);
        session_write_close();

        // ?????????????????????
        $finish   = mb_substr_count(file_get_contents(__FILE__), '$progress->update') - 6;
        $progress = $this->progressBar($finish);

        $cnt = 0;
        $progress->update(++$cnt);

        try {
            $company    = \App::make(CompanyRepositoryInterface::class);
            $companyRow = $company->getDataForId(getUser()->getProfile()->id);
            $progress->update(++$cnt);

            $table   = \App::make(HpPageRepositoryInterface::class);

            // ATHOME_HP_DEV-5329  ???????????????&?????????????????????????????????
            Logger\CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.SITE_DELETE'));

            // NHP-2801 ????????????????????????????????????????????????:??????????????????
            $publishConfig = getConfigs('publish')->publish;
            $getLockKey = sprintf("%s_%d", $publishConfig->lock_key_prefix, $companyRow->id);
            $row = \DB::select(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
            if(!$row[0]->LOCK_RES) { // ????????????????????????
                throw new \Exception($publishConfig->exclusive_error_msg);
            }
            // NHP-2801 ????????????????????????????????????????????????:???????????????
            $row = \DB::select(sprintf("SELECT GET_LOCK('%s', %d) AS LOCK_RES", $getLockKey, $publishConfig->lock_wait));
            if(!$row[0]->LOCK_RES) { // ??????????????????
                throw new \Exception($publishConfig->exclusive_error_msg);
            }

            \DB::beginTransaction();
            $progress->update(++$cnt);

            $hpTable      = \App::make(HpRepositoryInterface::class);
            $reserveTable = \App::make(ReleaseScheduleRepositoryInterface::class);
            $progress->update(++$cnt);

            // ??????????????????????????????????????????
            $companyRow->deletePublicSearch();

            //???????????????????????????
            $companyRow->deletePublicSpecial();

            $hp = array();
            if ($row = $companyRow->getCurrentHp()) {
                $hp[] = $row;
            }
            $progress->update(++$cnt);

            // Get id list global navigation
            if ($this->topOriginal) {
                $globalNavs = $row->getGlobalNavigation()->toSiteMapArray();
                $listGlobals = [];
                foreach ($globalNavs as $global) {
                    $listGlobals[] = $global["id"];
                }
            }

            foreach ($hp as $row) {

                $row->all_upload_flg = 1;
                $row->setAllUploadParts('ALL', 1);
                $row->save();

                // Except global navigation page when set private
                if ($this->topOriginal && !empty($listGlobals)) {
                    $table->update(array(['hp_id', $row->id], 'whereNotIn' => ['id', $listGlobals]), array('public_flg' => 0, 'public_path' => NULL));
                    $reserveTable->update(array(['hp_id', $row->id], 'whereNotIn' => ['id', $listGlobals]), array('delete_flg' => 1));
                } else {
                    $table->update(array(['hp_id', $row->id]), array('public_flg' => 0, 'public_path' => NULL));
                    $reserveTable->update(array(['hp_id', $row->id]), array('delete_flg' => 1));
                }
                $progress->update(++$cnt);
            }

            $ftp    = new Publish\Ftp($this->hp->id, config('constants.publish_type.TYPE_PUBLIC'));
            $cftp = new \Library\Custom\Ftp($ftp->getCompany()->ftp_server_name, $ftp->getCompany()->ftp_server_port, 120);
            $progress->update(++$cnt);

            //??????????????????
            $cftp->login($ftp->getCompany()->ftp_user_id, $ftp->getCompany()->ftp_password);
            $progress->update(++$cnt);

            //??????????????????????????????
            if ($ftp->getCompany()->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE')) $cftp->pasv(true);
            $progress->update(++$cnt);

            //?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
            //$cftp->deleteFolderBelow($companyRow->ftp_directory);

            $pre_dir = $cftp->pwd();
            $progress->update(++$cnt);

            //HTML????????????????????????????????????????????????
            $cftp->chdir($ftp->getCompany()->ftp_directory);
            $progress->update(++$cnt);

            $list = $cftp->rawlist("./");
            $progress->update(++$cnt);

            foreach ($list as $key => $val) {

                $progress->update(++$cnt);

                $child = preg_split("/\s+/", $val);
                $progress->update(++$cnt);
                if ($child[8] == "." || $child[8] == "..") continue;
                if ($child[0][0] === "d") {
                    $cftp->rmdir($child[8]);
                }else{
                    $cftp->delete($child[8]);
                }
                $progress->update(++$cnt);
            }

            $cftp->chdir($pre_dir);
            $progress->update(++$cnt);

            \DB::commit();

            // NHP-2801 ????????????????????????????????????????????????:???????????????
            $stmt = \DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));

            $progress->update(++$cnt);

        } catch (\Exception $e) {
            \DB::rollback();

            // NHP-2801 ????????????????????????????????????????????????:???????????????
            $stmt = \DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));

            $progress->update($finish, '[error_msg]'.$e->getMessage());
            $progress->update($finish, 'error');
            throw $e;
        }
        $progress->update(++$cnt);
        $progress->finish();
    }

    /**
     * ???????????????????????????????????????????????? 
     */
    function shutdownPublish($logger, $progress, $finish, $progressId )
    {
        $status		= $logger->getStatus()							;
        
        // ??????????????????????????????????????????????????????
        if ( ( \App::environment() != "production" ) || ( $logger->isMonitorKaiin() ) )
        {
            $message	= "End of publish( status = {$status} )"		;
            $logger->info(			$message	) ;
            $logger->infoRender(	$message	) ;
        }
        if ( $status == Logger\Publish::PUBLISH_ABNORMALY )
        {
            $progressTable   = \App::make(\App\Repositories\PublishProgress\PublishProgressRepositoryInterface::class);

            $progressTable->publishFinish($progressId, 0, "?????????????????????????????????????????????");
            // ???????????????????????????????????????????????????(NHP-4617)
            $progRow = $progressTable->find($progressId);

            \DB::rollback();

            $date = new \DateTime();
            $progRow->status = 0;
            $progRow->progress = sprintf("[%s] %s\n", $date->format('Y-m-d H:i:s'), '????????????');
            $progRow->finish_time = $date->format('Y-m-d H:i:s');
            $progRow->exception_msg = "?????????????????????????????????????????????";
            $progRow->save();        

            $progress->update( $finish, '[error_msg]' . "?????????????????????????????????????????????" ) ;
            $progress->update( $finish, 'error' ) ;
        }
    }

}
