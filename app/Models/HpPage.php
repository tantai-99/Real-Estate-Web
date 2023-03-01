<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Models\HpMainPart;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Collections\HpPageCollection;
use Illuminate\Support\Facades\App;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use Library\Custom\Hp\Page;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use Library\Custom\Model\Estate\TypeList;

class HpPage extends Model
{
	use MySoftDeletes;

	protected $table = 'hp_page';
	public $timestamps = false;
	const DELETED_AT = 'delete_flg';
	protected $repository;

	protected $fillable = [
		'id',
		'link_id',
		'page_type_code',
		'public_flg',
		'diff_flg',
		'republish_flg',
		'public_image_ids',
		'public_file_ids',
		'public_file2_ids',
		'published_at',
		'public_title',
		'updated_at',
		'date',
		'page_category_code',
		'parent_page_id',
		'level',
		'sort',
		'link_house',
		'link_url',
		'link_page_id',
		'link_estate_page_id',
		'link_target_blank',
		'contact_count_id',
		'title',
		'description',
		'keywords',
		'filename',
		'member_only_flg',
		'member_id',
		'member_password',
		'page_description',
		'page_flg',
		'new_mark',
		'list_title',
		'hp_id',
		'copied_id',
		'public_path',
		'new_flg',
		'article_parent_id',
		'link_article_flg',
		'delete_flg',
		'create_id',
		'create_date',
		'update_id',
		'update_date',
	];
	public function getRepository() {
		return App::make(HpPageRepositoryInterface::class);
	}
	public function hpImageContent()
    {
        return $this->belongsTo(HpImageContent::class, 'hp_image_content_id' ,);
    }

    public function hpImageUser()
    {   
        return $this->hasOne(hpImageUser::class, 'hp_page_id');
    }

	public function hp() {
        return $this->hasMany(Hp::class, 'hp_id');
    }

	public function isRequiredType()
	{
		$this->repository = App::make(HpPageRepositoryInterface::class);
		return $this->getRepository()->isRequiredType($this->page_type_code);
	}

	public function isUniqueType()
	{
		return $this->getRepository()->isUniqueType($this->page_type_code);
	}

	public function isPublic()
	{
		return $this->public_flg == 1;
	}

	public function isNew()
	{
		return $this->new_flg == 1;
	}

	public function isDraft()
	{
		return !$this->isPublic() && !$this->isNew();
	}

	public function getTypeNameJp()
	{
		$this->repository = App::make(HpPageRepositoryInterface::class);
		return $this->getRepository()->getTypeNameJp($this->page_type_code);
	}

	public function hasChild()
	{
		$table = $this->getRepository();
		return !!$table->fetchRow($this->getChildWhere());
	}

	public function hasPublicChild()
	{
		$table = $this->getRepository();
		$where = $this->getChildWhere(array(['public_flg', 1]));
		return !!$table->fetchRow($where);
	}

	public function countChildren()
	{
		$table = $this->getRepository();
		$table->countRows($this->getChildWhere());
	}

	public function fetchAllChild()
	{

		$table = $this->getRepository();
		return $table->fetchAll($this->getChildWhere());
	}

	public function fetchAllPublishChild($pageSize = null)
	{
		$select = $this->select();
		$select->where('parent_page_id', $this->id);
		$select->where('hp_id', $this->hp_id);

		if ($pageSize) {
			$select->take($pageSize);
			$select->orderBy('date', 'DESC');
			$select->orderBy('id', 'DESC');
		} else {
			$hpMainPart = App::make(HpMainPartsRepositoryInterface::class)->getSettingForNotification($this->link_id, $this->hp_id);
			if ($hpMainPart) {
				$select->take($hpMainPart->attr_3);
				$select->orderBy('date', 'DESC');
				$select->orderBy('id', 'DESC');
			}
		}

		$table = $this->getRepository();
		return $table->fetchAll($select);
	}

	/**
	 * !一覧前提
	 */
	public function countDetail()
	{
		$table = $this->getRepository();
		return $table->countRows($this->getDetailWhere());
	}

	/**
	 * !一覧前提
	 */
	public function fetchAllDetail($order = array(), $count = null, $offset = null)
	{
		if ($order === true) {
			if ($this->hasMultiPageType()) {
				$order = array('desc' => 'date', 'DESC' =>'id');
			} else {
				$order = array('sort', 'id');
			}
		}

		$table = $this->getRepository();
		//$count, $offset
		return $table->fetchAll($this->getDetailWhere(), $order, $count, $offset);
	}

	/**
	 * !一覧前提
	 */
	public function getDetailPageTypeCode()
	{
		return $this->page_type_code + 1;
	}

	/**
	 * !一覧前提
	 */
	public function getDetailWhere($merge = null)
	{
		$where = $this->getChildWhere(array(['page_type_code', $this->getDetailPageTypeCode()]));
		if ($merge) {
			$where = array_merge($where, $merge);
		}
		return $where;
	}

	/**
	 * !一覧前提
	 * 新規詳細ページ行オブジェクト作成
	 */
	public function createDetailRow()
	{
		$row = new HpPage();
		$row->page_type_code = $this->page_type_code + 1;
		$row->parent_page_id = $this->id;
		$row->level = $this->level + 1;
		$row->new_flg = 1;
		$row->hp_id = $this->hp_id;
		$row->sort = null;
		$row->date = null;
		$row->page_category_code = null;
		return $row;
	}

	/**
	 * !一覧前提
	 * 新規詳細ページ行オブジェクト作成
	 */
	public function createInfoDetailRow($type = 0) {
		$row = new HpPage();
		$row->page_type_code = $this->page_type_code + 1;
		$row->parent_page_id = $this->id;
		$row->level = $this->level + 1;
		$row->new_flg = 1;
        $row->hp_id = $this->hp_id;
        $row->page_flg = $type;
		$row->list_title = '<p>'.App::make(HpPageRepositoryInterface::class)->getTypeNameJp($this->page_type_code + 1).'</p>';
		$row->sort = null;
		$row->date = null;
		$row->page_category_code = null;
		return $row;
	}

	public function getChildWhere($merge = null)
	{
		$where = array(['parent_page_id', $this->id], ['hp_id', $this->hp_id]);
		if ($merge) {
			$where = array_merge($where, $merge);
		}
		return $where;
	}

	public function hasMultiPageType()
	{
		return $this->getRepository()->hasMultiPageType($this->page_type_code);
	}

	public function hasDetailPageType()
	{
		$this->repository = App::make(HpPageRepositoryInterface::class);
		return $this->getRepository()->hasDetailPageType($this->page_type_code);
	}

	public function isDetailPageType()
	{
		$this->repository = App::make(HpPageRepositoryInterface::class);
		return $this->getRepository()->isDetailPageType($this->page_type_code);
	}

	public function isMultiPageType()
	{
		return $this->getRepository()->isMultiPageType($this->page_type_code);
	}

	public function isEstateAliasType()
	{
		return $this->getRepository()->isEstateAliasType($this->page_type_code);
	}

	public function notIsPageInfoDetail() {
        return $this->getRepository()->notIsPageInfoDetail($this->page_type_code, $this->page_flg);
    }

	public function removePageFromMenuRecursive()
	{
		$items = array();

		// 階層移動時に差分フラグON
		$this->diff_flg = 1;

		$this->sort = 0;
		$this->repository = App::make(HpPageRepositoryInterface::class);
		if ($this->getRepository()->isMultiPageType($this->page_type_code)) {
			$this->level = 2;
		} else {
			$this->level = 1;
			$this->parent_page_id = null;
		}

		$table = $this->getRepository();

		// リンク、詳細まとめ以外の一覧の場合削除
		if (
			($table->getCategoryByType($this->page_type_code) === HpPageRepository::CATEGORY_LINK) ||
			($table->hasDetailPageType($this->page_type_code) &&
				!$table->hasMultiPageType($this->page_type_code)
			)
		) {
			$this->delete_flg = 1;
		}

		$this->save();

		if (!$table->isMultiPageType($this->page_type_code)) {
			$items[] = $this->toSiteMapArray();
		}

		$children = $this->fetchAllChild();
		foreach ($children as $child) {
			$items = array_merge($items, $child->removePageFromMenuRecursive());
		}
		return $items;
	}

	public function toSiteMapArray()
	{
		$data = array();
		$data['id']					= (int)$this->id;
		$data['link_id']			= (int)$this->link_id;
		$data['page_type_code'] 	= (int)$this->page_type_code;
		$data['page_category_code'] = (int)$this->page_category_code;
		$data['new_flg']			= $this->new_flg == 1;
		$data['public_flg']			= $this->public_flg == 1;
		$data['parent_page_id'] 	= toNumericOrNull($this->parent_page_id);
		$data['sort']				= (int)$this->sort;
		$data['link_url']			= $this->link_url;
		$data['link_page_id']		= toNumericOrNull($this->link_page_id);
		$data['link_estate_page_id'] = $this->link_estate_page_id;
		$data['link_target_blank']	= $this->link_target_blank == 1;
		$data['link_house']	        = $this->link_house;
		$data['title']				= $this->title;
		$data['filename']			= $this->filename;
		$data['deleted']			= $this->delete_flg == 1;
		$data['detail']				= new \stdClass();
		$data['update_date']	    = $this->updated_at ?  $this->updated_at : $this->update_date;

		// 詳細件数の取得
		if (!$this->repository) {
			$this->repository = App::make(HpPageRepositoryInterface::class);
		}
		if ($this->page_type_code == HpPageRepository::TYPE_BLOG_INDEX) {
			$table = $this->getRepository();
			$data['detail']->id = 0;
			$data['detail']->page_type_code = HpPageRepository::TYPE_BLOG_DETAIL;
			$data['detail']->page_category_code = HpPageRepository::CATEGORY_BLOG;
			$data['detail']->parent_page_id = (int)$this->id;
			$data['detail']->sort = 0;
			$data['detail']->count = $table->countRows($this->getChildWhere(array(['page_type_code', HpPageRepository::TYPE_BLOG_DETAIL])));
			$data['detail']->public_flg = $table->countRows($this->getChildWhere(array(['public_flg', 1], ['page_type_code', HpPageRepository::TYPE_BLOG_DETAIL])));
			$data['detail']->new_flg = !$data['detail']->count;
		} else if ($this->page_type_code == HpPageRepository::TYPE_INFO_INDEX) {
			$table = $this->getRepository();
			$data['detail']->id = 0;
			$data['detail']->page_type_code = HpPageRepository::TYPE_INFO_DETAIL;
			$data['detail']->page_category_code = HpPageRepository::CATEGORY_INFO;
			$data['detail']->parent_page_id = (int)$this->id;
			$data['detail']->sort = 0;
			$data['detail']->count = $table->countRows($this->getChildWhere(array(['page_type_code', HpPageRepository::TYPE_INFO_DETAIL])));
			$data['detail']->public_flg = $table->countRows($this->getChildWhere(array(['public_flg', 1], ['page_type_code', HpPageRepository::TYPE_INFO_DETAIL])));
			$data['detail']->new_flg = !$data['detail']->count;
		} else if ($this->page_type_code == HpPageRepository::TYPE_COLUMN_INDEX) {
			$table = $this->getRepository();
			$data['detail']->id = 0;
			$data['detail']->page_type_code = HpPageRepository::TYPE_COLUMN_DETAIL;
			$data['detail']->page_category_code = HpPageRepository::CATEGORY_COLUMN;
			$data['detail']->parent_page_id = (int)$this->id;
			$data['detail']->sort = 0;
			$data['detail']->count = $table->countRows($this->getChildWhere(array(['page_type_code', HpPageRepository::TYPE_COLUMN_DETAIL])));
			$data['detail']->public_flg = $table->countRows($this->getChildWhere(array(['public_flg', 1], ['page_type_code', HpPageRepository::TYPE_COLUMN_DETAIL])));
			$data['detail']->new_flg = !$data['detail']->count;
		}
		return $data;
	}

	public function fetchParts($parts_type_code = null)
	{
		$table = App::make(HpMainPartsRepositoryInterface::class);
		$where = array(
			['hp_id', $this->hp_id],
			['page_id', $this->id]);
		if (!is_null($parts_type_code)) {
			$where = array_merge($where, array(['parts_type_code', $parts_type_code]));
		}

		return $table->fetchAll($where);
	}

	public function fetchPartsWithOrder($parts_type_code = null, $orders = array('desc' => 'create_date'))
	{
		$table = App::make(HpMainPartsRepositoryInterface::class);
		$where = array(
			['hp_id', $this->hp_id],
			['page_id', $this->id],

		);

		if (!is_null($parts_type_code)) {
			$where = array_merge($where, array(['parts_type_code', $parts_type_code]));
		}

		if (!empty($orders)) {
			if (!is_array($orders)) {
				$orders = array('desc' => $orders);
			}
		}

		return $table->fetchAll($where, $orders);
	}

	/**
	 * URLを生成する
	 *
	 * @param int $hp_id
	 * @return App\Models\HpPage
	 */
	public function getPageUrl($id, $url = array())
	{
		$table = App::make(HpPageRepositoryInterface::class);

		// $select = $this->select();
		// $select->where("id", $id);
		$row =  $table->find($id);
		if ($row != null) $url[] = $row->filename;
		if ($row->parent_page_id != null && $row->parent_page_id > 0) {
			$url = $this->getPageUrl($row->parent_page_id, $url);
		}
		return $url;
	}

	/**
	 * 物件エイリアス用
	 * 物件エイリアスリンク元の物件ページのタイトルを取得する
	 * @param boolean $withFilename
	 */
	public function getEstateLinkTitle($withFilename = false)
	{
		$settingTable = App::make(HpEstateSettingRepositoryInterface::class);
		$setting = $settingTable->getSetting($this->hp_id);
		if (!$setting) {
			return null;
		}
		switch ($pageRow->getEstateLinkType()) {
			case HpEstateSetting::ESTATE_LINK_TYPE:
				return $setting->getTitle($withFilename);
			case EstateClassSearch::ESTATE_LINK_TYPE:
				return $this->_getEstateLinkTitleOfEstateType($setting, $withFilename);
			case SpecialEstate::ESTATE_LINK_TYPE:
				return $this->_getEstateLinkTitleOfSpecial($setting, $withFilename);
			default:
				return null;
		}
	}

	/**
	 * 物件種目ページのタイトルを取得する
	 * @param App\Models\HpEstateSetting $setting
	 */
	private function _getEstateLinkTitleOfEstateType($setting, $withFilename = false)
	{
		// 物件種目IDを取得
		$typeId = $this->getEstateLinkTypeId();
		// 物件種目IDから物件種別IDを取得
		$class = TypeList::getInstance()->getClassByType($typeId);
		if (!$class) {
			return null;
		}

		// 物件種別データを取得
		$searchSetting = $setting->getSearchSetting($class);
		if (!$searchSetting || $searchSetting->isEnabledEstateType($type)) {
			return null;
		}

		return $searchSetting->getTitle($type, $withFilename);
	}

	/**
	 * 物件特集ページのタイトルを取得する
	 * @param App\Models\HpEstateSetting $setting
	 */
	private function _getEstateLinkTitleOfSpecial($setting, $withFilename = false)
	{
		// 特集IDを取得
		$typeId = $this->getEstateLinkTypeId();
		// 特集を取得
		$special = $setting->getSpecial($typeId);
		if (!$special) {
			return null;
		}
		return $special->getTitle($withFilename);
	}

	/**
	 * 物件リンク用
	 */
	public function getEstateLinkType()
	{
		$info = $this->_getEstateLinkInfo();
		return isset($info[1]) ? $info[1] : null;
	}

	/**
	 * 物件リンク用
	 */
	public function getEstateLinkTypeId()
	{
		$info = $this->_getEstateLinkInfo();
		return isset($info[2]) ? $info[2] : null;
	}

	/**
	 * [0] -> estete prefix, [1] -> estate link type, [2] -> estate link type id
	 * @return array
	 */
	private function _getEstateLinkInfo()
	{
		if (!$this->isEstateAliasType()) {
			return [];
		}
		return explode('_', (string)$this->link_estate_page_id);
	}

	public function isEstateAliasForEstateSearchTop()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return false;
		}

		return preg_match("/^estate_top/", $this->link_estate_page_id);
	}

	public function isEstateAliasForEstateSearchRent()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return false;
		}

		return preg_match("/^estate_rent/", $this->link_estate_page_id);
	}

	public function isEstateAliasForEstateSearchPurchase()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return false;
		}

		return preg_match("/^estate_purchase/", $this->link_estate_page_id);
	}

	public function isEstateAliasForEstateSearch()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return false;
		}

		return preg_match("/^estate_type_/", $this->link_estate_page_id);
	}

	public function isEstateAliasForSpecial()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return false;
		}

		$prefix = 'estate_special_';
		return preg_match("/^$prefix/", $this->link_estate_page_id);
	}

	public function getEstatePageOriginId()
	{

		if ((int)$this->page_type_code !== HpPageRepository::TYPE_ESTATE_ALIAS) {

			return null;
		}

		$prefix = 'estate_type_';
		if (preg_match("/^$prefix/", $this->link_estate_page_id)) {
			return (int)str_replace($prefix, '', $this->link_estate_page_id);
		}

		$prefix = 'estate_special_';
		if (preg_match("/^$prefix/", $this->link_estate_page_id)) {
			return (int)str_replace($prefix, '', $this->link_estate_page_id);
		}

		return null;
	}

	public function isContactForSearch()
	{

		return $this->getRepository()->isContactForSearch($this->page_type_code);
	}

	//タイトルを設定
	public function setTitle($title)
	{
		$this->title = $title;
		$this->diff_flg = 1;
		$this->save();
	}


	/**
	 * 08_Notification
	 * @param array $order
	 * @param null $count
	 * @param null $offset => apply
	 */
	public function fetchNewsCategories($order = array(), $count = null, $offset = null)
	{
		$table = App::make(HpMainPartsRepositoryInterface::class);
		if (empty($order)) {
			$order = array(
				'DESC' => '-sort',
				'id'
			);
		}

		$id = $this->link_id;
		if ($this->parent_page_id) {
			$parentRow = App::make(HpPageRepositoryInterface::class)->fetchRowById($this->parent_page_id);
			$id = $parentRow->link_id;
		}
		return $table->fetchAll(array(
			['parts_type_code', HpMainPartsRepository::NEWS_CATEGORY],
			['hp_id', $this->hp_id],
			['attr_1', $id],
			['delete_flg', 0]
		), $order, $count, $offset);
	}

	public function createMainPartTopPage($part_type_code, $data = array(), $hp = false, $areaId = null)
	{
		if ($this->page_type_code != HpPageRepository::TYPE_TOP) return;
		$topPage = $this;
		if ($hp === false) {
			$hp = App::make(HpRepositoryInterface::class)->fetchRow(array(
				['id', $this->hp_id]
			));
		}
		/** @var App\Models\Hp $hp */
		$factory = Page::factory($hp, $topPage);
		if ($areaId === null) {
			$area = App::make(HpAreaRepositoryInterface::class)->save($topPage, 1, 1, null);
			$areaId = $area->id;
		}

		/** @var Library\Custom\Hp\Page\Parts\EstateKoma $part */
		$part = $factory->createMainParts($part_type_code);
		$part->setData($data);
		$part->setDefaults($data);
		$part->save($hp, $topPage, $areaId);
	}

	public function isArticlePage() {
		if (!$this->repository) {
			$this->repository = App::make(HpPageRepositoryInterface::class);
		}
        return in_array($this->page_category_code, $this->getRepository()->getCategoryCodeArticle());
	}

	public function toSiteMapIndexArray() {
		$data = array();
		$data['id']					= (int)$this->id;
		$data['link_id']			= (int)$this->link_id;
		$data['page_type_code'] 	= (int)$this->page_type_code;
		$data['page_category_code'] = (int)$this->page_category_code;
		$data['new_flg']			= $this->new_flg == 1;
		$data['public_flg']			= $this->public_flg == 1;
		$data['parent_page_id'] 	= toNumericOrNull($this->parent_page_id);
		$data['sort']				= (int)$this->sort;
		$data['link_url']			= $this->link_url;
		$data['link_page_id']		= toNumericOrNull($this->link_page_id);
		$data['link_estate_page_id']= $this->link_estate_page_id;
		$data['link_target_blank']	= $this->link_target_blank == 1;
		$data['title']				= $this->title;
		$data['filename']			= $this->filename;
		$data['deleted']			= $this->delete_flg == 1;
		$data['detail']				= new \stdClass();
		$data['update_date']	    = $this->updated_at ?  $this->updated_at : $this->update_date;

		return $data;
	}

	public function newCollection(array $models = Array()) {
		return new HpPageCollection($models);
	}

	public function cannotEstateRequest($hp, $page_type_code) {
        return $this->getRepository()->cannotEstateRequest($hp, $page_type_code);
    }

	public function fetchAllChildRecursive() {
		$children = $this->fetchAllChild();
		if ($children->count() == 0) {
			return array();
		}

		$list = array();
		foreach ($children as $child) {
			$list[] = $child['id'];
			$list = array_merge($list, $child->fetchAllChildRecursive());
		}
		return $list;
	}
}
