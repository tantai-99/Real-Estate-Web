<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;
use Exception;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists\InfoDatailType;
use Library\Custom\Hp\Page;
use Illuminate\Routing\Redirector;
use Library\Custom\User\Cms;
use Illuminate\Support\Facades\DB;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpImage\HpImageRepository;
use App\Repositories\HpFile2\HpFile2Repository;
use Library\Custom\Hp\Page\Parts\Element\Terminology;
use Library\Custom\Model\Lists\Original;
use Library\Custom\User\UserAbstract;
use App\Http\Form\EstateSetting\Special;
use App\Http\Form\EstateSetting\SpecialMethod;
use Library\Custom\Model\Estate\SearchTypeCondition;
use Library\Custom\Model\Estate\ShumokuSort;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SearchTypeList;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\SpecialPublishEstateList;
use Library\Custom\Model\Estate\SpecialTesuryoKokokuhiList;
use Library\Custom\Model\Estate\SpecialSearchPageTypeList;
use Library\Custom\Estate\Setting;
use Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Controller\Action\InitializedCompany;
use App\Traits\JsonResponse;
use App\Exceptions\TopSaveException;
use Library\Custom\Mail;
use App\Repositories\Company\CompanyRepositoryInterface;

class PageController extends InitializedCompany {
    use JsonResponse;
	/**
	 * 
	 * @var Library\Custom\Hp\Page
	 */
    private $page;

    // ATHOME_HP_DEV-3126
    private $hp;

    protected function _thenNotInitialized($request, $next, $hp) {
    	$action = getActionName();
        if (($action == 'api-validate' || $action == 'apiValidate' || $action == 'api-test-mail' || $action == 'apiTestMail') && $hp) {
            return $next($request);
        }

        return parent::_thenNotInitialized($request, $next, $hp);
    }
    
    public function init($request, $next) {
        if (getUser()->isCreator() && getUser()->hasBackupData()) {
            return redirect('/');
        }
        
        $actionName = getActionName();
        if ($actionName == 'api-validate-terminology' ||
            $actionName == 'api-test-mail' || $actionName == 'apiValidateTerminology' || $actionName == 'apiTestMail'
        ) {
            return $next($request);
        }

        $hp = getUser()->getCurrentHp();
        if($hp){
            $id = $request->id;
            $parent_id = $request->parent_id;
            $typeInfoDetail = $request->type;
            
            $row = null;
            $parentRow = null;
            
            if ($id) {
            	$where = [
            			['id', (int) $request->id],
            			['hp_id', $hp->id],
            	];
            	
            	if (!$row = App::make(HpPageRepositoryInterface::class)->fetchRow($where)) {
                    // $this->_redirectSimple('index', 'site-map');
                    // $redirect->to('/site-map')->send();
                    return redirect('/site-map');
            	}
                if ($row->isDetailPageType() && !$parent_id && $row->parent_page_id) {
                    $parentRow = App::make(HpPageRepositoryInterface::class)->fetchRow(array(['id', $row->parent_page_id], ['hp_id', $hp->id]));
                    
                    if (!$parentRow) {
                        // $this->_redirectSimple('index', 'site-map');
                        // $redirect->to('/site-map')->send();
                        return redirect('/site-map');
                    }
                }
            }
            
            if ($parent_id) {
            	// 親行チェック
            	$parent_id = (int)$parent_id;
            	$parentRow = App::make(HpPageRepositoryInterface::class)->fetchRow(array(['id', $parent_id], ['hp_id', $hp->id]));
            	if (!$parentRow) {
                    // $this->_redirectSimple('index', 'site-map');
                    // $redirect->to('/site-map')->send();
                    return redirect('/site-map');
            	}
            	
            	// タイプチェック
            	if (!$parentRow->hasDetailPageType()) {
            		throw new Exception('invalid parent page type');
            	}
            	
            	// 子ページ新規作成の場合
            	if (!$row) {
            	
                    if ($parentRow->page_type_code == HpPageRepository::TYPE_INFO_INDEX) {
                        if ($typeInfoDetail == InfoDatailType::ONLY_ADD_LIST) {
                            $row = $parentRow->createInfoDetailRow(1);
                        } else {
                            $row = $parentRow->createInfoDetailRow();
                        }
                    } else {
                        // 新規行作成
            		    $row = $parentRow->createDetailRow();
                    }
            		
            	}
            	else {
            		
            		// 親子関係チェック
            		if ($parentRow->getDetailPageTypeCode() != $row->page_type_code) {
            			throw new Exception('invalid parent page type');
            		}
            	}
            	
            }
            if (!$row) {
                // $this->_redirectSimple('index', 'site-map');
                // $redirect->to('/site-map')->send();
                return redirect('/site-map');
            }
            
            //既に登録されているデータに対してプリセットを設定する
            if($row->title == "")       $row->title       = App::make(HpPageRepositoryInterface::class)->getTypeNameJp($row->page_type_code);
            if($row->description == "") $row->description = App::make(HpPageRepositoryInterface::class)->getDescriptionNameJp($row->page_type_code);
            if($row->keywords == "")    $row->keywords    = App::make(HpPageRepositoryInterface::class)->getKeywordNameJp($row->page_type_code);
            if($row->filename == "")    $row->filename    = App::make(HpPageRepositoryInterface::class)->getPageNameJp($row->page_type_code);

            $this->page = Page::factory($hp, $row, $parentRow);

            // ATHOME_HP_DEV-3126 hpをプロパティに設定
    		$this->hp = $hp;
            return $next($request);
        }
        return parent::init($request, $next);
    }

    public function edit(Request $request) {
        $hp = getUser()->getCurrentHp();
        $this->page->init();

        // セッションのトークンを再生成する
        $new_token = getUser()->regenerateCsrfToken();

        try{
            if (App::make(HpPageRepositoryInterface::class)->isEstateContactPageType($this->page->getType())) {
                $this->page->forceLoad();
            }
            $this->page->load();
        }
        catch(Exception $ex){
            $this->moveSearchPartElements($ex);
        }
        $this->view->page = $this->page;
        $this->view->createableMainParts = $this->page->getCreateableMainParts();
        $this->view->createableSideParts = $this->page->getCreateableSideParts();
        $this->view->displayFreeword = $this->checkDisplayFreeword($this->page->getHpId());
        
        // 未設定のシステム画像を設定
        
        App::make(HpImageRepositoryInterface::class)->addSysImages(
            $this->page->getHpId(),
            HpImageRepository::TYPE_SAMPLE, null, 
            glob(storage_path('data/samples/images'.DIRECTORY_SEPARATOR.'*.*')));

        $this->view->sampleImageMap = App::make(HpImageRepositoryInterface::class)->getSysImageMap(
            $this->page->getHpId(),
            HpImageRepository::TYPE_SAMPLE);

        App::make(HpFile2RepositoryInterface::class)->initSysFile2s($this->hp->id);
        $this->view->sampleFile2sMap = App::make(HpFile2RepositoryInterface::class)->getSysFile2Map(
            $this->page->getHpId(),
            HpFile2Repository::TYPE_SAMPLE);
        
        $this->view->terminologyForm = new Terminology();
        
        $this->view->topicPath('ページの作成/更新', 'index', 'site-map');
        if ($this->page->isArticlePage()) {
            $this->view->topicPath('ページの作成/更新 （不動産お役立ち情報）', 'article', 'site-map');
        }

        $isTopOriginal = $this->page->isTopOriginal();
        $hasParent = $this->page->isDetailPageType();
        $parentTitle = ($hasParent)?$this->page->getParentTitle():'';

        $disableTitle = false;
        $typePage = $this->page->getType();
        if ($typePage == HpPageRepository::TYPE_INFO_DETAIL) {
            $pageTitle = $this->page->getInfoDeatailTitle($this->page->getTypeInfoDetail());
        } else {
            $pageTitle = $this->page->getTitle();
            if ($this->page->getRow()->page_type_code == HpPageRepository::TYPE_MOVING && $this->page->getRow()->page_category_code != HpPageRepository::CATEGORY_ARTICLE) {
                $pageTitle = "引っ越しのチェックポイント";
            }
            if ($this->page->getRow()->page_type_code == HpPageRepository::TYPE_BUILDING_EVALUATION && $this->page->getRow()->page_category_code != HpPageRepository::CATEGORY_ARTICLE) {
                $pageTitle = "中古戸建てはどのように評価されるのか？";
            }
        }

        $table = App::make(HpPageRepositoryInterface::class);
        $siteMaps = $table->fetchSiteMapRows($this->page->getHpId());
        $this->view->siteMapData = $siteMaps->toSiteMapArray();
        $siteMapIndex = $table->fetchSiteMapIndexRows($this->page->getHpId());
        $this->view->siteMapIndexData = $siteMapIndex->toSiteMapIndexArray();
        $this->view->articleCategories = $table->getCategoryCodeArticle();
        $this->view->templateArticle = json_decode(@file_get_contents(storage_path('data/samples/TemplateArticlePage.json')), true);
        if ($setting = $this->page->getHp()->getEstateSetting()) {
            $this->view->estateSiteMapData = $setting->toSiteMapData();
        }

        if($isTopOriginal){

            if($typePage == HpPageRepository::TYPE_TOP){
                $pageTitle = config('constants.original.TOP_CONTENT');
            }

            $pageChangedTitle = Original::getChangedTitlePages();
            if(in_array($typePage,$pageChangedTitle)){
                $setting = App::make(HpMainPartsRepositoryInterface::class)->getSettingForNotification(
                    ($hasParent)?$this->page->getParentRow()->link_id:$this->page->getRow()->link_id,
                    $this->page->getHpId()
                );
                if($setting) {
                    $notificationType = Original::$EXTEND_INFO_LIST['notification_type'];
                    $settingData = Original::getInfoPageName($setting->$notificationType);
                    if($typePage == HpPageRepository::TYPE_INFO_INDEX){
                        if(isset($settingData[$typePage])){
                            $pageTitle = $settingData[$typePage];
                        }
                    }
                    else if ($typePage == HpPageRepository::TYPE_INFO_DETAIL){
                        $parentTitle = $settingData[$this->page->getParentRow()->page_type_code];
                    }
                }
            }

            if(!UserAbstract::getInstance()->isAgency()){
                if($this->page->isGlobalNav()){
                    $disableTitle = true;
                }
            }
        }
        $isSeo = true;
        $typeCode = $this->page->getRow()->page_type_code;
        if (in_array($typeCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_LARGE)) || in_array($typeCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_TOP_ARTICLE)) ||in_array($typeCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_SMALL)) || in_array($typeCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))) {
            $isSeo = false;
        }
        $this->view->isSeo = $isSeo;

        $this->view->disableTitle = $disableTitle;


        if ($hasParent) {
            $this->view->topicPath($parentTitle, 'edit', 'page', array('id'=>$this->page->getParentId()));
        }

        $this->view->topicPath($pageTitle);

        $this->view->pageTitle = $pageTitle;
        $this->view->isTopOriginal = $isTopOriginal;

        // TOPページかの判定
        $isTopPage = false;
		if($this->page->getType() == HpPageRepository::TYPE_TOP) {
            $isTopPage = true;
		}
        $this->view->isTopPage = $isTopPage;

        // ATHOME_HP_DEV-3126 全公開フラグの判定
        $this->view->all_upload_flg = 0;
        if(isset($this->hp->all_upload_flg) && $this->hp->all_upload_flg == 1) {
            $this->view->all_upload_flg = $this->hp->all_upload_flg;
        }

        // ATHOME_HP_DEV-5186 親情報を持っている場合にはポップアップCを出さない
		$this->view->hasParent = 0;
		if(isset($hasParent) && $hasParent == 1) {
			$this->view->hasParent = $hasParent;
        }
        
        // ATHOME_HP_DEV-4794 物件詳細URL機能を追加する - start
        $hp = getUser()->getCurrentHp();
        $setting = $hp->getEstateSetting();
        $this->view->hasSearchSetting = 0;
        if ($setting) {
            // ベースとなる物件検索設定を全て取得
            $searchSettings = $setting->getSearchSettingAll();

            $baseSettings = [];
            foreach ($searchSettings as $searchSettingRow) {
                $searchSetting = $searchSettingRow->toSettingObject();
                $baseSettings[ $searchSetting->estate_class ] = $searchSetting;
            }
            if (count($baseSettings) > 0) {
                $this->view->hasSearchSetting = 1;
            }
            $this->view->baseSettings = $baseSettings;
            
            $this->view->form = new Special([
                'hpId' => $hp->id,
                'settingId' => $setting->id,
                'searchSettings' => $searchSettings]);

            $this->view->formMethod = new SpecialMethod([
                'hpId' => $hp->id,
                'settingId' => $setting->id,
                'searchSettings' => $searchSettings]);
            $this->view->searchTypeConditionMaster = SearchTypeCondition::getInstance()->getAll();
            $selShumoku = [];
            if(isset($settingTmp->categories) && isset($settingTmp->categories[0]) && $settingTmp->categories[0]->category_id == 'shumoku') {
                foreach($settingTmp->categories[0]->items as $val) {
                    $selShumoku[] = $val->item_id;
                }
            }
            $shumoku_sort = ShumokuSort::getInstance()->getAll();
            $shumokuTypeMaster = [];
            for($eno = 1; $eno < 13; $eno++) {
                if(!isset($shumoku_sort[ $eno ])) {
                    continue;
                }

                $searchFilter = new SearchFilter\Special();
                $searchFilter->loadEnables($eno);
                $searchFilter->asMaster();
                if($searchFilter->categories[0]->category_id != 'shumoku') {
                    continue;
                }
                if(count($searchFilter->categories[0]->items) == 0) {
                    continue;
                }
                $shumokuTypeMaster[ $eno ] = [];

                $sType = [];
                foreach($searchFilter->categories[0]->items as $item) {
                    $checked = (in_array($item->item_id, $selShumoku)) ? '1' : '0';
                    $sType[ $item->item_id ] = [ 'item_id' => $item->item_id,  'label' => $item->label, 'checked' => $checked ];
                }
                foreach($shumoku_sort[ $eno ] as $item_id) {
                    if(isset($sType[ $item_id ])) {
                        $shumokuTypeMaster[ $eno ][] = $sType[ $item_id ];
                    } else if(gettype($item_id) == 'string') {
                        $shumokuTypeMaster[ $eno ][] = $item_id;
                    }
                }
                $searchFilter = null;
            }
            $this->view->shumokuTypeMaster = $shumokuTypeMaster;
            // マスタ
            $this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
            $this->view->searchTypeMaster = SearchTypeList::getInstance()->getAll();
            $this->view->searchTypeConditionMaster = SearchTypeCondition::getInstance()->getAll();
            $this->view->searchTypeDirectMaster = SearchTypeList::getInstance()->getAllForSpecialDirect();
            $this->view->searchTypeConst  = SearchTypeList::getInstance()->getKeyConst();
            $this->view->estateTypeMaster = TypeList::getInstance()->getAll();
            $this->view->specialPublishEstateMaster = SpecialPublishEstateList::getInstance()->getAll();
            $this->view->specialTesuryoKokokuhiMaster = SpecialTesuryoKokokuhiList::getInstance()->getAll();
            $this->view->specialSearchPageTypeMaster = SpecialSearchPageTypeList::getInstance()->getAll();
            
            $specialSetting = new Setting\Special();
            $this->view->specialSetting = $specialSetting;
        }
        $this->view->params = $request->all();
        return view('page.edit');
        // ATHOME_HP_DEV-4794 物件詳細URL機能を追加する - end
    }

    public function apiValidateTerminology(Request $request) {
    	$form = new Terminology();
    	$data = $request->all();
    	$data['sort'] = 0;
    	$data['type'] = 'terminology';
        $form->setData($data);
        $cmsPlan = getUser()->getProfile()->cms_plan;
    	if (!$form->isValid($data)) {
    		// $this->data->errors = $form->getMessages();
            return $this->success(['errors' => $form->getMessages(), 'cms_plan' => $cmsPlan]);
    	}
        return $this->success(['cms_plan' => $cmsPlan]);
    }

    public function apiValidate(Request $request) {
    	$cmsPlan = getUser()->getProfile()->cms_plan;
    	$this->page->init();
        if (!$this->page->isValid($request->all())) {
            return $this->success(['errors' => $this->page->getMessagesById()]);
    	}
        return $this->success([],$cmsPlan);
    }
    
    public function apiSave(Request $request) {

        // $this->_helper->csrfToken();

        $this->page->init();

        //既に容量が超えている場合はエラーとする
        $hp = getUser()->getCurrentHp();
        if($hp->capacityCalculation() > config('constants.hp.SITE_OBER_CAPASITY_DATAMAX')) {
            $errors = array("over_capacity" => array("data_max" => "容量が". config('constants.hp.SITE_OBER_CAPASITY_DATAMAX')  ."MBを超えています。不要な画像などを削除してください。"));
            return $this->success(['errors' => $errors]);
        }

        if (!$this->page->isValid($request->all())) {
        	$errors = $this->page->getMessagesById();
        	return $this->success(['errors' => $errors]);
        }

        DB::beginTransaction();
        try {
        
            $this->page->save();
            $data = [];
            $data['cms_plan'] = $this->page->getCompany()->cms_plan;
            $info = $this->page->getEditInfo();
        	
        	DB::commit();
        } catch (TopSaveException $e) {
        	DB::rollback();
        	throw $e;
        } catch (Exception $e) {
        	DB::rollback();
        	throw $e;
        }
        $data['info'] = $info;
        return $this->success($data);
    }

    public function apiDelete(Request $request) {

        // $this->_helper->csrfToken();
        
        if (!$this->page->canDelete()) {
        	$error = '削除できません。';
            return $this->success(['error' => $error]);
        }

        if (!$this->page->canDeleteEstateRequest()) {
            $error = '「物件検索設定」にて物件リクエスト選択中のため、削除ができません。';
            return $this->success(['error' => $error]);
        }
        
        $row = $this->page->getRow();
        if ($row->id && $row->hasDetailPageType() && $row->hasChild()) {
        	$error = '詳細ページが存在する為、削除できません。';
        	return $this->success(['error' => $error]);
        }

        if ($this->page->isScheduled()) {
            $error = '公開予約中のため削除できません。';
            return $this->success(['error' => $error]);
        }

        $table   = App::make(HpPageRepositoryInterface::class);
        if ($this->page->isArticlePage()) {
            $hp = getUser()->getCurrentHp();
            $pages = $table->fetchAll(array(
                ['hp_id', $hp->id],
                'whereIn' => [
                    'page_category_code',
                    $table->getCategoryCodeArticle()
                ],
            ))->toSiteMapArray();
            if (!$this->page->canDeleteCategoryArticlePage($pages)) {
                $error = '配下にページが存在するため削除できません。このページを削除する場合は、配下のページを削除してください。';
                return $this->success(['error' => $error]);
            }
            if (!$this->page->canDeleteArticlePage($pages)) {
                // $this->data->errorArticle = true;
                $error = 'このページを削除する場合、上位のカテゴリーページも合わせて削除が必要となります。<br>ページの作成/更新（不動産お役立ち情報）内「不要なページをまとめて削除する」より削除してください。';
                return $this->success(['error' => $error, 'errorArticle' => true]);
            }
        }
        
		// $adapter = $table->getAdapter();
        DB::beginTransaction();
        $data = [];
        try {
		    // $adapter->beginTransaction();
            
		    $this->page->deletePage();
		     
		    // $adapter->commit();
            DB::commit();
	    } catch (Exception $e) {
		    // $adapter->rollback();
            DB::rollback();
		    throw $e;
	    }
        if ($this->page->isArticlePage()) {
            $data['url'] = '/site-map/article';
        } else {
            $data['url'] = '/site-map';
        }
        
        return $this->success($data);
    }
    
    public function apiTestMail(Request $request) {
    	// $this->_helper->csrfToken();
    	
    	$mail = new Mail();
    	try {
			$profile = getUser()->getProfile();
			$company  = App::make(CompanyRepositoryInterface::class)->getDataForId($profile->id);

			$mailTo = $request->to;
    		$mail->addTo($mailTo);
    		$mail->setFrom($mailTo[0]);////fromアドレスはCMSで登録した加盟店のToアドレスの先頭とする仕様
    		$mail->setSubject('テストメール');
    		$mail->setbodyFromTemplate('test_mail', array('memberName'=>$company['member_name']));
    		$mail->send();

            return $this->success([]);
    	}
    	catch (Exception $e) {
    		$error = 'メール送信に失敗しました。';
            return $this->success(['error' => $error]);
    	}
    }

    /**
     * remove search element part for plan lite
     * @param $ex
     * @return void
     */
    protected function moveSearchPartElements($ex) {
        $cms_plan =Cms::getInstance()->getProfile()->cms_plan ;
        if ($cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') && $ex->getMessage() == 'invalid parts type') {
            DB::beginTransaction();
            $actual_link = $_SERVER['REQUEST_URI'];
            App::make(HpMainPartsRepositoryInterface::class)->delete(array(['hp_id', $this->page->getHpId()], 'whereIn' => ['parts_type_code', array(HpMainPartsRepository::PARTS_FREEWORD, HpMainPartsRepository::PARTS_ESTATE_KOMA)]));
            App::make(HpSidePartsRepositoryInterface::class)->delete(array(['hp_id', $this->page->getHpId()], ['parts_type_code', HpSidePartsRepository::PARTS_FREEWORD]));
            DB::commit();
            $this->_redirect($actual_link);
        } else {
            throw $ex;
        }
    }

}
