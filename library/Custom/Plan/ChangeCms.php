<?php

namespace Library\Custom\Plan;

use Library\Custom\Model\Lists\CmsPlan;
use Illuminate\Http\Request;
use DB;
use App;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use App\Repositories\MTheme\MThemeRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\HpContact\HpContactRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use App\Repositories\HpSideElements\HpSideElementsRepositoryInterface;
use App\Repositories\HpMainElementElement\HpMainElementElementRepositoryInterface;
use App\Repositories\HpImageUsed\HpImageUsedRepositoryInterface;
use App\Repositories\HpFile2Used\HpFile2UsedRepositoryInterface;

use Library\Custom\Plan;
use Library\Custom\Model\Lists;

/**
 *
 */
class ChangeCms
{
	/**
	 * 指定されたユーザの契約情報予約を現在の契約情報に移す
	 */
	public function setNowPlanByUser(&$user)
	{
		$id			= $user->getProfile()->id;
		$commany	= App::make(CompanyRepositoryInterface::class);
		$row		= $commany->find($id);
		$this->updatePlanInfo($row);
		$memberNo	= $user->getProfile()->member_no;
		$profile	= $commany->fetchLoginProfileByMemberNo($memberNo);
		$user->setProfile($profile);
	}

	/**
	 * 変更対象のプランを変更（バッチからも呼ばれている）
	 * @param App\Models\Company $row
	 * @param int $hpId
	 * @throws Exception
	 */
	public function changePlan($hpId, $row)
	{
		if ($row->reserve_cms_plan == $row->cms_plan) {	// 同一プランへの変更なので契約情報を更新するだけ
			$this->updatePlanInfo($row);
			return;
		}

		if (isset($row->reserve_cms_plan) && is_null($row->cms_plan)) {
			$row->cms_plan = $row->reserve_cms_plan;
		}

		if ($this->_isUpdate($row) == false) {
			return;	// 予約情報が妥当でないので何もしない。
		}

		$hpTable 		= App::make(HpRepositoryInterface::class);
		$hpPagetable	= App::make(HpPageRepositoryInterface::class);
		$reserveTable	= App::make(ReleaseScheduleRepositoryInterface::class);
		$reserveSpecialTable = App::make(ReleaseScheduleSpecialRepositoryInterface::class);
		DB::beginTransaction();

		$planNow	= Plan::factory(Lists\CmsPlan::getCmsPLanName($row->cms_plan));
		$planTo		= Plan::factory(Lists\CmsPlan::getCmsPLanName($row->reserve_cms_plan));

		$idPlanNow = $row->cms_plan;
		$idPlanTo  = $row->reserve_cms_plan;
		$isUpdate = $this->_isUpdate($row);

		$diffPages	= $planNow->getDiffPages($planTo);
		if ($row->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
			$hpTable->update($hpId, array('slide_show' => '{"slide_show_flg":1,"slideshow":1,"effect_slideshow":[2,2,0]}'));
		}
		foreach ($diffPages['add'] as $pageCodeType) {
			// NHP-3767 ぺージタイプからカテゴリ取得
			$pageCategoryCode = $planTo->getCategoryByType($pageCodeType);

			$this->addToOuter($hpPagetable, $hpId, $pageCodeType, $pageCategoryCode);
		}
		foreach ($diffPages['del'] as $pageCodeType) {
			$this->moveToOuter($hpPagetable, $hpId, $pageCodeType);
		}

		switch ($row->reserve_cms_plan) {
			case config('constants.cms_plan.CMS_PLAN_STANDARD'):
				$this->_setAutoReply($hpId, 0);
				$this->_setEstateRequest($hpId, 0);
				// ATHOME_HP_DEV-5807 プランダウンした際に不動産お役立ち情報ページが不要なケースでは合わせて削除する
				$this->removeArticleTopPage($hpId, $hpPagetable);
				break;
			case config('constants.cms_plan.CMS_PLAN_LITE'):
				$this->_setAutoReply($hpId, 0);
				$this->moveSearchSetting($hpId);
				$this->removeLinkHouseAllParts($hpId);
				App::make(EstateAssociatedCompanyRepositoryInterface::class)->delete([['parent_company_id', $row->id]]);	// ATHOME_HP_DEV-3039 物件グループの自動入力について
				// ATHOME_HP_DEV-5807 プランダウンした際に不動産お役立ち情報ページが不要なケースでは合わせて削除する
				$this->removeArticleTopPage($hpId, $hpPagetable);
				$this->initHankyoPlusUseFlg($row);
				break;
		}

		$this->checkDesignDownPlan($row);
		$this->updatePlanInfo($row);
		$this->checkUseDesign($row);
		$this->updateTopOriginal($row, $idPlanNow, $idPlanTo, $isUpdate);

		// $hpTable		->update( array( 'all_upload_flg'	=> 1 ), array( 'id		= ?' => $hpId ) ) ;
		// ATHOME_HP_DEV-3126
		$hprow = $hpTable->find($hpId);
		$hprow->all_upload_flg = 1;
		$hprow->setAllUploadParts('ALL', 1);
		$hprow->save();

		$this->deleteTheHpID($reserveTable, $hpId);
		$this->deleteTheHpID($reserveSpecialTable, $hpId);
		DB::commit();
	}

	/**
	 * 指定されたテーブルの指定されたHpIDを持つレコードの削除する
	 */
	protected	function deleteTheHpID(&$table, $hpId)
	{
		$table->delete(array(['hp_id', $hpId]));
	}

	/**
	 * 使用しているデザインテンプレート（テーマ）が変更後のプランで使えない場合は、「スタンダード」に変更する
	 */
	protected	function checkUseDesign(&$row)
	{
		if ($row->getCurrentHp() == false) {
			return;				// HP自体が無いため何もしない
		}

		$table		= App::make(MThemeRepositoryInterface::class);
		$table->setPlan($row->cms_plan);
		$theme_id	= $row->getCurrentHp()->theme_id;
		if ($theme_id == null) {
			return;				// テーマが設定されていないので何もしない
		}

		$customData = $table->getCustomData($row->id);
		if ($customData && ($customData->count() > 0) && (array_search($theme_id, $customData) !== false)) {	// 顧客別にカスタム用のテーマを使用している
			return;
		}
		$themeRow = $table->fetchTheme($theme_id);
		if ($themeRow->plan == 0) {	// 使用できないテーマを使用している
			$table	= App::make(HpRepositoryInterface::class);
			$hpId	= $row->getCurrentHp()->id;
			$hpRow	= $table->find($hpId);
			$hpRow->theme_id	= 1;	// スタンダードテーマ（何処かに定義は無いのかな？）
			$hpRow->color_id	= 1;	// 色を最初の選択肢に（何処かに定義は無いのかな？）
			$hpRow->save();
		}
	}

	/**
	 * 契約情報予約を現在の契約情報に移す
	 */
	public function updatePlanInfo(&$row, $allowUpdateCheck = true)
	{
		if (($this->_isUpdate($row) == false) && $allowUpdateCheck) {
			return;	// 予約情報が妥当でないので何もしない。
		}

		if (!empty($row->end_date) && (strtotime($row->end_date) < time())) {
			$row->end_date						= null	;		// 停止中だった場合、再契約として利用中にする為に削除
			$row->applied_end_date				= null;
			$row->cancel_staff_id				= null;
			$row->cancel_staff_name				= null;
			$row->cancel_staff_department		= null;
			$this->initHankyoPlusUseFlg($row);
		}

		if ($row->initial_start_date == null) {
			$row->initial_start_date			= $row->reserve_start_date;
		}
		$row->cms_plan							= $row->reserve_cms_plan;
		$row->start_date						= $row->reserve_start_date;
		$row->applied_start_date				= $row->reserve_applied_start_date;
		$row->contract_staff_id					= $row->reserve_contract_staff_id;
		$row->contract_staff_name				= $row->reserve_contract_staff_name;
		$row->contract_staff_department			= $row->reserve_contract_staff_department;
		$row->reserve_cms_plan					= 0;
		$row->reserve_applied_start_date		= null;
		$row->reserve_start_date				= null;
		$row->reserve_contract_staff_id			= null;
		$row->reserve_contract_staff_name		= null;
		$row->reserve_contract_staff_department	= null;
		$row->save();
	}

	/**
	 * 予約情報を反映させる事が妥当か
	 */
	protected 	function	_isUpdate(&$row)
	{
		$result		= true;
		if (CmsPlan::getCmsPLanName($row->cms_plan) == 'unknown') {
			$result		= false;	// 変更元プランが不明なのでNG
		}

		if (CmsPlan::getCmsPLanName($row->reserve_cms_plan) == 'unknown') {
			$result		= false;	// 変更プランが不明なのでNG
		}

		$startDate	= strtotime($row->reserve_start_date);
		if (($startDate === false) || ($startDate > time())) {
			$result		= false;	// 利用開始日が不定or未来なのでNG
		}

		return $result;
	}

	/**
	 * 自動返信メールを使えるようにするか設定にする
	 *
	 * @param	$hpId			int								ホームページID
	 */
	protected function _setAutoReply($hpId, $val)
	{
		$table	= App::make(HpContactRepositoryInterface::class);
		$rowSet	= $table->fetchAll(array(['hp_id', $hpId]));
		foreach ($rowSet as $row) {
			$row['autoreply_flg'] = $val;
			$row->save();
		}
	}

	/**
	 * 物件リクエスト利用の設定
	 *
	 * @param	$hpId			int								ホームページID
	 */
	protected function _setEstateRequest($hpId, $val)
	{
		$table	= App::make(HpEstateSettingRepositoryInterface::class);
		$row	= $table->fetchRow(array(['hp_id',  $hpId], ['setting_for',	config('constants.hp_estate_setting.SETTING_FOR_CMS')]));
		if ($row == null) {
			return;
		}

		$table	= App::make(EstateClassSearchRepositoryInterface::class);
		$rowSet	= $table->fetchAll(array(['hp_id', $hpId], ['hp_estate_setting_id', $row->id]));
		foreach ($rowSet as $row) {
			$row['estate_request_flg'] = $val;
			$row->save();
		}
	}

	/**
	 * 指定されたページタイプのページを階層外に「未作成」で追加
	 *
	 * @param	$table			App\Models\HpPage&		hp_pageテーブルのインスタンスへのリファレンス
	 * @param	$hpId			int								ホームページID
	 * @param	$pageCodeType	int								追加対象ページID
	 * @param	$pageCategoryCode	int							追加対象ページのカテゴリID(NHP-3767)
	 */
	protected function addToOuter(&$table, $hpId, $pageCodeType, $pageCategoryCode)
	{
		if ($table->isRequiredWithoutHierarchyPage($pageCodeType)) {
			return;
		}
		$row['hp_id'] = $hpId;
		$row['page_type_code'] = $pageCodeType;
		$row['page_category_code'] = $pageCategoryCode;
		$row['level']           = 1;
		$row['new_flg'] = 1;
		$row['title'] = $table->getTypeNameJp($pageCodeType);
		$row['filename'] = $table->getPageNameJp($pageCodeType);
		$id                       = $table->create($row)->id;
		$row = $table->find($id);
		$row['link_id'] = $id;
		$row->save();
	}

	/**
	 * 指定されたページタイプのページを削除、その配下を階層外へ移動
	 *
	 * @param	$table			HpPageRepository&		hp_pageテーブルのインスタンスへのリファレンス
	 * @param	$hpId			int								ホームページID
	 * @param	$pageCodeType	int								移動対象ページID
	 */
	protected function moveToOuter(&$table, $hpId, $pageCodeType)
	{
		$where	= array(
			['hp_id', $hpId],
			['page_type_code', $pageCodeType],
		);
		$rowset = $table->fetchAll($where);
		
		$model_hp_image_used	= App::make(HpImageUsedRepositoryInterface::class);
		$model_hp_file2_used	= App::make(HpFile2UsedRepositoryInterface::class);
		$linkPage = array(HpPageRepository::TYPE_LINK, HpPageRepository::TYPE_ALIAS, HpPageRepository::TYPE_ESTATE_ALIAS, HpPageRepository::TYPE_LINK_HOUSE);
		foreach ($rowset as $row) {
			if (in_array($row->page_category_code, $table->getCategoryCodeArticle())) {
				$this->deletePageByParent($table, $row->id, $hpId);
			}
			$model_hp_image_used->delete(array(['hp_page_id', $row->id]), true);	// 削除するページに使用されていたイメージを、そのページでは、使っていない様にする
			$model_hp_file2_used->delete(array(['hp_page_id', $row->id]), true);	// 削除するページに使用されていたファイルを、そのページでは、使っていない様にする
			$table->delete(array(['hp_id', $hpId], 'whereIn' => ['page_type_code', $linkPage], ['parent_page_id', $row->id]));
			$this->moveToOuterByParent($table, $row->id);
			$table->delete(array(['link_page_id', $row->link_id], ['hp_id', $hpId]));
			$row->parent_page_id	= null;
			$row->save();
			$row->delete();
		}
	}

	/**
	 * 指定された親ページを持つページを階層外へ移動
	 *
	 * @param	$table			HpPage&		hp_pageテーブルのインスタンスへのリファレンス
	 * @param	$ParentPageId	int								移動対象ページの親ID
	 */
	protected function moveToOuterByParent(&$table, $ParentPageId)
	{
		$rowset = $table->fetchAll(array(['parent_page_id', $ParentPageId]));

		foreach ($rowset as $row) {
			$this->moveToOuterByParent($table, $row->id);
			$row->parent_page_id	= null;
			$row->save();
		}
	}

	// Set default theme when down plan (toporiginal)
	protected function checkDesignDownPlan($row)
	{
		if ($row->getCurrentHp() == false) {
			return;
		}
		$table = App::make(MThemeRepositoryInterface::class);
		$table->setPlan($row->cms_plan);
		$theme_id = $row->getCurrentHp()->theme_id;
		if ($theme_id == null) {
			if ($row->checkTopOriginal()) {
				$table = App::make(HpRepositoryInterface::class);
				$hpId = $row->getCurrentHp()->id;
				$hpRow = $table->find($hpId);
				$hpRow->theme_id = 1;
				$hpRow->color_id = 1;
				$hpRow->save();
			}
		}
	}

	protected function moveSearchSetting($hpId)
	{
		// delete mainpart koma
		App::make(HpMainPartsRepositoryInterface::class)->delete(array(['hp_id', $hpId], ['parts_type_code', HpMainPartsRepository::PARTS_ESTATE_KOMA]));
		// delete main parts freeword
		App::make(HpMainPartsRepositoryInterface::class)->delete(array(['hp_id', $hpId], ['parts_type_code', HpMainPartsRepository::PARTS_FREEWORD]));
		// delete side parts freeword
		App::make(HpSidePartsRepositoryInterface::class)->delete(array(['hp_id', $hpId], ['parts_type_code', HpSidePartsRepository::PARTS_FREEWORD]));
		$table	= App::make(HpEstateSettingRepositoryInterface::class);
		$row	= $table->fetchRow(array(['hp_id', $hpId], ['setting_for', 	config('constants.hp_estate_setting.SETTING_FOR_CMS')]));
		if ($row == null) {
			return;
		}
		// delete estateClassSearch
		App::make(EstateClassSearchRepositoryInterface::class)->delete(array(['hp_id', $hpId], ['hp_estate_setting_id', $row->id]));
		// delete SpecialEstate
		App::make(SpecialEstateRepositoryInterface::class)->delete(array(['hp_id', $hpId], ['hp_estate_setting_id', $row->id]));
		$row->delete();
	}

	/**
	 * update top option when change plan
	 * @param $planBefore
	 * @param $planTo
	 * @param $isUpdate
	 * @throws Exception
	 * @param App\Models\Company $row
	 */
	protected function updateTopOriginal($row, $planBefore, $planTo, $isUpdate = true)
	{
		if (!$isUpdate) return;

		$downPlan = false;
		$currentPlan = $row->cms_plan;

		// plan is not change, keep as it is
		if ($currentPlan != $planTo) return;

		// check top before
		$topBefore = false;
		$request = app('request');
		if ($request->has('checkTopBefore')) {
			$topBefore = $request->checkTopBefore;
		}

		//cms plan > top original => disable
		$topTo = false;

		if (
			$planBefore == config('constants.cms_plan.CMS_PLAN_ADVANCE') &&
			$currentPlan < config('constants.cms_plan.CMS_PLAN_ADVANCE')
		) {
			$downPlan = true;
		}

		if ($downPlan == true && $topBefore == true) {
			Lists\Original::callTopOriginalEvent($row, $topTo, $topBefore, $downPlan);
		}
	}

	public function removeLinkHouseAllParts($hpId)
	{
		App::make(HpInfoDetailLinkRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['link_type', config('constants.link_type.HOUSE')]),
			array('delete_flg' => 1)
		);
		App::make(HpTopImageRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['link_type', config('constants.link_type.HOUSE')]),
			array('link_type' => config('constants.link_type.PAGE'), 'link_house' => null)
		);
		App::make(HpSideElementsRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['attr_1', config('constants.link_type.HOUSE')]),
			array('attr_1' => config('constants.link_type.PAGE'), 'attr_8' => null, 'attr_9' => null)
		);
		App::make(HpSidePartsRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['attr_4', config('constants.link_type.HOUSE')]),
			array('attr_4' => config('constants.link_type.PAGE'), 'attr_11' => null)
		);
		App::make(HpMainPartsRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['attr_5', config('constants.link_type.HOUSE')]),
			array('attr_5' => config('constants.link_type.PAGE'), 'attr_12' => null)
		);
		App::make(HpMainElementElementRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['attr_3', config('constants.link_type.HOUSE')], ['type', 'image']),
			array('attr_3' => config('constants.link_type.PAGE'), 'attr_10' => null)
		);
		App::make(HpMainElementElementRepositoryInterface::class)->update(
			array(['hp_id', $hpId], ['attr_3', config('constants.link_type.HOUSE')], ['type', 'image_text']),
			array('attr_3' => config('constants.link_type.PAGE'), 'attr_11' => null)
		);
	}

	protected function removeArticleTopPage($hpId, &$table)
	{
		$rows = $table->fetchAll(array(
			['hp_id', $hpId],
			'whereIn' => ['page_category_code', array(
				HpPageRepository::CATEGORY_TOP_ARTICLE,
				HpPageRepository::CATEGORY_LARGE,
				HpPageRepository::CATEGORY_SMALL
			)],
		), array('desc' => 'page_category_code'));
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$childs = $table->fetchAll(
					array(
						['hp_id', $hpId],
						['parent_page_id', $row->id]
					)
				);
				if (count($childs) == 0) {
					$table->delete(array(['link_page_id', $row->link_id], ['hp_id', $hpId]));
					$row->delete();
				}
			}
		}
	}

	protected function deletePageByParent(&$table, $ParentPageId, $hpId)
	{
		$rowset = $table->fetchAll(array(['parent_page_id', $ParentPageId]));

		foreach ($rowset as $row) {
			$this->deletePageByParent($table, $row->id, $hpId);
			$table->delete(array(['link_page_id', $row->link_id], ['hp_id', $hpId]));
			$row->delete();
		}
	}

	/**
	 * 反響プラス同意フラグを初期化する
	 */
	protected function initHankyoPlusUseFlg( &$row )
	{
		if ( $row->getCurrentHp() == false )
		{
			return	;				// HP自体が無いため何もしない
		}

		$table	= App::make(HpRepositoryInterface::class);
		$hpId	= $row->getCurrentHp()->id						;
		$hpRow	= $table->fetchRow(array(['id' , $hpId ]))		;
		$hpRow->hankyo_plus_use_flg	= 0							;
		$hpRow->save()											;
	}
}
