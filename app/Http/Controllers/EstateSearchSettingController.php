<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Model\Estate\FdpType;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SearchTypeList;
use Library\Custom\Model\Estate\TypeList;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Http\Form\EstateSetting\ClassSearch;
use Library\Custom\Estate\Setting\Basic;
use Illuminate\Support\Facades\DB;
use Exception;
use Library\Custom\Model\Lists\LogEditType;
use Library\Custom\Logger\CmsOperation;
use App\Traits\JsonResponse;
use Library\Custom\Controller\Action\InitializedCompany;

class EstateSearchSettingController extends InitializedCompany
{
	use JsonResponse;

	protected $_controller = 'estate-search-setting';

	public function init($request, $next)
	{
		if (!$this->isActionSearch()) {
			return redirect()->route('default.index.index');
		}
		
		return parent::init($request, $next);
	}

	public function index()
	{
		$this->view->topicPath('基本設定：物件検索設定');
		// $this->view->messages = $this->_helper->flashMessenger->getMessages();

		$hp = getInstanceUser('cms')->getCurrentHp();
		$setting = $hp->getEstateSetting();

		// 検索条件
		if ($setting) {
			$this->view->searchSettings = $setting->getSearchSettingAll()->toAssocBy('estate_class');
		}
		$this->view->searchClasses = ClassList::getInstance()->getAll();
		$this->view->mapOption = getInstanceUser('cms')->getMapOption();

		return view('estate-search-setting.index');
	}

	public function edit(Request $request)
	{
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->class])) {
			return $this->_forward404();
		}

		$class = $request->class;
		$title = $classes[$class];
		$this->view->estateClassName = $title;

		$this->view->topicPath('基本設定：物件検索設定', '');
		$this->view->topicPath($title . ':基本設定');

		$plan	= getInstanceUser('cms')->getProfile()->cms_plan;
		$isFDP = FdpType::getInstance()->isFDP(getInstanceUser('cms')->getProfile());
		$this->view->form = new ClassSearch(['estateClass' => $class, 'plan' => $plan, 'fdp' => $isFDP]);
		$this->view->dispEstateRequest = ($plan >= config('constants.cms_plan.CMS_PLAN_ADVANCE') ? 1 : 0);
		$this->view->pubEstateTypes = [];

		$hp = getInstanceUser('cms')->getCurrentHp();
		$setting = $hp->getEstateSetting();
		// js編集用データ作成
		if ($setting && $searchSettingRow = $setting->getSearchSetting($class)) {
			$searchSetting = $searchSettingRow->toSettingObject();

			// 公開予約で利用している物件種目
			$linkIds = $searchSettingRow->getLinkIdList();
			$pubLinkingSchedules = App::make(ReleaseScheduleRepositoryInterface::class)->fetchReserveRowsByHpId($hp->id);
			$pubEstateTypes = [];
			foreach ($pubLinkingSchedules as $pubLinkingSchedule) {
				$row = App::make(HpPageRepositoryInterface::class)->fetchRow(array(['id', $pubLinkingSchedule->page_id]));
				if ($row && $row->link_estate_page_id) {
					$pubEstateTypes[] = $row->link_estate_page_id;
				}
			}
			$pubEstateTypes = array_intersect($pubEstateTypes, array_keys($linkIds));
			$this->view->pubEstateTypes = str_replace('estate_type_', '', $pubEstateTypes);
		} else {
			$searchSetting = new Basic(['estate_class' => $class]);
			// 4489: Change UI setting FDP
			$searchSetting->display_fdp = FdpType::getInstance()->getDefaultSettingFDP();
		}
		$this->view->town = FdpType::getInstance()->getTown();
		$this->view->fdp_type = FdpType::getInstance()->getFdp();
		$this->view->townLabel = FdpType::getInstance()->getTownLabel();
		$searchSetting->typeSpatial	= SearchTypeList::TYPE_SPATIAL;
		$searchSetting->mapOption	= getInstanceUser('cms')->getMapOption();
		$searchSetting->_token = getInstanceUser('cms')->getCsrfToken();
		if (!is_array($searchSetting->display_fdp)) {
			$searchSetting->display_fdp = json_decode($searchSetting->display_fdp);
		}
		// 4489: Change UI setting FDP
		if (!$isFDP) {
			$searchSetting->display_fdp = FdpType::getInstance()->getDefaultSettingNotFDP();
		}
		$this->view->fdp = $isFDP;
		$this->view->setting = $searchSetting;

		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
		$this->view->searchTypeMaster = SearchTypeList::getInstance()->getAll();
		$this->view->searchTypeConst  = SearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getByClass($class);

		// 特集で利用されている種目リスト
		$estateTypes = [];

		// ATHOME_HP_DEV-5165 :hp_estate_setting_id が未設定の場合は特集を検索しない
		if (!empty($setting->id)) {
			$spTable = App::make(SpecialEstateRepositoryInterface::class);
			$spWhere = [
				['hp_id', $hp->id],
				['hp_estate_setting_id', $setting->id],
				['estate_class', $class]
			];
			$rows = $spTable->distinctRows($spWhere, ['enabled_estate_type']);
			foreach ($rows as $row) {
				$typeList = explode(",", $row['enabled_estate_type']);
				$estateTypes = array_merge($estateTypes, $typeList);
			}
		}

		$estateTypes = array_unique($estateTypes);
		$this->view->spEstateTypes = array_values($estateTypes);

		return view('estate-search-setting.edit');
	}

	public function apiSave(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->estate_class])) {
			return $this->_forward404();
		}
		$class = $request->estate_class;

		$hp = getInstanceUser('cms')->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (isset($setting)) {
			$searchSettingRow = $setting->getSearchSetting($class);
		}
		$isCreate = false;

		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();
			if (!$setting) {
				$setting = $hp->createEstateSetting();
				$isCreate = true;
			}
			$setting->saveSearchSetting(new Basic($request->all()));

			// cmsデータ更新
			$setting->cmsLastUpdated();

			if (isset($searchSettingRow)) {
				$linkIds = $searchSettingRow->getLinkIdList();
				$searchSettingAfterUpdateRow = $setting->getSearchSetting($class);
				$afterUpdateLinkIds = $searchSettingAfterUpdateRow->getLinkIdList();
				$settingEstateClassList = $table->getSettingEstateClassList($setting);
				$innerLinkPages = $table->getInnerLinkPages($hp, array_diff(array_keys($linkIds), array_keys($afterUpdateLinkIds)), $settingEstateClassList);
				// ページ作成・更新上のリンクを削除
				$table->deleteLinkPages($innerLinkPages);
			}

			// 特集で利用されている種目がすべて今回設定する種目に含まれていることを確認する
			$spEstateTypes = [];
			$spTable = App::make(SpecialEstateRepositoryInterface::class);
			$spWhere = [
				['hp_id', $hp->id],
				['hp_estate_setting_id', $setting->id],
				['estate_class', $class]
			];
			$rows = $spTable->distinctRows($spWhere, ['enabled_estate_type']);
			foreach ($rows as $row) {
				$typeList = explode(",", $row['enabled_estate_type']);
				$spEstateTypes = array_merge($spEstateTypes, $typeList);
			}
			$spEstateTypes = array_unique($spEstateTypes);

			if (count($spEstateTypes)) {
				foreach ($spEstateTypes as $type) {
					if (!in_array($type, $request->enabled_estate_type)) {
						throw new Exception("特集で利用されている種目が設定されています");
					}
				}
			}

			// 問い合わせページ取得
			$table = App::make(HpPageRepositoryInterface::class);
			$row = $table->fetchEstateFormByEstateClass($hp->id, $class);
			if (!$row) {
				// 無い場合は作成する
				$table = App::make(HpPageRepositoryInterface::class);
				$row = $table->createEstateFormByEstateClass($hp->id, $class);
				if ($row->page_type_code !== null) {
					$contactPartsTable = App::make(HpContactPartsRepositoryInterface::class);
					$contactPartsTable->insertContactPartsWithDefault($row->page_type_code, $row->id, $hp->id);
				}
			}

			$estateFormId = $row->id;

			//CMS操作ログ
			$editType = ($isCreate) ? LogEditType::ESTATE_SETTING_CREATE : LogEditType::ESTATE_SETTING_UPDATE;
			CmsOperation::getInstance()->cmsLogEstate($editType, $class);


			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data['estateFormId'] = $estateFormId;
		$data['cms_plan'] = $cms_plan;

		return $this->success($data);
	}

	public function detail(Request $request)
	{
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->class])) {
			return $this->_forward404();
		}

		$class = $request->class;
		$title = $classes[$class];
		$this->view->estateClassName = $title;

		$this->view->topicPath('基本設定：物件検索設定', '');
		$this->view->topicPath($title . ':設定確認');

		$hp = getInstanceUser('cms')->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (!$setting) {
			return redirect()->route('default.estate-search-setting.index');
		}
		$searchSettingRow = $setting->getSearchSetting($class);
		if (!$searchSettingRow) {
			return redirect()->route('default.estate-search-setting.index');
		}
		// js用データ
		if (!is_array($searchSettingRow->display_fdp)) {
			$searchSettingRow->display_fdp = json_decode($searchSettingRow->display_fdp);
		}
		$this->view->setting = $searchSettingRow->toSettingObject();
		$this->view->setting->mapOption    = getInstanceUser('cms')->getMapOption();
		$this->view->setting->is_fdp = FdpType::getInstance()->isFDP(getInstanceUser('cms')->getProfile());
		$this->view->setting->town_label = FdpType::getInstance()->getTownLabel()[2];
		if (!empty($this->view->setting->display_fdp->fdp_type)) {
			$this->view->setting->fdp_check_label = FdpType::getInstance()->getLabelFdp($this->view->setting->display_fdp->fdp_type);
		}
		if (!empty($this->view->setting->display_fdp->town_type)) {
			$this->view->setting->town_check_label = FdpType::getInstance()->getLabelTown($this->view->setting->display_fdp->town_type);
		}
		$this->view->dispEstateRequest = (getInstanceUser('cms')->getProfile()->cms_plan >= config('constants.cms_plan.CMS_PLAN_LITE') ? 1 : 0);

		// マスタ
		$this->view->prefMaster       = PrefCodeList::getInstance()->getAll();
		$this->view->searchTypeMaster = SearchTypeList::getInstance()->getAll();
		$this->view->searchTypeConst  = SearchTypeList::getInstance()->getKeyConst();
		$this->view->estateTypeMaster = TypeList::getInstance()->getByClass($class);

		// 特集利用の有無チェック
		$enaDelete = 1;	// 削除可
		$spTable = App::make(SpecialEstateRepositoryInterface::class);
		$spWhere = [
			['hp_id', $hp->id],
			['hp_estate_setting_id', $setting->id],
			['estate_class', $class]
		];
		$spCount = $spTable->countRows($spWhere);
		if ($spCount) {
			$enaDelete = 0;
		}
		$this->view->enaDelete = $enaDelete;

		// 予約公開の有無チェック
		$isScheduled = false;
		$table = App::make(HpPageRepositoryInterface::class);
		$linkIds = $searchSettingRow->getLinkIdList();
		$settingEstateClassListAfterDelete = array_diff($table->getSettingEstateClassList($setting), array($class));
		$settingEstateClassList = array_values($settingEstateClassListAfterDelete);
		$innerLinkPages = $table->getInnerLinkPages($hp, array_keys($linkIds), $settingEstateClassList);

		if (count($innerLinkPages)) {
			$isScheduled = App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds($innerLinkPages);;
		}
		$this->view->isScheduled = $isScheduled;

		return view('estate-search-setting.detail');
	}

	public function apiDelete(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$classes = ClassList::getInstance()->getAll();
		if (!isset($classes[$request->class])) {
			return $this->_forward404();
		}
		$class = $request->class;

		$hp = getInstanceUser('cms')->getCurrentHp();
		$setting = $hp->getEstateSetting();
		if (!$setting) {
			return;
		}
		$searchSettingRow = $setting->getSearchSetting($class);
		if (!$searchSettingRow) {
			return;
		}

		// 特集利用の有無チェック
		$spTable = App::make(SpecialEstateRepositoryInterface::class);
		$spWhere = [
			['hp_id', $hp->id],
			['hp_estate_setting_id', $setting->id],
			['estate_class', $class]
		];
		$spCount = $spTable->countRows($spWhere);
		if ($spCount) {
			throw new Exception("特集が設定されています");
		}

		$table   = App::make(HpPageRepositoryInterface::class);

		$linkIds = $searchSettingRow->getLinkIdList();
		$settingEstateClassListAfterDelete = array_diff($table->getSettingEstateClassList($setting), array($class));
		$settingEstateClassList = array_values($settingEstateClassListAfterDelete);
		$innerLinkPages = $table->getInnerLinkPages($hp, array_keys($linkIds), $settingEstateClassList);
		try {
			DB::beginTransaction();

			App::make(EstateClassSearchRepositoryInterface::class)->delete($searchSettingRow->id, true);

			// cmsデータ更新
			$setting->cmsLastUpdated();

			// ページ作成・更新上のリンクを削除
			$table->deleteLinkPages($innerLinkPages);

			//CMS操作ログ
			CmsOperation::getInstance()->cmsLogEstate(LogEditType::ESTATE_SETTING_DELETE, $class);

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data = [];
		$data['cms_plan'] = $cms_plan;

		return $this->success($data);
	}
}
