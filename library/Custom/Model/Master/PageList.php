<?php
namespace Library\Custom\Model\Master;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\Hp\HpRepositoryInterface;
use Illuminate\Support\Facades\App;
use Library\Custom\Model\Estate\ClassList;
use App\Repositories\HpPage\HpPageRepositoryInterface;

class PageList extends MasterAbstract {
	/**
	 * @var Library\Custom\Model\Master\Theme
	 */
	static protected $_instance;
	protected $_tableClass = 'HpPage';
	
	protected $_valueCol = 'link_id';
	protected $_labelCol = 'title';
	
	protected $_hpId = 0;
	protected $_currentId;
	protected $_table;
	
	protected $_ignoreTypes = array(
			HpPageRepository::TYPE_LINK,
			HpPageRepository::TYPE_ALIAS,
			HpPageRepository::TYPE_ESTATE_ALIAS,
            HpPageRepository::TYPE_LINK_HOUSE,
		
			HpPageRepository::TYPE_FORM_LIVINGLEASE,
			HpPageRepository::TYPE_FORM_OFFICELEASE,
			HpPageRepository::TYPE_FORM_LIVINGBUY,
			HpPageRepository::TYPE_FORM_OFFICEBUY,
	);

	function __construct($options = array()) {
		$this->_table =App::make(HpPageRepositoryInterface::class);
		parent::__construct($options);
	}
	
	public function setHpId($hpId) {
		$this->_hpId = $hpId;
		return $this;
	}
	
	public function setCurrentId($id) {
		$this->_currentId = $id;
		return $this;
	}
	
	public function reload() {
		
		$where = $this->_table->getModel()::where('hp_id', $this->_hpId)
		->whereNotIn('page_type_code', $this->_ignoreTypes)
		->where('new_flg', 0)
		->where(function($query){
			$query->where('page_flg', 0)->orWhereNull('page_flg');
		});
		
		if ($this->_currentId) {
			$where->where('id', '!=', $this->_currentId);
		}
		
		$this->setWhere($where);
		
		parent::reload();
		
		// 物件検索TOPへのリンク追加
		$hpTable = App::make(HpRepositoryInterface::class);
		$hp = $hpTable->fetchRow([['id', $this->_hpId]]);
		$estateSetting = $hp->getEstateSetting();
		if (!$estateSetting) {
			return;
		}
		$searchSettings = $estateSetting->getSearchSettingAll();
		if (count($searchSettings) > 0) {
			// リンクのタイトルを取得
			$this->_options[$estateSetting->getLinkId('物件検索トップ')] = $estateSetting->getTitle('物件検索トップ','shumoku',true);
		}
		foreach ($searchSettings as $searchSetting) {
			if ($searchSetting->estate_class == ClassList::CLASS_CHINTAI_KYOJU ||
				$searchSetting->estate_class == ClassList::CLASS_CHINTAI_JIGYO)
			{
				$this->_options[$estateSetting->getLinkId('賃貸物件検索トップ')] = $estateSetting->getTitle('賃貸物件検索トップ','rent',true);
			}elseif ($searchSetting->estate_class == ClassList::CLASS_BAIBAI_KYOJU ||
				$searchSetting->estate_class == ClassList::CLASS_BAIBAI_JIGYO)
			{
				$this->_options[$estateSetting->getLinkId('売買物件検索トップ')] = $estateSetting->getTitle('売買物件検索トップ','purchase',true);
			}
		}
		// 物件検索種目へのリンク追加
		$searchSettings = $estateSetting->getSearchSettingAll();
		foreach ($searchSettings as $searchSetting) {
			foreach ($searchSetting->getLinkIdList(true) as $linkId => $label) {
				$this->_options[$linkId] = $label;
			}
		}
		
		// 物件特集へのリンク追加
		$specials = $estateSetting->getSpecialAll();
		foreach ($specials as $special) {
			$this->_options[$special->getLinkId()] = $special->getTitle(true);
		}
	}
	
	protected function _createOptions($rowset) {
		$this->_options = array();
		foreach ($rowset as $row) {
			$filename = $row->new_flg || !$row->filename ? '' : $row->filename;
			if ($filename) {
				$filename = '（'.$filename.'）';
			}
			$this->_options[$row->{$this->_valueCol}] = $row->{$this->_labelCol} . $filename;
		}
	}
}