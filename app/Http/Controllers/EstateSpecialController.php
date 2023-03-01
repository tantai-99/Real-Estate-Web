<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use Library\Custom\Estate\Setting\Special;
use Library\Custom\Estate\Setting\SearchFilter\Special as SearchFilterSpecial;
use App\Http\Form\EstateSetting\Special as FormSpecial;
use App\Http\Form\EstateSetting\SpecialMethod;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SearchTypeList;
use Library\Custom\Model\Estate\SearchTypeCondition;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\SpecialPublishEstateList;
use Library\Custom\Model\Estate\SpecialTesuryoKokokuhiList;
use Library\Custom\Model\Estate\SpecialSearchPageTypeList;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Logger\CmsOperation;
use Exception;
use App\Traits\JsonResponse;
use Library\Custom\Model\Lists\Original;
use Library\Custom\DirectoryIterator;
use Library\Custom\Model\Lists\LogEditType;
use Library\Custom\Controller\Action\InitializedCompany;

class EstateSpecialController extends InitializedCompany {
	use JsonResponse;

	protected $_controller = 'estate-special';
	
	private $shumoku_sort = [
		// 賃貸(アパート・マンション・一戸建て)
		 '1' => [17,18,19,24,25],
		// 貸ビル・貸倉庫・その他
		 '6' => [20,21,61,22,23,26,27,28,29,30,31,32,33,34,35,36,37,38],
		// 一戸建て（新築・中古）
		 '8' => [39,40],	
		// 売ビル・売倉庫・売工場・その他
		'12' => [13,14,15,47,48,49,50,51,52,53,54,55,56,57,58,59,60,16,'<br style="clear:both;"/>',41,42,43,44,45,46]
	];

    public function init($request,$next) {

        if (!$this->isActionSearch()) {
            return Redirect::to('/');
        }
		return parent::init($request,$next);
    }

	public function index(Request $request) 
	{
		$this->view->topicPath('基本設定：特集の作成/更新');
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		$this->view->setting = $setting;
		
		if ($setting) {
			// ベースとなる物件設定
			$this->view->searchSettings = $setting->getSearchSettingAll();
			$this->view->specials = $setting->getSpecialAllWithPubStatus($request->order);
		}
		
		// 編集可能かどうか
		$this->view->canEdit = ($setting && $this->view->searchSettings->count());
		
		// 並び替えオプション
		$this->view->sortOptions = App::make(SpecialEstateRepositoryInterface::class)->getSortOptions();

		// 地図検索オプション
        $this->view->mapOption   = getUser()->getMapOption();
		return view('estate-special.index');
	}
	
	public function new (Request $request) {
		if (!$this->edit($request)) {
			return Redirect::to('/estate-special');
		}
		return view('estate-special.edit');
	}

	public function edit($request) {
		$this->view->topicPath('基本設定：特集の作成/更新', '',$this->_controller);
		$this->view->topicPath('基本設定');
		
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		
		if (!$setting) {
			return false;
		}
		
		// ベースとなる物件検索設定を全て取得
		$searchSettings = $setting->getSearchSettingAll();
		
		// 編集可能かどうか
		if (!$setting || !$searchSettings->count()) {
			return false;
		}
		
		
		// idが指定された場合は更新処理
		if (is_numeric($request->id)) {
			$special = $setting->getSpecialWithPubStatus((int)$request->id);
			if (!$special) {
				return $this->_forward404();
			}
			
			$this->view->special = $special;
			// 特集設定オブジェクトを作成
			$this->view->specialSetting = $special->toSettingObject();
			$this->view->specialSetting->id = $special->id;
            $specialResetSetting = new Special();
            $specialResetSetting->id = $special->id;
            $specialResetSetting->origin_id = $special->origin_id;
            $specialResetSetting->second_estate_enabled = $special->second_estate_enabled;
            $this->view->specialResetSetting = $specialResetSetting;
            $this->view->resetSetting = 1;
		}
		else {
			// 特集設定のスケルトンオブジェクトを作成
			$specialSetting = new Special();
			$this->view->specialSetting = $specialSetting;
            $this->view->specialResetSetting = $specialSetting;
		}
		
		// csrfトークン付与
		$this->view->specialSetting->_token = getUser()->getCsrfToken();
        $this->view->specialResetSetting->_token = getUser()->getCsrfToken();

        // 地図検索オプション
        $this->view->specialSetting->mapOption   =getUser()->getMapOption();
        $this->view->specialResetSetting->mapOption   = getUser()->getMapOption();

        // 特集の基本設定フォーム作成
		$this->view->form = new FormSpecial([
			'hpId' => $hp->id,
			'settingId' => $setting->id,
			'searchSettings' => $searchSettings]);

        $this->view->formMethod = new SpecialMethod([
            'hpId' => $hp->id,
            'settingId' => $setting->id,
            'searchSettings' => $searchSettings]);
		
		// 物件種別毎にベースとなる物件検索設定マップを作成
		$baseSettings = [];
		foreach ($searchSettings as $searchSettingRow) {
			$searchSetting = $searchSettingRow->toSettingObject();
            $baseSettings[ $searchSetting->estate_class ] = $searchSetting;
		}
		$this->view->baseSettings = $baseSettings;

		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
        $this->view->searchTypeMaster = SearchTypeList::getInstance()->getAll();
        $this->view->searchTypeConditionMaster =SearchTypeCondition::getInstance()->getAll();
		$this->view->searchTypeDirectMaster = SearchTypeList::getInstance()->getAllForSpecialDirect();
		$this->view->searchTypeConst  = SearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getAll();
		$this->view->specialPublishEstateMaster = SpecialPublishEstateList::getInstance()->getAll();
		$this->view->specialTesuryoKokokuhiMaster =SpecialTesuryoKokokuhiList::getInstance()->getAll();
		$this->view->specialSearchPageTypeMaster = SpecialSearchPageTypeList::getInstance()->getAll();

		// 種目詳細
		// 現在選択中の種目を取得
        if(!empty($special->search_filter)) {
            $settingTmp = json_decode($special->search_filter);
        }
		$selShumoku = [];
		if(isset($settingTmp->categories) && isset($settingTmp->categories[0]) && $settingTmp->categories[0]->category_id == 'shumoku') {
			foreach($settingTmp->categories[0]->items as $val) {
				$selShumoku[] = $val->item_id;
			}
		}
		
		$shumokuTypeMaster = [];
		for($eno = 1; $eno < 13; $eno++) {
			if(!isset($this->shumoku_sort[ $eno ])) {
				continue;
			}

        	$searchFilter = new SearchFilterSpecial();
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
			foreach($this->shumoku_sort[ $eno ] as $item_id) {
				if(isset($sType[ $item_id ])) {
					$shumokuTypeMaster[ $eno ][] = $sType[ $item_id ];
				} else if(gettype($item_id) == 'string') {
					$shumokuTypeMaster[ $eno ][] = $item_id;
				}
			}
			$searchFilter = null;
		}
		$this->view->shumokuTypeMaster = $shumokuTypeMaster;
		return true;
	}
	
	public function detail(Request $request) {

		$this->view->topicPath('基本設定：特集の作成/更新','',$this->_controller);
		$this->view->topicPath('設定確認');
		
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		
		if (!$setting) {
			return redirect()->back();
		}
		
		// 全公開フラグ確認
		$this->view->all_upload_flg = 0;
		if(!empty($hp) && isset($hp->all_upload_flg)) {
			$this->view->all_upload_flg = $hp->all_upload_flg;
		}

		// ベースとなる物件設定
		$searchSettings = $setting->getSearchSettingAll();
		// 編集可能かどうか
		$this->view->canEdit = $searchSettings->count();
		$special = $setting->getSpecialWithPubStatus((int)$request->id);
		if (!$special) {
			return $this->_forward404();
		}
		
		$this->view->special = $special;
		$this->view->specialSetting = $special->toSettingObject();
        $this->view->specialSetting->mapOption = getUser()->getMapOption();

		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
		$this->view->searchTypeMaster = SearchTypeList::getInstance()->getAll();
		$this->view->searchTypeDirectMaster = SearchTypeList::getInstance()->getAllForSpecialDirect();
		$this->view->searchTypeConst  = SearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getAll();
		$this->view->specialPublishEstateMaster = SpecialPublishEstateList::getInstance()->getAll();
		$this->view->specialTesuryoKokokuhiMaster = SpecialTesuryoKokokuhiList::getInstance()->getAll();
		$this->view->specialSearchPageTypeMaster = SpecialSearchPageTypeList::getInstance()->getAll();


		$shumokuTypeMaster = [];
		for($eno = 1; $eno < 13; $eno++) {
			if(!isset($this->shumoku_sort[ $eno ])) {
				continue;
			}

        	$searchFilter = new SearchFilterSpecial();
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
				$sType[ $item->item_id ] = [ 'item_id' => $item->item_id,  'label' => $item->label ];
			}
			foreach($this->shumoku_sort[ $eno ] as $item_id) {
				if(isset($sType[ $item_id ])) {
					$shumokuTypeMaster[ $eno ][] = $sType[ $item_id ];
				}
			}
			$searchFilter = null;
		}
		$this->view->shumokuTypeMaster = $shumokuTypeMaster;
		return view('estate-special.detail');
	}
	
	/**
	 * 特集基本設定の値意を検証する
	 */
	public function apiValidateBasic(Request $request) {
		$data=[];
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		$data['cms_plan'] =getUser()->getProfile()->cms_plan;
		if (!$setting) {
			return $this->_forward404();
		}
		// idが指定された場合は更新処理
		$special = null;
		if (is_numeric($request->id)) {
			$special = $setting->getSpecial((int)$request->id);
			if (!$special) {
				return $this->_forward404();
			}
		}
		// ベースとなる物件検索設定を全て取得
		$searchSettings = $setting->getSearchSettingAll();
		
		// 特集の基本設定フォーム作成
		$form = new FormSpecial([
			'hpId' => $hp->id,
			'settingId' => $setting->id,
			'searchSettings' => $searchSettings,
			'specialId' => $special ? $special->id : null,
			'searchPage' => $request->has_search_page,
			'searchType' => $request->search_type
		]);
		
		// 全値取得
		$params = $request->all();
		
		// 検証時はsp-接頭辞無しで渡されるので補完
		if (isset($params['filename'])) {
			$params['filename'] = 'sp-'.$params['filename'];
		}
		// 物件種別があれば物件種目のオプションを更新
        if (ClassList::getInstance()->get($params['estate_class'])) {
		    $options = $form->getElement('enabled_estate_type')->getValueOptions();
            $typesOfClass = TypeList::getInstance()->getByClass($params['estate_class']);
            $newOptions = [];
            foreach ($typesOfClass as $type => $typeName) {
                if (isset($options[$type])) {
                    $newOptions[$type] = $typeName;
                }
            }
            $form->getElement('enabled_estate_type')->setValueOptions($newOptions);
        }
		$form->setData($params);
		// 検証
		if (!$form->isValid($params)) {
			$data['errors'] = $form->getMessages();
		}
        // if(!isset($params['publish_estate']) || count($params['publish_estate']) == 0) {
        //     $this->data->errors = [ 'publish_estate' => [ 'msg' => '公開する物件の種類を選択してください'] ];
        // }
		if (isset($data['errors']['filename'][0])) {
			$data['errors']['filename'][0] = str_replace('20', '17', $data['errors']['filename'][0]);
		}
		return $this->success($data);
	}
	
	/**
	 * 特集設定を保存する
	 */
	public function apiSave(Request $request) {

		$data=[];
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (!$setting) {
			return $this->_forward404();
		}

        $isCreate=false;

		// idが指定された場合は更新処理
		$special = null;
		if (is_numeric($request->id)) {
			$special = $setting->getSpecial((int)$request->id);
			if (!$special) {
				return $this->_forward404();
			}
            // $isCreate=true;
		} else {
            $isCreate=true;
		}


		// ベースとなる物件検索設定を全て取得
		$searchSettings = $setting->getSearchSettingAll();
		
		// 特集の基本設定フォーム作成
		$form = new FormSpecial([
			'hpId' => $hp->id,
			'settingId' => $setting->id,
			'searchSettings' => $searchSettings,
			'specialId' => $special ? $special->id : null]);

        // 全値取得
        $params = $request->all();
		// 検索設定オブジェクトを作成
        $specialSettingObject = new Special($params);
		// フォームの検証用に値を設定
		
		if ($specialSettingObject->jisha_bukken) {
			$params['publish_estate'][] = 'jisha_bukken';
		}
		if ($specialSettingObject->niji_kokoku) {
			$params['publish_estate'][] = 'niji_kokoku';
		}
		if ($specialSettingObject->niji_kokoku_jido_kokai) {
			$params['publish_estate'][] = 'niji_kokoku_jido_kokai';
		}

		$params['has_search_page'] = $specialSettingObject->area_search_filter->has_search_page;
		$params['search_type']     = $specialSettingObject->area_search_filter->search_type;
		$params['pref']            = $specialSettingObject->area_search_filter->area_1;

        if ($specialSettingObject->method_setting == 2 && count($specialSettingObject->houses_id) > 0) {
            $settingRow = App::make(EstateClassSearchRepositoryInterface::class)->getSetting($hp->id, $setting->id, $specialSettingObject->estate_class);
            $result = [];
            foreach ($specialSettingObject->houses_id as $houseId) {
                $houseIds = explode(':', $houseId);
                if (in_array($houseIds[1], $settingRow->toSettingObject()->area_search_filter->area_1)) {
                    $result[] = $houseId;
                }
            }
            $specialSettingObject->houses_id = $result;
        }

		// 検索ページ有りの場合は検索タイプ必須
		if ($specialSettingObject->area_search_filter->has_search_page == "1" && $specialSettingObject->method_setting != 3) {
			$form->getElement('search_type')->setRequired(true);
        }
		$form->setData($params);
		// 検証
		if (!$form->isValid($params)) {
			throw new Exception('特集ページの検証に失敗しました');
		}

		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();
			// リンク名を保存
			$table->saveLinkName($specialSettingObject,$special,$hp->id);
			$special_id = $setting->saveSpecial($specialSettingObject, $special);
			// cmsデータ更新;
			$setting->cmsUpdated();

            $editType = ($isCreate) ? config('constants.log_edit_type.SPECIAL_SETTING_CREATE') : config('constants.log_edit_type.SPECIAL_SETTING_UPDATE');
            CmsOperation::getInstance()->cmsLogSpecial($editType, $specialSettingObject->filename,$specialSettingObject->title);

			DB::commit();
            $this->updateOriginalKomaHtmlAction(config('constants.log_edit_type.SPECIAL_SETTING_CREATE'), $special_id);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		return $this->success($data);
	}
	
	/**
	 * 特集をコピーする
	 */
	public function apiCopy(Request $request) {
		$data=array();
        $hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (!$setting) {
			return $this->_forward404();
		}
		
		$special = $setting->getSpecial((int)$request->id);
		if (!$special) {
			return $this->_forward404();
		}
		try {
			DB::beginTransaction();
		
			$row = $special->copySpecial();
            $filename = $row['filename'];
            if ($filename) {
                // cmsデータ更新
                $setting->cmsUpdated();	
                $data['error'] = false;		
                $data['id'] = $row->id;

                $title = $row['title'];
                $editType = config('constants.log_edit_type.SPECIAL_SETTING_COPY');
                CmsOperation::getInstance()->cmsLogSpecial($editType, $filename, $title);

                DB::commit();
                $this->updateOriginalKomaHtmlAction(config('constants.log_edit_type.SPECIAL_SETTING_COPY'), $row->id);
            }else {
                $data['error'] = true;	
                $data['message'] = 'ページの作成に失敗しました。';
                DB::rollback();
            }
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		return $this->success($data);
	}
	
	/**
	 * 削除
	 */
	public function apiDelete(Request $request) 
	{
		$data=[];
		$hp = getUser()->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (!$setting) {
			return $this->_forward404();
		}
		$data['cms_plan']=getUser()->getProfile()->cms_plan;
		$id = (int)$request->id;
		$special = $setting->getSpecialWithPubStatus($id);
		if (!$special) {
			return $this->_forward404();
		}
		if(!$special->canDelete()) {
			throw new Exception('特集が公開中または物件コマで使用されている為削除できません。');
		}
		if($special->isScheduled()) {
			throw new Exception('特集が公開予約中の為削除できません。');
		}

		try {
			DB::beginTransaction();
		
			App::make(SpecialEstateRepositoryInterface::class)->delete($id, true);
			// delete link
			$where = [
					['page_type_code',config('constants.hp_page.TYPE_ESTATE_ALIAS')],
					['link_estate_page_id', "estate_special_{$special->origin_id}"],
					['hp_id', $hp->id]
					];
			App::make(HpPageRepositoryInterface::class)->delete($where, true);

			$where = [
					['link_page_id',"estate_special_{$special->origin_id}"],
					['hp_id',$hp->id]
			];
			App::make(HpInfoDetailLinkRepositoryInterface::class)->delete($where, true);

			// cmsデータ更新
			$setting->cmsUpdated();

            $editType = config('constants.log_edit_type.SPECIAL_SETTING_DELETE');
            CmsOperation::getInstance()->cmsLogSpecial($editType, $special->getFilename(),$special->getTitle());

			DB::commit();
            $this->updateOriginalKomaHtmlAction(config('constants.log_edit_type.SPECIAL_SETTING_DELETE'), $special->id);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		return $this->success($data);
	}
  
    /**
    * sync koma html file
    * 
    * @param Library\Custom\Model\Lists\Original $setttingType
    * @param int $special_id
    * return void
    */
    public function updateOriginalKomaHtmlAction($setttingType, $special_id)
    {
        $hp = getUser()->getCurrentHp();
        $company = $hp->fetchCompanyRow();
        
        if (null == $company || !$company->id) {
            throw new Exception("No Company Data.");
            return;
        }
        
        $koma_path = Original::getOriginalImportPath($company->id, config('constants.original.ORIGINAL_IMPORT_TOPKOMA'));

        $file_pc = 'special'.$special_id.'_pc.html';
        $file_sp = 'special'.$special_id.'_sp.html';
        
        $di = new DirectoryIterator();
        $di->initialImportHtmlDir($company->id);
        
        switch ($setttingType) {
            case config('constants.log_edit_type.SPECIAL_SETTING_DELETE'):
                $di = new DirectoryIterator();
                $di->load($koma_path);
                $di->removeFile($file_pc);
                $di->removeFile($file_sp);
                break;                
            case config('constants.log_edit_type.SPECIAL_SETTING_CREATE'):
            case config('constants.log_edit_type.SPECIAL_SETTING_COPY'):
                $override = true;
                $defaultContent = "『このテンプレートファイルを利用して、特集コマのデザインを設定してください』";
                
                // $di->load($root_path);
                // $default_pc = $di->get('special0_pc.html');
                // $default_sp = $di->get('special0_sp.html');
                // if (null === $default_pc || null === $default_sp) {
                    // throw new Exception("Default special file is not define.");
                    // return;
                // }
                
                $di->load($koma_path);
                if (null == $di->get($file_pc)) $di->makeFile($file_pc, $defaultContent, $override);
                if (null == $di->get($file_sp)) $di->makeFile($file_sp, $defaultContent, $override);
                break;                
            default:
                break;
        }
    }

    public function apiValidateMethod(Request $request) {
		$data=[];
        $hp = getUser()->getCurrentHp();
        $setting = $hp->getEstateSetting();

        if (!$setting) {
            return $this->_forward404();
        }

        $special = null;
        if (is_numeric($request->id)) {
            $special = $setting->getSpecial((int)$request->id);
            if (!$special) {
                $this->_forward404();
            }
        }

        $searchSettings = $setting->getSearchSettingAll();

        $form = new SpecialMethod([
            'hpId' => $hp->id,
            'settingId' => $setting->id,
            'searchSettings' => $searchSettings,
            'specialId' => $special ? $special->id : null,
            'method' => $request->method,
            'searchType' => $request->search_page,
        ]);

        $params = $request->all();

        if (isset($params['search_type'])) {
            $params['search_type_method'] = $params['search_type'];
        }

		$form->setData($params);
        if (!$form->isValid($params)) {
            $data['errors'] = $form->getMessages();
        }

        if(!isset($params['publish_estate']) || count($params['publish_estate']) == 0) {
            $data['errors'] = [ 'publish_estate' => [ 'msg' => '公開する物件の種類を選択してください'] ];
        }
		return $this->success($data);
    }
}