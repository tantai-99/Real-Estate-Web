<?php
namespace App\Http\Controllers;
use App\Repositories\Hp\HpRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Library\Custom\Plan\ChangeCms;
use Library\Custom\Plan;
use Exception;
use Library\Custom\Model\Lists\CmsPlan;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use Library\Custom\Model\Lists\NewMark;
use App\Http\Form;
use App\Repositories\MTheme\MThemeRepositoryInterface;
use Illuminate\Http\Request;
use Library\Custom\Hp\Page;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use Illuminate\Routing\Redirector;
use App\Traits\JsonResponse;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Model\Lists\LogEditType;
use App\Repositories\HankyoPlusLog\HankyoPlusLogRepositoryInterface;

class InitializeController extends Controller {
	use JsonResponse;
	/**
	 * @var App\Models\Hp
	 */
	protected $_hp;
	
	public function init($request, $next) {
		$user = getUser();
		$hp = $user->getCurrentHp();
		$createdHp	= false				;
		// ホームページがない場合は作成する
		if (!$hp) {
			if ($user->isCreator() || $user->isAgency()) {
				if (getActionName() != 'copy') {
                    return $this->_redirectSimple('copy');
				}
				return $next($request);
			} else {
				DB::beginTransaction();
				$createdHp = $hp = $this->_initialize($user);
				DB::commit();
			}
		}
		else if ($hp->isInitialized()) { 
			if (getActionName() == 'copyComplete') {
				return $next($request);
			}

            return $this->_redirectSimple('index', 'index');
		}
		else {
            // $hp->repository = \App::make(HpRepositoryInterface::class);
            $hp->setTable('hp');
        }
		$this->_hp = $hp;
		
		if ($this->isApiRequest()) {
			return $next($request);
		}
		
		$initializeAction = 'index';
		switch ($hp->getInitializeStatus()) {
			case config('constants.hp.INITIAL_SETTING_STATUS_INIT'):
				$initializeAction = 'design';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_DESIGN'):
				$initializeAction = 'top-page';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE'):
				$initializeAction = 'company-profile';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_COMPANYPROFILE'):
				$initializeAction = 'privacy-policy';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_PRIVACYPOLICY'):
				$initializeAction = 'site-policy';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_SITEPOLICY'):
				$initializeAction = 'contact';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_CONTACT'):
				$initializeAction = 'complete';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE'):
				$initializeAction = 'complete';
				break;
			case config('constants.hp.INITIAL_SETTING_STATUS_NEW'):
			default:
				break;
		}
		
		$targetStatus = 0;
		switch (getActionName()) {
			case 'index':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_INIT');
				break;
			case 'design':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_DESIGN');
				break;
			case 'topPage':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE');
				break;
			case 'companyProfile':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_COMPANYPROFILE');
				break;
			case 'privacyPolicy':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_PRIVACYPOLICY');
				break;
			case 'sitePolicy':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_SITEPOLICY');
				break;
			case 'contact':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_CONTACT');
				break;
			case 'complete':
				$targetStatus = config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE');
				break;
		}
        list($initializeStatus, $initializeAction, $targetStatus) = $this->getInitializedList($hp->getInitializeStatus(), $initializeAction, $targetStatus);
		if (!($initializeStatus + 1 >= $targetStatus) && 
			!($initializeStatus == config('constants.hp.INITIAL_SETTING_STATUS_CONTACT') && $targetStatus == config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE'))
		) {
            return $this->_redirectSimple($initializeAction, 'initialize');
		}
		
		$this->view->layout = 'initialize';

		if ($createdHp) {
			(new ChangeCms())->setNowPlanByUser($user);
		}
		return $next($request);
	}
	
    private function setDefaultThemes()
    {
        $hp = getUser()->getCurrentHp();
        if ($hp && $hp->theme_id == null) {
            $table = \App::make(HpRepositoryInterface::class);
            DB::beginTransaction();
            try {
                $hpRow = $table->find($hp->id);
                $hpRow->theme_id = 1;
                $hpRow->color_id = 1;
                $hpRow->layout_id = 1;
                $hpRow->save();

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
        }
    }

    private function getInitializedList($status, $action, $target)
    {
        if (getInstanceUser('cms')->isNerfedTop()) {
			$action = getActionName();

            $this->setDefaultThemes();

            if ('design' == $action) {
				$target = config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE');
            } else if ('topPage' == $action) {
            	$action = 'top-page';
				if (config('constants.hp.INITIAL_SETTING_STATUS_DESIGN') > $status) {
                    $status = config('constants.hp.INITIAL_SETTING_STATUS_DESIGN');
                }
            }
            
            if (config('constants.hp.INITIAL_SETTING_STATUS_INIT') == $status) {
				$action = 'top-page';
            }
        }
        
        return array($status, $action, $target);
    }
    
	protected function _initialize( $user )
	{	
		$hp			= $user->createHp()					;		// HPを作成する
		$cmsPlan	= $user->getProfile()->cms_plan		;
		$plan		= Plan::factory( CmsPlan::getCmsPLanName($cmsPlan));

		$this->_createHpPageRecursive($hp, $plan->initialPages['main'], 0, 1);
		$this->_createHpPageRecursive($hp, $plan->initialPages['fix'], null, 1);

		\App::make(HpPageRepositoryInterface::class)->update(array(['hp_id', $hp->id]), array(['link_id', 'id']));

        $this->_initializeTop($hp);

		$hpImageTable = \App::make(HpImageRepositoryInterface::class);
		$hpImageTable->initSysImages($hp->id);
		
		return $hp;
	}
	
	protected function _createHpPageRecursive($hp, $pages, $parentId, $level) {
		$table = \App::make(HpPageRepositoryInterface::class);
		$sort = 0;
		foreach ($pages as $type => $children) {
            if ($type == config('constants.hp_page.TYPE_USEFUL_REAL_ESTATE_INFORMATION')) {
                $category = $table->getCategoryUsefulEstate($type);
            }else {
                $category = $table->getCategoryByType($type);
            }
            $data = array(
                'new_flg'			=> 1,
                'page_type_code'	=> $type,
                'page_category_code'=> $category,
                'title'				=> $table->getTypeNameJp($type),
                'description'		=> $table->getDescriptionNameJp($type),
                'keywords'			=> $table->getKeywordNameJp($type),
                'filename'			=> $table->getPageNameJp($type),
                'parent_page_id'	=> $parentId,
                'level'				=> $level,
                'sort'				=> $sort++,
                'hp_id'				=> $hp->id,
            );
            if ($type == config('constants.hp_page.TYPE_INFO_INDEX')) {
                $data += array(
                    'new_mark' => NewMark::COMMON
                );
            }
            $row = $table->create($data);
            $row->save();
            $row->link_id = $row->id;
            if ($type == config('constants.hp_page.TYPE_USEFUL_REAL_ESTATE_INFORMATION')) {
                $row->new_flg = 0;
                $row->diff_flg = 1;
                $template = json_decode(@file_get_contents(storage_path('models/Template/TemplateArticlePage.json')), true);
                if (isset($template[$type])) {
                    $hp->createTemplateArticlePage($row, $template);
                }
            }
            $row->save();

            if ($type == config('constants.hp_page.TYPE_FORM_CONTACT')) {
                $contactPartsTable = \App::make(HpContactPartsRepositoryInterface::class);
                $contactPartsTable->insertContactPartsWithDefault($type, $row->id, $hp->id);
            }
			
			$this->_createHpPageRecursive($hp, $children, $row->id, $level + 1);
		}
	}
	
	public function index() {
		
		$this->view->form = new Form\Site();
		
		if ($this->_hp->getInitializeStatus() >= config('constants.hp.INITIAL_SETTING_STATUS_INIT')) {
			$this->view->form->setData($this->_hp->toArray());
			$this->view->form->getSubForm('keywords')->setData(explode(",", $this->_hp->keywords));
		}
        return view('site-setting.index');
	}
	
	public function apiSaveIndex(Request $request) {
		$form = new Form\Site();
		$form->setData($request->all());
		if (!$form->isValid($request->all())) {
			return $this->success(['errors' => $form->getMessages()]);
		}
		
        $data = $request->all();
        $data['keywords'] = implode(',', $data['keywords']);
        // 画像IDが空の場合nullをセット
        foreach (array('logo_pc', 'logo_sp', 'footer_link_level') as $col) {
            if (isEmptyKey($data, $col)) {
                $data[$col] = null;
            }
        }

        $this->_hp->setFromArray($data);
		if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_INIT')) {
			$this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_INIT');
		}
		$this->_hp->all_upload_flg = 1;
		$this->_hp->setAllUploadParts('initial', 1); // 初期設定による全公開 = 1;

		DB::beginTransaction();
		try {
		    $this->_hp->save();
			
			// 反響プラスログを保存する
			if (getInstanceUser('cms')->getProfile()->cms_plan != config('constants.cms_plan.CMS_PLAN_LITE')) {
				$company = $this->_hp->fetchCompanyRow();
				$hankyoPlusLogTable = \App::make(HankyoPlusLogRepositoryInterface::class);
				$hankyoPlusLogTable->saveOperation(
					$data['hankyo_plus_use_flg'],
					$this->_hp->id,
					$company->id
				);
			}

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
		
		// CMS操作ログ
        CmsOperation::getInstance()->cmsLog(LogEditType::SITESETTING_UPDATE);
		
		// 次の遷移遷移先を返す
		$dataTo['redirectTo'] = '/initialize/design';
        return $this->success($dataTo);
	}

    public function design() {
        if (getInstanceUser('cms')->isNerfedTop()) {
            return redirect('/initialize/top-page');
        }
        $company_id =getInstanceUser('cms')->getProfile()->id;
        $this->view->form = new Form\Design($company_id);

        if ($this->_hp->getInitializeStatus() >= config('constants.hp.INITIAL_SETTING_STATUS_DESIGN')) {
            $this->view->form->setData($this->_hp->toArray());
        }
        return view('site-setting.design');
    }

	public function apiSaveDesign(Request $request) {
        $company_id = getUser()->getProfile()->id;
		$form = new Form\Design($company_id);
		
		//デザインパターン追加（カラー自由版）
		$theme_id = $request->get('theme_id');
		$model = \App::make(MThemeRepositoryInterface::class);
		// $select = $model->select();
		// $select->where('id = ?', $theme_id);
		$row = $model->find($theme_id);
        $this->_hp->setFromArray($request->all());
        $form->setData($this->_hp->toArray());
		if($row) {
			if(strpos($row->name, 'custom_color') === false) {
				$form->getElement('color_code')->setRequired(false);
			}else{
				$form->removeElement("color_id");
				$color_code = $request->get('color_code');
				$request->merge([
				    'color_code' => str_replace('#', '', $color_code),
				]);
			}
		}
		//デザインパターン追加（カラー自由版）

		if (!$form->isValid($request->all())) {
			return $this->success(['errors' => $form->getMessages()]);
		}
		
        $data = $request->all();

		//デザインパターン追加（カラー自由版）
		if(strpos($row->name, 'custom_color') === false) {
			$data['color_code'] = '';
		}else{
			$data['color_id'] = 0;
		}
		//デザインパターン追加（カラー自由版）
		
		$this->_hp->setFromArray($data);
		if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_DESIGN')) {
			$this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_DESIGN');
		}
		$this->_hp->all_upload_flg = 1;
		$this->_hp->setAllUploadParts('design', 1);  // デザイン変更による全公開
	
		DB::beginTransaction();
		try {
		    $this->_hp->save();
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		// CMS操作ログ
        CmsOperation::getInstance()->cmsLog(LogEditType::DESIGN_UPDATE);
		
		// 次の遷移遷移先を返す
        $dataTo['redirectTo'] = '/initialize/top-page';
        return $this->success($dataTo);
	}
	
	public function topPage() {
		$this->_editPage(config('constants.hp_page.TYPE_TOP'));
        return view('page.edit');
	}
	
	public function apiSaveTopPage(Request $request) {

		$table = \App::make(HpPageRepositoryInterface::class);
		
		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $ret = $this->_savePage($request, config('constants.hp_page.TYPE_TOP'));
		    if (!$ret) {
			    return $this->success(['errors' => $this->errors]);
		    }
		
		    if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE')) {
			    $this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE');
		    }
		    $this->_hp->save();

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		$dataTo['redirectTo'] = '/initialize/company-profile';
        return $this->success($dataTo);
	}
	
	public function companyProfile() {
		$this->_editPage(config('constants.hp_page.TYPE_COMPANY'));
        return view('page.edit');
	}
	
	public function apiSaveCompanyProfile(Request $request) {
	
		$table = \App::make(HpPageRepositoryInterface::class);
		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $ret = $this->_savePage($request, config('constants.hp_page.TYPE_COMPANY'));
		
		    if (!$ret) {
			    return $this->success(['errors' => $this->errors]);
		    }
		
		    if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_COMPANYPROFILE')) {
			    $this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_COMPANYPROFILE');
		    }
		    $this->_hp->save();

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		$dataTo['redirectTo'] = '/initialize/privacy-policy';
        return $this->success($dataTo);
	}
	
	public function privacyPolicy() {
		$this->_editPage(config('constants.hp_page.TYPE_PRIVACYPOLICY'));
        return view('page.edit');
	}
	
	public function apiSavePrivacyPolicy(Request $request) {
	
		$table = \App::make(HpPageRepositoryInterface::class);
		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $ret = $this->_savePage($request, config('constants.hp_page.TYPE_PRIVACYPOLICY'));
		
		    if (!$ret) {
			    return $this->success(['errors' => $this->errors]);
		    }
		
		    if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_PRIVACYPOLICY')) {
			    $this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_PRIVACYPOLICY');
		    }
		    $this->_hp->save();
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		$dataTo['redirectTo'] = '/initialize/site-policy';
        return $this->success($dataTo);
	}
	
	public function sitePolicy() {
		$this->_editPage(config('constants.hp_page.TYPE_SITEPOLICY'));
        return view('page.edit');
	}
	
	public function apiSaveSitePolicy(Request $request) {
	
		$table = \App::make(HpPageRepositoryInterface::class);
		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $ret = $this->_savePage($request, config('constants.hp_page.TYPE_SITEPOLICY'));
		
		    if (!$ret) {
			    return $this->success(['errors' => $this->errors]);
		    }
		
		    if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_SITEPOLICY')) {
			    $this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_SITEPOLICY');
		    }
		    $this->_hp->save();
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		$dataTo['redirectTo'] = '/initialize/contact';
        return $this->success($dataTo);
	}
	
	public function contact() {
		$this->_editPage(config('constants.hp_page.TYPE_FORM_CONTACT'));
        return view('page.edit');
	}
	
	public function apiSaveContact(Request $request) {
	
		$table = \App::make(HpPageRepositoryInterface::class);
		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $ret = $this->_savePage($request, config('constants.hp_page.TYPE_FORM_CONTACT'));
		
		    if (!$ret) {
			    return $this->success(['errors' => $this->errors]);
		    }
		
		    if ($this->_hp->initial_setting_status_code < config('constants.hp.INITIAL_SETTING_STATUS_CONTACT')) {
			    $this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_CONTACT');
		    }
		    $this->_hp->save();
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		$dataTo['redirectTo'] = '/initialize/complete';
        return $this->success($dataTo);
	}

	public function complete() {
		$this->_hp->initial_setting_status_code = config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE');
	
		$table = \App::make(HpPageRepositoryInterface::class);

		DB::beginTransaction();
		try {
			// $adapter->beginTransaction();
		
		    $this->_hp->save();
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
        return view('initialize.complete');
	}
	
	protected function _editPage($type) {
		$row = $this->_getHpPage($type);
		
		$page = Page::factory($this->_hp, $row);
        $serviceTop = getInstanceUser('cms')->checkHasTopOriginal();
        $topPage  = get_class($page) == "Library\Custom\Hp\Page\Top";
        $contactPage = get_class($page) == 'Library\Custom\Hp\Page\FormContact';
        if($serviceTop && $topPage || $contactPage){
            $page->forceLoad();
        }
        $isNerfedTop = getInstanceUser('cms')->isNerfedTop();
        $companyPage = get_class($page) == "Library\Custom\Hp\Page\Company";
        $this->view->disableTitle = false;
        if ($isNerfedTop && $companyPage) {
            $this->view->disableTitle = true;
        }
        $page->init();
        $page->load();
        
        $this->view->page = $page;
        $this->view->createableMainParts = $page->getCreateableMainParts();
        $this->view->createableSideParts = $page->getCreateableSideParts();
        $this->view->displayFreeword = $this->checkDisplayFreeword($this->_hp->id);
        $this->view->isTopOriginal = $serviceTop;
        $this->view->isTopPage = false;
        // $this->view->isSeo = false;
        $setting = $this->_hp->getEstateSetting();
        $this->view->hasSearchSetting = 0;
        if ($setting) {
            $this->view->hasSearchSetting = 1;
        }
        // $this->_helper->viewRenderer('page/edit', null, true);
	}
	
	protected function _savePage($request, $type) {
		$row = $this->_getHpPage($type);
		
		$page = Page::factory($this->_hp, $row);
		$page->init();
		if (!$page->isValid($request->all())) {
			$this->errors = $page->getMessagesById();
			return false;
		}
		
		$page->save();
		
		return true;
	}
	
	protected function _getHpPage($type) {
		$row = \App::make(HpPageRepositoryInterface::class)->fetchRow(array(['hp_id', $this->_hp->id], ['page_type_code', $type]));
		if (!$row) {
			throw new Exception('hp page not found');
		}
		return $row;
	}
	
	public function copy(Request $request) {
		if (!$request->isMethod('post')) {
			return view('initialize.copy');
		}

		$user = getUser();
		$profile = getUser()->getProfile();
		$table   = App::make(HpPageRepositoryInterface::class);
		DB::beginTransaction();
		try {
		    if (!$hp = $profile->getCurrentHp()) {
		    	$hp = $this->_initialize($user);
			}
			
			$isTopOriginal = $profile->checkTopOriginal();
			$newHp = $hp->copyAllForCompanyToCreator($isTopOriginal);
		    \App::make(AssociatedCompanyHpRepositoryInterface::class)->updateCreatorHp($profile->id, $newHp->id);
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
        CmsOperation::getInstance()->creatorLog(config('constants.log_edit_type.CREATOR_DATA_COPY'));

		// 親画面をリダイレクトする
		echo '<script type="text/javascript">parent.location.href="/initialize/copy-complete";</script>';
		// return $this->_redirectSimple('copy-complete', 'initialize');
		exit;
	}

	public function copyComplete()
	{
		return view('initialize.copy-complete');
	}

    /**
     * @param $hp
     * @throws Exception
     */
	protected function _initializeTop($hp){
        /**
         * @var $hp App\Models\Hp
         * @var $company App\Models\Company
         */
        $company = $hp->fetchCompanyRow();
        $isTopOriginal = $company->checkTopOriginal();
        if($isTopOriginal){
            $company->topOriginalEvent( $hp , $isTopOriginal);
        }
    }
}