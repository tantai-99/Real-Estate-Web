<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Http\Form\EstateSetting\SecondClassSearch;
use App\Traits\JsonResponse;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Estate\Setting;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SecondSearchTypeList;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\SecondEstateEnabledList;
use Library\Custom\Estate\Setting\Second;
use Library\Custom\Logger\CmsOperation;
use Exception;
use Library\Custom\Controller\Action\InitializedCompany;

class SecondEstateSearchSettingController extends InitializedCompany {
	use JsonResponse;
	protected $_controller = 'second-estate-search-setting';
	
    public function init($request, $next) {

        // if (!$this->isActionSearch()) {
        //     $this->_redirectSimple('index', 'index');
        // }
        return parent::init($request, $next);
    }

	public function index() {
		$user = getInstanceUser('cms');

		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}
		$this->view->topicPath('基本設定：2次広告自動公開設定');
		
		$hp = $user->getCurrentHp();
		// 検索条件
		$this->view->searchSettings = $hp->getSecondSearchSettingAll()->toAssocBy('estate_class');
		$this->view->searchClasses = ClassList::getInstance()->getAll();
		return view('second-estate-search-setting.index');
	}
	
	public function edit(Request $request) {
		// 加盟店の二次広告設定を取得
		$secondEstate = getUser()->getSecondEstate();
		// 有効期間内かチェック
		if (!$secondEstate || !$secondEstate->isAvailable()) {
			$this->_forward404();
		}
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->class])) {
			$this->_forward404();
		}	
		
		$class = $request->class;
        $this->view->estateClassNo = $class;
		$title = $classes[$class];
		$this->view->estateClassName = $title;

		$this->view->topicPath('基本設定：2次広告自動公開設定', 'index', $this->_controller);
		$this->view->topicPath($title.':基本設定');
		$this->view->form = new SecondClassSearch(['estateClass'=>$class, 'secondEstate'=>$secondEstate]);
		$hp = getUser()->getCurrentHp();
		// js編集用データ作成
		if ($searchSettingRow = $hp->getSecondSearchSetting($class)) {
			// 設定オブジェクトを作成
			$searchSetting = $searchSettingRow->toSettingObject();
		}
		else {
			// 設定のスケルトンオブジェクトを作成
			$searchSetting = new Setting\Second(['estate_class'=>$class]);
		}
		// csrfトークン付与
		$searchSetting->_token = getUser()->getCsrfToken();
		$this->view->setting = $searchSetting;
		
		// 都道府県のベース用に設定オブジェクト作成
		$this->view->baseSetting = $secondEstate->toSettingObject();
		
		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
		$this->view->searchTypeMaster = SecondSearchTypeList::getInstance()->getAll();
		$this->view->searchTypeConst  = SecondSearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getByClass($class);
		$this->view->secondEnabledMaster = SecondEstateEnabledList::getInstance()->getAll();
		return view('second-estate-search-setting.edit');
	}
	
	public function apiSave(Request $request)
    {
        // 加盟店の二次広告設定を取得
        $secondEstate = getUser()->getSecondEstate();
        // 有効期間内かチェック
        if (!$secondEstate || !$secondEstate->isAvailable()) {
            $this->_forward404();
        }

        $classes = ClassList::getInstance()->getAll();
        if (!isset($classes[$request->estate_class])) {
            $this->_forward404();
        }
        $class = $request->estate_class;

        // 全値取得
        $params = $request->all();

		// 検索設定オブジェクトを作成
		$secondSettingObject = new Second($params);
		// フォームの検証用に値を設定
		$params['search_type'] = $secondSettingObject->area_search_filter->search_type;
		$params['pref']        = $secondSettingObject->area_search_filter->area_1;
		$form = new SecondClassSearch(['estateClass'=>$class, 'secondEstate'=>$secondEstate]);
		// 2次広告自動公開しない場合は全ての必須チェックをはずす
		if (!$secondSettingObject->enabled) {
			foreach ($form as $name => $elem) {
				if ($name == 'enabled') {
					continue;
				}
				$elem->setRequired(false);
			}
		}
		// 検証
		$form->setData($params);

		if(!$params['enabled']){
			$form->enabled_estate_type->setRequired(false);
			$form->pref->setRequired(false);
		}
		
		if (!$form->isValid($params)) {
			throw new Exception('2次広告自動公開設定の検証に失敗しました');
		}

		$hp = getUser()->getCurrentHp();
		$isCreate=false;
        if($hp->getSecondSearchSetting($class)){
            $isCreate=true;
        }
	
		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();
		    $hp->saveSecondSearchSetting($secondSettingObject);
		    //CMS操作ログ
            $editType = ($isCreate) ? config('constants.log_edit_type.SECOND_SETTING_CREATE') :config('constants.log_edit_type.SECOND_SETTING_UPDATE');
            CmsOperation::getInstance()->cmsLogSecondEstate($editType, $class);
            DB::commit();
            return $this->success([]);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
	}
	
	public function detail(Request $request) {
		// 加盟店の二次広告設定を取得
		$secondEstate = getUser()->getSecondEstate();
		// 有効期間内かチェック
		if (!$secondEstate || !$secondEstate->isAvailable()) {
			$this->_forward404();
		}
		
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->class])) {
			$this->_forward404();
		}
		
		$class = $request->class ;
		$title = $classes[$class];
		$this->view->estateClassName = $title;
		
		$this->view->topicPath('基本設定：2次広告自動公開設定', 'index', $this->_controller);
		$this->view->topicPath($title.':設定確認');
		
		$hp = getUser()->getCurrentHp();
		$searchSettingRow = $hp->getSecondSearchSetting($class);
		if (!$searchSettingRow) {
			$this->_redirectSimple('index');
		}
		
		// js用データ
		$this->view->setting = $searchSettingRow->toSettingObject();
		
		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
		$this->view->searchTypeMaster = SecondSearchTypeList::getInstance()->getAll();
		$this->view->searchTypeConst  = SecondSearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getByClass($class);
		$this->view->secondEnabledMaster = SecondEstateEnabledList::getInstance()->getAll();
		return view('second-estate-search-setting.detail');
	}
}