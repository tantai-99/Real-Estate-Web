<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpImage\HpImageRepository;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use Library\Custom\Model\Lists\Original;
use App\Http\Form\EstateSetting\Special;
use App\Http\Form\EstateSetting\SpecialMethod;
use Library\Custom\Model\Estate\SearchTypeCondition;
use Library\Custom\Model\Estate\ShumokuSort;
use Library\Custom\Estate\Setting\SearchFilter\Special as SearchFilterSpecial;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Model\Estate\SearchTypeList;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\SpecialPublishEstateList;
use Library\Custom\Model\Estate\SpecialTesuryoKokokuhiList;
use Library\Custom\Model\Estate\SpecialSearchPageTypeList;
use Library\Custom\Estate\Setting\Special as SettingSpecial;
use App\Http\Form\SiteMap\Page;
use App\Http\Form\SiteMap\EstateAlias;
use App\Http\Form\SiteMap\Alias;
use App\Http\Form\SiteMap\LinkHouse;
use App\Http\Form\SiteMap\Link;
use Library\Custom\User\UserAbstract;
use Library\Custom\Util;
use Illuminate\Support\Facades\DB;
use App\Traits\JsonResponse;
use Library\Custom\Model\Lists\ArticleLinkType;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Model\Lists\LogEditType;
use Library\Custom\Hp\Page as HpPage;
use Library\Custom\Hp\Page\Parts\EstateKoma;
use Library\Custom\Controller\Action\InitializedCompany;

class SiteMapController extends InitializedCompany
{
	use JsonResponse;

	protected $settingTmp;

	public function init($request, $next)
	{
		$user = getInstanceUser('cms');
		if ($user->isCreator() && $user->hasBackupData()) {
			return redirect()->route('default.index.index');
		}
		return parent::init($request, $next);
	}

	public function index()
	{
		$this->view->topicPath('ページの作成/更新');

		/** @var App\Models\Hp */
		$hp = getInstanceUser('cms')->getCurrentHp();
		$this->view->hp = $hp;
		$company = $hp->fetchCompanyRow();

		$isTopOriginal = $company->checkTopOriginal();

		$table = App::make(HpPageRepositoryInterface::class);
		$table->setPageTypeInfoUnique($hp, $isTopOriginal);

		if ($isTopOriginal) {
			$pageList = $table->getTypeListJp();
			$pageList[$table::TYPE_TOP] = config('constants.original.TOP_CONTENT');
			$table->setTypeListJp($pageList);
		}

		$this->view->types = $table->getTypeList();
		// $this->view->typeNames = $table->getTypeListJp();
		$typeNames = $table->getTypeListJp();
		$typeNames[HpPageRepository::TYPE_MOVING] = "引っ越しのチェックポイント";
		$typeNames[HpPageRepository::TYPE_BUILDING_EVALUATION] = "中古戸建てはどのように評価されるのか？";
		$this->view->typeNames = $typeNames;
		$this->view->categories = $table->getCategoryList();
		$this->view->categoryNames = $table->getCategories();

		$this->view->fixedMenuTypes = $table->getFixedMenuTypeList();
		$this->view->globalMenuTypes = array_values($table->getGlobalMenuTypeList());
		$this->view->globalMenuNumber = $isTopOriginal ? $hp->global_navigation : 6;
		$this->view->notInMenuTypes = array_values($table->getNotInMenuTypeList());
		$this->view->uniqueTypes = $table->getUniqueTypeList();

		$this->view->hasDetailPageTypes = $table->getHasDetailPageTypeList();
		$this->view->detailPageTypes = $table->getDetailPageTypeList();
		$this->view->hasMultiPageTypes = $table->getHasMultiPageTypeList();
		$this->view->childTypes = $table->getAllChildTypesByType();
		$table->setCategoryMap($company);
		$this->view->categoryMap		= $table->getCategoryMap();

		if ($company->cms_plan >= config('constants.cms_plan.CMS_PLAN_ADVANCE')) {	// TODO:ここ何とかしなきゃ（新しいプランとか色々影響が出る）
			$table->checkNewTemplateData($hp->id);			// 新規追加テンプレートチェック
			// 物件リクエストチェック
			// NHP-3234で物件リクエストを削除できるようになり、削除後は手動で追加する仕様なので物件リクエストチェックをコメントアウトする
			// $table->checkEstateRequest(		$hp->id		) ;
		}
		$this->view->cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$siteMapData = $table->fetchSiteMapRows($hp->id)->toSiteMapArray();
		$pageTopArticle = array_filter($siteMapData, function ($item) {
			return $item['page_type_code'] == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION;
		});
		if (count($pageTopArticle) <= 0) {
			$siteMapData[] = $table->createRowPageArray(HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION, $hp->id);
		}
		foreach ($siteMapData as &$page) {
			if ($table->isLink($page['page_type_code'])) {
				$page['isScheduled'] = App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds(array($page['id']));
			}
		}
		$this->view->siteMapData = $siteMapData;
		$this->view->siteMapIndexData = $table->fetchSiteMapIndexRows($hp->id)->toSiteMapIndexArray();
		$this->view->allCategoryArticlePage = $table->getCategoryCodeArticle();
		$this->view->allTypeArticlePage = $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE);

		if ($setting = $hp->getEstateSetting()) {
			$this->view->estateSiteMapData = $setting->toSiteMapData();
		}

		$this->view->isTopOriginal = (int)$isTopOriginal;
		$this->view->isAgency = (int)getInstanceUser('cms')->isAgency();

		// ATHOME_HP_DEV-3126
		$this->view->allUploadFlg = (int)$hp->all_upload_flg;

		if ($this->view->isTopOriginal && !$this->view->isAgency) {
			$this->view->globalNav = $hp->getGlobalNavigation()->toSiteMapArray();
		}

		if ($isTopOriginal) {
			$mapKeyInfoList = Original::$EXTEND_INFO_LIST;
			foreach ($this->view->siteMapData as $k => &$v) {
				if ($v['page_type_code'] == HpPageRepository::TYPE_INFO_INDEX) {
					$notiSetting = App::make(HpMainPartsRepositoryInterface::class)->getSettingForNotification(
						$v['link_id'],
						$hp->id
					);
					if (!$notiSetting) continue;
					$notiSettingData = $notiSetting->toArray();
					$key = $notiSettingData[$mapKeyInfoList['notification_type']];
					$textList = Original::getInfoPageName($key);
					$v['text'] = $textList[$v['page_type_code']];
					if (!(isset($v['detail']) && !is_null($v['detail']))) continue;
					$v['detail']->text = $textList[$v['page_type_code'] + 1];
				} elseif ($v['page_type_code'] == HpPageRepository::TYPE_TOP) {
					$v['title'] = config('constants.original.TOP_CONTENT');
				}
			}

			#3603
			#2019/02/18 don't hide special
			//            $housingBlocksHidden = array();
			//            $topPage = App::make(HpPageRepositoryInterface::class)->getTopPageData($hp->id);
			//            $housingBlocksData = $topPage->fetchParts(HpMainPartsRepository::PARTS_ESTATE_KOMA);
			//            $housingBlocks= $housingBlocksData->toArray();
			//            $display_flg = EstateKoma::CMS_DISABLE;
			//            $special_id = EstateKoma::SPECIAL_ID_ATTR;
			//            foreach($housingBlocks as $k => $housingBlock){
			//                if($housingBlock[$display_flg] == 1) continue;
			//                $housingBlocksHidden[] = $housingBlock[$special_id];
			//            }
			//            $this->view->housingBlocksHidden = $housingBlocksHidden;
		}

		// ATHOME_HP_DEV-4794 物件詳細URL機能を追加する - start
		$setting = $hp->getEstateSetting();
		$this->view->hasSearchSetting = 0;
		if ($setting) {
			// ベースとなる物件検索設定を全て取得
			$searchSettings = $setting->getSearchSettingAll();

			$baseSettings = [];
			foreach ($searchSettings as $searchSettingRow) {
				$searchSetting = $searchSettingRow->toSettingObject();
				$baseSettings[$searchSetting->estate_class] = $searchSetting;
			}
			if (count($baseSettings) > 0) {
				$this->view->hasSearchSetting = 1;
			}
			$this->view->baseSettings = $baseSettings;

			$this->view->form = new Special([
				'hpId' => $hp->id,
				'settingId' => $setting->id,
				'searchSettings' => $searchSettings
			]);
			$this->view->formMethod = new SpecialMethod([
				'hpId' => $hp->id,
				'settingId' => $setting->id,
				'searchSettings' => $searchSettings
			]);
			$this->view->searchTypeConditionMaster = SearchTypeCondition::getInstance()->getAll();

			$selShumoku = [];
			if (isset($this->settingTmp->categories) && isset($this->settingTmp->categories[0]) && $this->settingTmp->categories[0]->category_id == 'shumoku') {
				foreach ($this->settingTmp->categories[0]->items as $val) {
					$selShumoku[] = $val->item_id;
				}
			}
			$shumokuTypeMaster = [];
			$shumoku_sort = ShumokuSort::getInstance()->getAll();
			for ($eno = 1; $eno < 13; $eno++) {
				if (!isset($shumoku_sort[$eno])) {
					continue;
				}

				$searchFilter = new SearchFilterSpecial();
				$searchFilter->loadEnables($eno);
				$searchFilter->asMaster();
				if ($searchFilter->categories[0]->category_id != 'shumoku') {
					continue;
				}
				if (count($searchFilter->categories[0]->items) == 0) {
					continue;
				}
				$shumokuTypeMaster[$eno] = [];

				$sType = [];
				foreach ($searchFilter->categories[0]->items as $item) {
					$checked = (in_array($item->item_id, $selShumoku)) ? '1' : '0';
					$sType[$item->item_id] = ['item_id' => $item->item_id,  'label' => $item->label, 'checked' => $checked];
				}
				foreach ($shumoku_sort[$eno] as $item_id) {
					if (isset($sType[$item_id])) {
						$shumokuTypeMaster[$eno][] = $sType[$item_id];
					} else if (gettype($item_id) == 'string') {
						$shumokuTypeMaster[$eno][] = $item_id;
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

			$specialSetting = new SettingSpecial();
			$this->view->specialSetting = $specialSetting;
		}
		// ATHOME_HP_DEV-4794 物件詳細URL機能を追加する - end

		return view('site-map.index');
	}

	/**
	 * 新規ページを追加
	 */
	public function apiCreatePage(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$form = new Page();

		$form->setData($request->all());
		if (!$form->isValid($request->all())) {
			return $this->success(['errors' => $form->getMessages()]);
		}

		$hp = getUser()->getCurrentHp();
		$company = $hp->fetchCompanyRow();

		$isTopOriginal = UserAbstract::getInstance()->checkAvailableTopOriginal($company->id);

		// 一意なタイプの確認
		$table = App::make(HpPageRepositoryInterface::class);
		$table->setPageTypeInfoUnique($hp, $isTopOriginal);

		$type = (int)$form->getElement('page_type_code')->getValue();
		if (in_array($type, $table->getUniqueTypeList(), true)) {
			if ($table->fetchRow(array(['hp_id', $hp->id], ['page_type_code', $type]))) {
				$form->getElement('page_type_code')->setMessages('ひとつのみ作成可能なページです。');
				return $this->success(['errors' => $form->getMessages()]);
			}
		}

		// 会員ページチェック
		if ($type === HpPageRepository::TYPE_MEMBERONLY && Util::isEmpty($form->getElement('parent_page_id')->getValue())) {
			$form->getElement('page_type_code')->setMessages('階層外には作成できない種別です。');
			return $this->success(['errors' => $form->getMessages()]);
		}

		$items = array();

		DB::beginTransaction();

		$data = $request->all();
		$data['title']       = $table->getTypeNameJp($data['page_type_code']);
		$data['description'] = $table->getDescriptionNameJp($data['page_type_code']);
		$data['keywords']    = $table->getKeywordNameJp($data['page_type_code']);
		$data['filename']    = $table->getPageNameJp($data['page_type_code']);

		$data['new_flg'] = 1;
		$data = $this->_beforeSave($data, $form);

		$row = $table->create($data);
		// ATHOME_HP_DEV-2626 「会員さま専用ページ」が「未作成」だと配下のページが一般ページと判断される
		if ($type === HpPageRepository::TYPE_MEMBERONLY) {
			$row->member_only_flg	= 1;
		}
		$row->save();

		$row->link_id = $row->id;
		$row->save();

		$items[] = $row->toSiteMapArray();

		// 一覧ページ且つ、詳細まとめでない場合
		if (
			$table->hasDetailPageType($row->page_type_code) &&
			!$table->hasMultiPageType($row->page_type_code)
		) {
			// 子を作成する
			$childType = $row->page_type_code + 1;
			$childData = array(
				'title' => $table->getTypeNameJp($childType),
				'description' => $table->getDescriptionNameJp($childType),
				'keywords'    => $table->getKeywordNameJp($childType),
				'filename'    => $table->getPageNameJp($childType),
				'new_flg' => 1,
				'page_type_code' => $childType,
				'parent_page_id' => $row->id,
				'sort' => 0,
			);
			$childData = $this->_beforeSave($childData);
			$childData['level'] = $row->level + 1;
			$childRow = $table->create($childData);
			$childRow->save();

			$childRow->link_id = $childRow->id;
			$childRow->save();

			$items[] = $childRow->toSiteMapArray();
		}
		DB::commit();


		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}


	/**
	 * 既存ページを追加
	 * 階層外からグロナビへ移動
	 */
	public function apiAddPage(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$table = App::make(HpPageRepositoryInterface::class);
		$row = $table->fetchRow(array(['id', (int)$request->id], ['hp_id', $hp->id]));
		if (!$row) {
			return $this->success(['errors' => array('id' => array('ページが存在しません。'))]);
		}

		if ($row->parent_page_id !== null) {
			return $this->success(['errors' => array('id' => array('階層内のページは追加できません。'))]);
		}

		$autoCreateIndex = false;

		$params = $request->all();
		$params['page_type_code'] = $row->page_type_code;

		$items = array();

		$form = new Page();
		$form->setData($params);
		if (!$form->isValid($params)) {
			// 詳細ページの場合は一覧ページで再度バリデーション
			if ($table->isDetailPageType($row->page_type_code)) {
				$autoCreateIndex = true;
				$params['page_type_code'] = $row->page_type_code - 1;
				$form = new Page();
				if (!$form->isValid($params)) {
					return $this->success(['errors' => $form->getMessages()]);
				}
			} else {
				return $this->success(['errors' => $form->getMessages()]);
			}
		}

		DB::beginTransaction();

		$data = $form->getValues();
		$data['diff_flg'] = 1;
		$data = $this->_beforeSave($data, $form);

		if ($autoCreateIndex) {
			// 一覧ページを作成
			$parentData = $data;
			$parentData['title'] = $table->getTypeNameJp($parentData['page_type_code']);
			$parentData['new_flg'] = 1;
			$parentRow = $table->create($parentData);
			$parentRow->save();

			$parentRow->link_id = $parentRow->id;
			$parentRow->save();

			$data = array(
				'parent_page_id' => $parentRow->id,
				'level' => $parentRow->level + 1,
				'sort' => 0,
				'diff_flg' => 1,
			);

			$items[] = $parentRow->toSiteMapArray();
		}

		$row->setFromArray($data);
		$row->save();

		$items[] = $row->toSiteMapArray();

		DB::commit();

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	/**
	 * 既存ページへのリンクを追加
	 */
	public function apiCreateAlias(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$params = $request->all();
		if (isset($params['link_page_id']) && strpos((string)$params['link_page_id'], 'estate_') === 0) {
			$params['page_type_code'] = HpPageRepository::TYPE_ESTATE_ALIAS;
			$form = new EstateAlias();
			$isEstate = true;
		} else {
			$params['page_type_code'] = HpPageRepository::TYPE_ALIAS;
			$form = new Alias();
			$isEstate = false;
		}
		$form->setData($params);
		if (!$form->isValid($params)) {
			return $this->success(['errors' => $form->getMessages()]);
		}
		
		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data = $form->getValues();
			$data['diff_flg'] = 1;
			$data = $this->_beforeSave($data, $form);

			// 物件ページへのリンクの場合、link_estate_page_idに保存する
			if ($isEstate) {
				$data['link_estate_page_id'] = $data['link_page_id'];
				unset($data['link_page_id']);
			} else {
				$table   = App::make(HpPageRepositoryInterface::class);
				$row = App::make(HpPageRepositoryInterface::class)->fetchRowByLinkId($params['link_page_id'], $hp->id);
				if (in_array($row->page_category_code, App::make(HpPageRepositoryInterface::class)->getCategoryCodeArticle())) {
					$data['link_article_flg'] = 1;
					$data['article_parent_id'] = $row->parent_page_id;
				}
			}

			$table = App::make(HpPageRepositoryInterface::class);
			$row = $table->create($data);
			$row->save();
			$items = array($row->toSiteMapArray());

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	/**
	 * 既存ページへのリンクを編集
	 */
	public function apiUpdateAlias(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$where = array(
			['id', (int)$request->id],
			['hp_id', $hp->id],
			'whereIn' => [
				'page_type_code', [HpPageRepository::TYPE_ALIAS, HpPageRepository::TYPE_ESTATE_ALIAS]
			],
		);
		$table = App::make(HpPageRepositoryInterface::class);
		$row = $table->fetchRow($where);
		if (!$row) {
			$this->_forward404();
		}

		$params = $request->all();
		$params['parent_page_id'] = $row->parent_page_id;
		$params['sort'] = $row->sort;
		if (isset($params['link_page_id']) && strpos((string)$params['link_page_id'], 'estate_') === 0) {
			$params['page_type_code'] = HpPageRepository::TYPE_ESTATE_ALIAS;
			$form = new EstateAlias();
			$isEstate = true;
		} else {
			$params['page_type_code'] = HpPageRepository::TYPE_ALIAS;
			$form = new Alias();
			$isEstate = false;
		}
		$form->setData($params);
		if (!$form->isValid($params)) {
			return $this->success(['errors' => $form->getMessages()]);
		}

		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data = $form->getValues();
			$data['diff_flg'] = 1;
			$data = $this->_beforeSave($data, $form);

			// 物件ページへのリンクの場合、link_estate_page_idに保存する
			if ($isEstate) {
				$data['link_estate_page_id'] = $data['link_page_id'];
				$data['link_page_id'] = null;
			} else {
				$data['link_estate_page_id'] = null;
				$originalRow = App::make(HpPageRepositoryInterface::class)->fetchRowByLinkId($params['link_page_id'], $hp->id);
				if (in_array($originalRow->page_category_code, App::make(HpPageRepositoryInterface::class)->getCategoryCodeArticle())) {
					$data['link_article_flg'] = 1;
					$data['article_parent_id'] = $originalRow->parent_page_id;
				}
			}

			$row->setFromArray($data);
			$row->save();
			$items = array($row->toSiteMapArray());

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	/**
	 * URLリンクを追加
	 */
	public function apiCreateLink(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$params = $request->all();
		$isHouse = false;
		if (array_key_exists('link_house', $params)) {
			$params['page_type_code'] = HpPageRepository::TYPE_LINK_HOUSE;
			$form = new LinkHouse();
			$isHouse = true;
		} else {
			$params['page_type_code'] = HpPageRepository::TYPE_LINK;
			$form = new Link();
		}
		$form->setData($params);
		if (!$form->isValid($params)) {
			return $this->success(['errors' => $form->getMessages()]);
		}

		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data = $form->getValues();
			$data['diff_flg'] = 1;
			if ($isHouse) {
				$data['title'] = $data['title_house'];
				unset($data['title_house']);
				$linkHouse = array(
					'url' => $data['link_house'],
					'search_type' => $params['search_type'] ? $params['search_type'] : 0
				);
				if (isset($params['house_no'])) {
					$linkHouse['house_no'] = $params['house_no'];
				}
				if (isset($params['house_type'])) {
					$linkHouse['house_type'] = explode(',', $params['house_type']);
				}

				$data['link_house'] = json_encode($linkHouse);
			}
			$data = $this->_beforeSave($data, $form);

			$table = App::make(HpPageRepositoryInterface::class);
			$row = $table->create($data);
			$row->save();
			$items = array($row->toSiteMapArray());

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	/**
	 * URLリンクを編集
	 */
	public function apiUpdateLink(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$where = array(
			['id', (int)$request->id],
			['hp_id', $hp->id],
			'whereIn' => [
				'page_type_code', [HpPageRepository::TYPE_LINK, HpPageRepository::TYPE_LINK_HOUSE]
			],
		);

		$table = App::make(HpPageRepositoryInterface::class);
		$row = $table->fetchRow($where);
		if (!$row) {
			$this->_forward404();
		}

		$params = $request->all();
		$params['parent_page_id'] = $row->parent_page_id;
		$params['sort'] = $row->sort;
		$isHouse = false;
		if (array_key_exists('link_house', $params)) {
			$params['page_type_code'] = HpPageRepository::TYPE_LINK_HOUSE;
			$form = new LinkHouse();
			$isHouse = true;
		} else {
			$params['page_type_code'] = HpPageRepository::TYPE_LINK;
			$form = new Link();
		}
		$form->setData($params);
		if (!$form->isValid($params)) {
			return $this->success(['errors' => $form->getMessages()]);
		}

		$table   = App::make(HpPageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data = $form->getValues();
			$data['diff_flg'] = 1;
			if ($isHouse) {
				$data['title'] = $data['title_house'];
				unset($data['title_house']);
				$linkHouse = array(
					'url' => $data['link_house'],
					'search_type' => $params['search_type'] ? $params['search_type'] : 0
				);
				if (isset($params['house_no'])) {
					$linkHouse['house_no'] = $params['house_no'];
				}

				if (isset($params['house_type'])) {
					$linkHouse['house_type'] = explode(',', $params['house_type']);
				}

				$data['link_house'] = json_encode($linkHouse);
			}
			$data = $this->_beforeSave($data);

			$row->setFromArray($data);
			$row->save();
			$items = array($row->toSiteMapArray());

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	protected function _beforeSave($data, $form = null)
	{
		if (Util::isEmptyKey($data, 'parent_page_id')) {
			$data['parent_page_id'] = null;
			$data['level'] = 1;
		} else {
			if ($data['parent_page_id'] == 0) {
				$data['level'] = 1;
			} else if ($form) {
				$validator = $form->getElement('parent_page_id')->getValidator('ParentHpPageId');
				$data['level'] = $validator->getRow()->level + 1;
			}
		}

		// カテゴリを設定
		$table = App::make(HpPageRepositoryInterface::class);
		$data['page_category_code'] = $table->getCategoryByType($data['page_type_code']);

		// ホームページID
		$data['hp_id'] = getUser()->getCurrentHp()->id;

		return $data;
	}

	/**
	 * ページをメニューから削除
	 */
	public function apiRemoveFromMenu(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$id = (int) $request->id;
		$hp = getUser()->getCurrentHp();

		$table = App::make(HpPageRepositoryInterface::class);
		$row = $table->fetchRow(array(['id', $id], ['hp_id', $hp->id]));

		if (!$row) {
			return $this->success(['error' => "ページが存在しません。"]);
		}

		if (App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds(array($id))) {
			return $this->success(['error' => "公開予約されています。"]);
		}

		$children = $row->fetchAllChildRecursive();
		if (count($children) != 0 && App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds($children)) {
			return $this->success(['error' => "配下が公開予約されています。"]);
		}

		// トップページはグロナビ固定
		if ($row->page_type_code == HpPageRepository::TYPE_TOP || $row->page_type_code == HpPageRepository::TYPE_MEMBERONLY) {
			return $this->success(['error' => $table->getTypeNameJp($row->page_type_code) . "は削除できません。"]);
		}

		// 詳細まとめは直接操作不可
		if ($table->isMultiPageType($row->page_type_code)) {
			return $this->success(['error' => '削除できません。']);
		}

		$items = array();

		// 階層外へ移動
		DB::beginTransaction();

		// 詳細の場合、兄弟がいない場合は親削除
		if ($table->isDetailPageType($row->page_type_code) && $row->parent_page_id) {
			$siblings = $table->fetchAll(array(['id', $row->id], ['parent_page_id', $row->parent_page_id], ['hp_id', $row->hp_id]));
			if (!count($siblings)) {
				if ($parentRow = $table->fetchRow(array(['id', $row->parent_page_id], ['hp_id', $row->hp_id]))) {
					$table->withoutGlobalScopes();
					$parentRow->delete_flg = 1;
					$parentRow->save();
					$items[] = $parentRow->toSiteMapArray();
				}
			}
			// 兄弟がいる場合、親の差分フラグON
			else {
				if ($row->public_flg && $parentRow = $table->fetchRow(array(['id', $row->parent_page_id], ['hp_id', $row->hp_id]))) {
					$parentRow->diff_flg = 1;
					$parentRow->save();
					$items[] = $parentRow->toSiteMapArray();
				}
			}
		}

		$items = array_merge($items, $row->removePageFromMenuRecursive());

		DB::commit();

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	/**
	 * 並び替え
	 */
	public function apiSort(Request $request)
	{
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();

		$sort = $request->sort;
		if (!is_array($sort)) {
			return;
		}

		$items = array();
		$table = App::make(HpPageRepositoryInterface::class);
		foreach ($sort as $i => $id) {

			if ($id == 0) {
				continue;
			}
			$row = $table->fetchRowById($id);
			// 並び順変更なければcontinue
			if (is_null($row) || $row->sort == $i) {
				continue;
			}

			if (is_numeric($i) && is_numeric($id)) {
				$table->update(array(['id', $id], ['hp_id', $hp->id]), array('sort' => $i, 'diff_flg' => 1));
				$items[] = array(
					'id' => $id,
					'sort' => $i,
				);
			}
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	public function article()
	{
		$this->view->topicPath('ページの作成/更新', '');
		$this->view->topicPath('ページの作成/更新 （不動産お役立ち情報）');

		$hp = getUser()->getCurrentHp();
		$this->view->hp = $hp;
		$company = $hp->fetchCompanyRow();

		$isTopOriginal = $company->checkTopOriginal();
		
		$table = App::make(HpPageRepositoryInterface::class);
		$table->setPageTypeInfoUnique($hp, $isTopOriginal);
		
		if ($isTopOriginal) {
			$pageList = $table->getTypeListJp();
			$pageList[$table::TYPE_TOP] = config('constants.original.TOP_CONTENT');
			$table->setTypeListJp($pageList);
		}
		App::make(HpFile2RepositoryInterface::class)->initSysFile2s($hp->id);
		// 未設定のシステム画像を設定
		App::make(HpImageRepositoryInterface::class)->addSysImages(
			$hp->id,
			HpImageRepository::TYPE_SAMPLE,
			null,
			glob(storage_path('data/samples/images') . DIRECTORY_SEPARATOR . '*.*')
		);

		$this->view->types = $table->getTypeList();
		$this->view->typeNames = $table->getTypeListJp();
		$this->view->categories = $table->getCategoryList();
		$this->view->categoryNames = $table->getCategories();
		
		$this->view->fixedMenuTypes = $table->getFixedMenuTypeList();
		$this->view->globalMenuTypes = array_values($table->getGlobalMenuTypeList());
		$this->view->globalMenuNumber = $isTopOriginal ? $hp->global_navigation : 6;
		$this->view->notInMenuTypes = array_values($table->getNotInMenuTypeList());
		$this->view->uniqueTypes = $table->getUniqueTypeList();

		$this->view->hasDetailPageTypes = $table->getHasDetailPageTypeList();
		$this->view->detailPageTypes = $table->getDetailPageTypeList();
		$this->view->hasMultiPageTypes = $table->getHasMultiPageTypeList();
		$table->setCategoryMap($company);
		$this->view->childTypes = $table->getAllChildTypesUsefulEstateByType();
		$this->view->categoryMap		= $table->getCategoryMap();
		$this->view->articlePageAllPlan = $table->getArticlePageAllPlan();

		if ($company->cms_plan >= config('constants.cms_plan.CMS_PLAN_ADVANCE')) {	// TODO:ここ何とかしなきゃ（新しいプランとか色々影響が出る）
			$table->checkNewTemplateData($hp->id);			// 新規追加テンプレートチェック
			// 物件リクエストチェック
			// NHP-3234で物件リクエストを削除できるようになり、削除後は手動で追加する仕様なので物件リクエストチェックをコメントアウトする
			// $table->checkEstateRequest(		$hp->id		) ;
		}

		$this->view->cmsPlan = getUser()->getProfile()->cms_plan;
		$siteMapData = $table->fetchUsefulRealEstatePages($hp->id)->toSiteMapArray();
		$this->view->siteMapData = $siteMapData;
		$this->view->isTopOriginal = (int)$isTopOriginal;
		$this->view->isAgency = (int)getUser()->isAgency();

		// ATHOME_HP_DEV-3126
		$this->view->allUploadFlg = (int)$hp->all_upload_flg;

		if ($this->view->isTopOriginal && !$this->view->isAgency) {
			$this->view->globalNav = $hp->getGlobalNavigation()->toSiteMapArray();
		}

		$this->view->sideLayoutArticle = $hp->getSideLayout()[config('constants.hp.SIDELAYOUT_ARTICLE_LINK')];		
		$this->view->sideLayoutArticleTitle = ArticleLinkType::getInstance()->getAll();
		$this->view->sideLayoutArticleResult = ArticleLinkType::getInstance()->getListTypeResult();		
		$this->view->isFirstCreatePageArticle = $table->isFirstCreatePageArticle($hp);

		return view('site-map.article');
	}
	/**
	 * create page article
	 */
	public function apiCreatePageArticle(Request $request)
	{
		// $this->_helper->csrfToken();
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$params = $request->all();

		$hp = getUser()->getCurrentHp();
		$company = $hp->fetchCompanyRow();

		$pages = json_decode($params['pages']);
		$parentId = $params['parent_page_id'] != 0 ? $params['parent_page_id'] : null;
		$sort = (int)$params['sort'];

		$template = json_decode(@file_get_contents(storage_path('data/samples/TemplateArticlePage.json')), true);

		$sampleImageMap = App::make(HpImageRepositoryInterface::class)->getSysImageMap(
			$hp->id,
			HpImageRepository::TYPE_SAMPLE
		);

		DB::beginTransaction();

		$items = array();
		try {
			if (!$this->_createHpPageRecursive($hp, $pages, $parentId, $sort, $items, $template, $sampleImageMap)) {
				$data_page['error'] = true;
				$data_page['message'] = '同時に作成できる上限を超えているため、作成できません。作成ページ数を減らしてください。';

				DB::rollback();
			} else {

				if ((bool)$params['isFirstCreatePageArticle']) {
					$hpRow = App::make(HpRepositoryInterface::class)->fetchRow(array(['id', $hp->id]));
					$uploadPart = json_decode($hpRow->all_upload_parts);
					$uploadPart->topside = 1;
					$hpRow->all_upload_flg = 1;
					$hpRow->all_upload_parts = json_encode($uploadPart, JSON_FORCE_OBJECT);
					$hpRow->save();
				}
				$data_page['error'] = false;
				DB::commit();
			}
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}
	/**
	 * create page article
	 */
	public function apiDeletePageArticle(Request $request)
	{
		// $this->_helper->csrfToken();
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$params = $request->all();
		$hp = getUser()->getCurrentHp();
		$company = $hp->fetchCompanyRow();

		$pages = $params['pages'];
		$table = App::make(HpPageRepositoryInterface::class);
		
		DB::beginTransaction();
		foreach ($pages as $id) {
			try {
				$row = $table->fetchRow(array(['id', $id], ['hp_id', $hp->id]));
				$page = HpPage::factory($hp, $row);
				
				if (!$page->canDelete()) {
					// $data_page['error'][$id] = '削除できません。';
					DB::rollback();
					return $this->success(['error'[$id] => '削除できません。']);
				}
				
				$page->deletePage();
			} catch (\Exception $e) {
				DB::rollback();
				throw $e;
			}
		}

		DB::commit();
		$items = $table->fetchUsefulRealEstatePages($hp->id)->toSiteMapArray();

		$data_page['items'] = $items;
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}

	public function apiSaveSetLinkArticle(Request $request)
	{
		// $this->_helper->csrfToken();
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$type = (int) $request->type;
		$hp = getUser()->getCurrentHp();
		$articleLinkType = ArticleLinkType::getInstance()->getAll();
		if (!array_key_exists($type, $articleLinkType)) {
			return $this->success(['error' => 'Type isvalib']);
		}
		$hpRow = App::make(HpRepositoryInterface::class)->fetchRow(array(['id', $hp->id]));
		$sideLayout = $hpRow->getSideLayout();
		$sideLayout[config('constants.hp.SIDELAYOUT_ARTICLE_LINK')]['type'] = $type;
		if ($hpRow->side_layout != json_encode($sideLayout, JSON_FORCE_OBJECT)) {
			$hpRow->side_layout = json_encode($sideLayout, JSON_FORCE_OBJECT);
			$hpRow->all_upload_flg = 1;
			$uploadPart = json_decode($hpRow->all_upload_parts);
			$uploadPart->topside = 1;
			$hpRow->all_upload_parts = json_encode($uploadPart, JSON_FORCE_OBJECT);
			$hpRow->save();
		}

		$data_page = [];
		$data_page['cms_plan'] = $cms_plan;

		return $this->success($data_page);
	}
	protected function _createHpPageRecursive($hp, $pages, $parentId, $sort = 0, &$items, $template, $sampleImageMap)
	{
		$table = App::make(HpPageRepositoryInterface::class);
		foreach ($pages as $type => $children) {
			$type = trim($type, '"');
			if ($this->checkCanAddPageArticle($type, $hp->id, $table, $parentId)) {
				if (!$filename = $table->getPageNameArticle($type, $hp->id)) {
					return false;
				}
				$data = array(
					'diff_flg'			=> 1,
					'page_type_code'	=> $type,
					'page_category_code' => $table->getCategoryUsefulEstate($type),
					'updated_at'        => date('Y-m-d H:i:s'),
					'title'				=> $table->getTypeNameJp($type),
					'description'		=> $table->getDescriptionNameJp($type),
					'keywords'			=> $table->getKeywordNameJp($type),
					'filename'			=> $filename,
					'parent_page_id'	=> $parentId,
					'level'				=> $table->getLevelByType($type),
					'sort'				=> $sort++,
					'hp_id'				=> $hp->id,
				);

				$row = $table->create($data);
				$row->save();
				$row->link_id = $row->id;
				$hp->createTemplateRealEstatePage($row, $template, $sampleImageMap);
				$row->save();
				$items[] = $row->toSiteMapArray();

				//CMS操作ログ
				CmsOperation::getInstance()->cmsLogPage(LogEditType::PAGE_CREATE, $row->id);
			} else {
				$row = $table->fetchRow(array(['hp_id', $hp->id], ['page_type_code', $type], 'whereIn' => ['page_category_code', $table->getCategoryCodeArticle()]));
				$sort = $sort != 0 ? $sort : 0;
			}

			if (!empty($children) > 0) {
				if (!$this->_createHpPageRecursive($hp, $children, $row->id, $sort, $items, $template, $sampleImageMap)) {
					return false;
				}
			}
		}
		return true;
	}

	public function checkCanAddPageArticle($type, $hpId, $table, $parentId)
	{
		$canAdd = false;
		$where = array(
			['hp_id', $hpId],
			['page_type_code', $type],
			'whereIn' => ['page_category_code', $table->getCategoryCodeArticle()],
		);
		if ($type != HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) {
			$where = array_merge($where, array(['parent_page_id', $parentId]));
		}
		$row = $table->fetchAll($where);

		if (count($row) == 0) {
			$canAdd = true;
		}
		switch ($type) {
			case HpPageRepository::TYPE_LARGE_ORIGINAL:
				if (count($row) < HpPageRepository::MAX_ORIGINAL_LARGE) {
					$canAdd = true;
				}
				break;
			case HpPageRepository::TYPE_SMALL_ORIGINAL:
				if (count($row) < HpPageRepository::MAX_ORIGINAL_SMALL) {
					$canAdd = true;
				}
				break;
			case HpPageRepository::TYPE_ARTICLE_ORIGINAL:
				$canAdd = true;
				break;
		}
		return $canAdd;
	}
}
