<?php
namespace Library\Custom\Hp;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use Library\Custom\Form;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Hp\Page\Layout\Section;
use Library\Custom\Hp\Page\Layout\SideCommon;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;
use App\Repositories\HpMainElementElement\HpMainElementElementRepositoryInterface;
use App\Repositories\HpSideElements\HpSideElementsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use Library\Custom\Hp\Page\Layout\Area;
use Library\Custom\Model\Lists\InfoDatailType;
use Library\Custom\User\UserAbstract;
use Library\Custom\Hp\Page\SectionParts;
use Library\Custom\Model\Lists\HpPagePlaceholderData;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use Library\Custom\Model\Lists\LogEditType;
use Library\Custom\Logger\CmsOperation;
use Illuminate\Support\Facades\DB;
use App\Repositories\HpImageUsed\HpImageUsedRepositoryInterface;
use App\Repositories\HpFile2Used\HpFile2UsedRepositoryInterface;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use Library\Custom\Model\Lists\Original;
use App\Exceptions\TopSaveException;
use Library\Custom\Form\Element\Textarea;

class Page {

    public $type;
    public $user;
    public $action;
    public $session;

    /**
     *
     * @var Library\Custom\Form
     */
    public $form;
    public $params;

    const EDIT   = 'edit';
    const SAVE   = 'api-save';
    const DELETE = 'api-delete';

    /**
     * @var App\Repositories\Hp\HpRepository
     */
    protected $_hp;

    /**
     * @var App\Repositories\Company\CompanyRepository
     */
    protected $_company;

    /**
     * @var App\Repositories\OriginalSetting\OriginalSettingRepository
     */
    protected $_originalSetting;

    /**
     *
     * @var App\Models\HpPage
     */
    protected $_row;

    /**
     * @var App\Models\HpPage
     */
    protected $_parentRow;

    /**
     * メイン固有パーツ
     * @var array
     */
    protected $_mainParts = array();

    /**
     * メイン必須パーツ
     * @var array
     */
    protected $_requiredMainParts = array();

    /**
     * 作成済みメインパーツ
     * @var array
     */
    protected $_hasMainParts = array();

    /**
     * メイン共通パーツ
     * @var array
     */
    protected $_commonMainParts = array(
    		HpMainPartsRepository::PARTS_TEXT,
    		HpMainPartsRepository::PARTS_LIST,
    		HpMainPartsRepository::PARTS_TABLE,
    		HpMainPartsRepository::PARTS_MAP,
    		HpMainPartsRepository::PARTS_IMAGE,
    		HpMainPartsRepository::PARTS_ESTATE_KOMA,
    		HpMainPartsRepository::PARTS_YOUTUBE,
            HpMainPartsRepository::PARTS_ESTATE_KOMA_SEARCH_E_R,
            // add freeword mainparts
            HpMainPartsRepository::PARTS_FREEWORD,
            // add freeword ER mainpart
            HpMainPartsRepository::PARTS_SEARCH_FREEWORD_ENGINE_RENTAL,

    		HpMainPartsRepository::PARTS_PANORAMA
    );

    /**
     * プリセットで使用するメインパーツ
     * array(
     *   // area array
     *   array(
     *     // column array
     *     array(
     *       parts_type_code
     *     )
     *   )
     * )
     * @var array
     */
    protected $_presetMainParts = array();

    /**
     * サイド固有パーツ
     * @var array
     */
    protected $_sideParts = array();

    /**
     * サイド共通パーツ
     * @var array
     */
    protected $_commonSideParts = array(
    		HpSidePartsRepository::PARTS_LINK,
    		HpSidePartsRepository::PARTS_IMAGE,
    		HpSidePartsRepository::PARTS_TEXT,
    		HpSidePartsRepository::PARTS_QR,
    		HpSidePartsRepository::PARTS_FB,
    		HpSidePartsRepository::PARTS_TW,
            HpSidePartsRepository::PARTS_MAP,
            HpSidePartsRepository::PARTS_LINE_AT_QR,
            HpSidePartsRepository::PARTS_LINE_AT_BTN,
            //start No.2 add freeword side parts
            HpSidePartsRepository::PARTS_FREEWORD,
            //end
            HpSidePartsRepository::PARTS_PANORAMA,
    );

    protected $_oldTemplateMainParts = array(
        HpMainPartsRepository::PARTS_SELL,
        HpMainPartsRepository::PARTS_REPLACEMENT_AHEAD_SALE,
        HpMainPartsRepository::PARTS_BUILDING_EVALUATION,
        HpMainPartsRepository::PARTS_PURCHASING_REAL_ESTATE,
        HpMainPartsRepository::PARTS_BUYER_VISITS_DETACHEDHOUSE,
        HpMainPartsRepository::PARTS_POINTS_SALE_OF_CONDOMINIUM,
        HpMainPartsRepository::PARTS_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
        HpMainPartsRepository::PARTS_NEWCONSTRUCTION_OR_SECONDHAND,
        HpMainPartsRepository::PARTS_LIFE_PLAN,
        HpMainPartsRepository::PARTS_BUY,
        HpMainPartsRepository::PARTS_PURCHASE_BEST_TIMING,
        HpMainPartsRepository::PARTS_ERECTIONHOUSING_ORDERHOUSE,
        HpMainPartsRepository::PARTS_FUNDING_PLAN,
        HpMainPartsRepository::PARTS_TYPES_MORTGAGE_LOANS,
        HpMainPartsRepository::PARTS_REPLACEMENTLOAN_MORTGAGELOAN,
        HpMainPartsRepository::PARTS_CONSIDERS_LAND_UTILIZATION_OWNER,
        HpMainPartsRepository::PARTS_UTILIZING_LAND,
        HpMainPartsRepository::PARTS_MEASURES_AGAINST_VACANCIES,
        HpMainPartsRepository::PARTS_HOUSE_REMODELING,
        HpMainPartsRepository::PARTS_LEASING_MANAGEMENT_MENU,
        HpMainPartsRepository::PARTS_TROUBLED_LEASING_MANAGEMENT,
        HpMainPartsRepository::PARTS_LEND,
        HpMainPartsRepository::PARTS_PURCHASE_INHERITANCE_TAX,
        HpMainPartsRepository::PARTS_SHOP_SUCCESS_BUSINESS_PLAN,
        HpMainPartsRepository::PARTS_STORE_SEARCH,
        HpMainPartsRepository::PARTS_SQUEEZE_CANDIDATE,
        HpMainPartsRepository::PARTS_UPPER_LIMIT,
        HpMainPartsRepository::PARTS_PREVIEW,
        HpMainPartsRepository::PARTS_RENTAL_INITIAL_COST,
        HpMainPartsRepository::PARTS_RENT,
        HpMainPartsRepository::PARTS_MOVING,
        HpMainPartsRepository::PARTS_UNUSED_ITEMS_AND_COARSEGARBAGE,
        HpMainPartsRepository::PARTS_COMFORTABLELIVING_RESIDENT_RULES,
    );

    /**
     * デフォルトページ名
     */
    protected $_default_filename = '';

    protected $_forceLoadParts = false;

    static public function factory($hp, $row, $parentRow = null) {
        if ($row->page_category_code == HpPageRepository::CATEGORY_ARTICLE) {
            $typeName = self::getTypeNameFromType($row->page_type_code, true);
        } else {
           $typeName = self::getTypeNameFromType($row->page_type_code);
        }
        $class_name = 'Library\Custom\Hp\Page\\' . pascalize($typeName);

        if (class_exists($class_name)) {
            return new $class_name($hp, $row, $parentRow);
        }
    	return new static($hp, $row, $parentRow);
    }

    static public function getTypeNameFromType($type, $partsType = false) {
        $table = App::make(HpPageRepositoryInterface::class);
        if (in_array($type, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_LARGE)) || in_array($type, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_TOP_ARTICLE))) {
            return 'LARGE';
        } else if (in_array($type, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_SMALL))) {
            return 'SMALL';
        } else if (in_array($type, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE)) && $partsType) {
            switch ($type) {
                case HpPageRepository::TYPE_ARTICLE_ORIGINAL:
                    return 'ORIGINAL_TEMPLATE';
                    break;
                default:
                    return 'ARTICLE_TEMPLATE';
                    break;
            }
        }
    	return str_replace('TYPE_', '', array_search($type, $table->getTypeList()));
    }

    /**
     *
     * @param App\Models\Hp $hp
     * @param App\Models\HpPage $row
     * @param App\Models\HpPage $parentRow
     * @return Library\Custom\Hp\Page
     */
    public function __construct($hp, $row, $parentRow = null) {
        $this->_hp  = $hp;
        $this->_row = $row;
        $this->_parentRow = $parentRow;
        $this->form = new Form();
        $this->removeSearchElementsAllParts();
    }

    public function getId() {
    	return $this->_row->id;
    }

    public function getType() {
    	return $this->_row->page_type_code;
    }

    public function getHpId() {
    	return $this->_row->hp_id;
    }

    public function isNew() {
    	return $this->_row->new_flg == 1;
    }

    public function forceLoad(){
        $this->_forceLoadParts = true;
    }

    public function isPublic() {
    	return $this->_row->public_flg == 1;
    }

    public function isScheduled() {
		if(App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds(array($this->getId()))) {
            // 自身が予約されている
            return true;
        }

        // 自身が下書きかつ、内部リングが公開予約はありえない？
        $linkingPageIds = [];
        $linkingPages = App::make(HpPageRepositoryInterface::class)->fetchAll(array(['delete_flg', 0], ['link_page_id', $this->getId()], ['hp_id', $this->getHpId()]));

        foreach($linkingPages as $lpage) {
            $linkingPageIds[] = $lpage->id;
        }
        // 内部リンクなし
        if(count($linkingPageIds) == 0) {
            return false;
        }

        if(App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByPageIds($linkingPageIds)) {
            // 内部リンクが予約されている
            return true;
        }
        return false;
    }

    public function isDetailPageType() {
    	return $this->_row->isDetailPageType();
    }

    public function getTitle() {
    	return $this->getTypeNameJp();
    }

    public function getParentTitle() {
    	return $this->_parentRow ? App::make(HpPageRepositoryInterface::class)->getTypeNameJp($this->_parentRow->page_type_code) : null;
    }

    public function getParentId() {
    	return $this->_parentRow ? $this->_parentRow->id : null;
    }

    /**
     * ページのデータを取得
     */
    public function getRow() {
    	return $this->_row;
    }

    public function getParentRow() {
    	return $this->_parentRow;
    }

    public function getHp() {
    	return $this->_hp;
    }

    public function getCompany(){
        if(!$this->_company){
            $this->_company = $this->getHp()->fetchCompanyRow();
        }
        return $this->_company;
    }

    public function isTopOriginal(){
        return $this->getCompany()->checkTopOriginal();
    }

    public function getOriginalSetting(){
        if(!$this->_originalSetting){
            $this->_originalSetting = $this->getCompany()->getOriginalSetting();
        }
        return $this->_originalSetting;
    }

    public function isGlobalNav(){
        if($this->isTopOriginal()){
            $globalNav = $this->getHp()->getGlobalNavigation();
            $ids = array_map(function($item){
                return $item['id'];
            },$globalNav->toArray());
            if(in_array($this->getRow()->id,$ids)){
                return true;
            }
        }
        return false;
    }

    public function notIsPageInfoDetail() {
        if(!$this->_row->getRepository()) {
            $this->_row->repository = App::make(HpPageRepositoryInterface::class);
        }
        return $this->_row->getRepository()->notIsPageInfoDetail($this->_row->page_type_code, $this->_row->page_flg);
    }

    public function getTypeInfoDetail() {
        if(!$this->_row->getRepository()) {
            $this->_row->repository = App::make(HpPageRepositoryInterface::class);
        }
        if ($this->_row->getRepository()->notIsPageInfoDetail($this->_row->page_type_code, $this->_row->page_flg)) {
            return InfoDatailType::ONLY_ADD_LIST;
        }
        return InfoDatailType::ADD_PAGE;
    }

    public function getInfoDeatailTitle($type) {
        return InfoDatailType::getInstance()->getAll()[$type];
    }

    /**
     * 編集用の情報を取得する
     */
    public function getEditInfo() {
		$id				= $this->getId()			;
		$parentId		= $this->getParentId()		;
    	return array(
			'id'				=> $id				,
			'parentId'			=> $parentId		,
    		'isDetail' => $this->isDetailPageType(),
    		'canDelete' => $this->canDelete(),
    		'isPublic' => $this->isPublic(),
			'isMemberOnly'		=> $this->_isThisMemberOnly( $id ? $id : $parentId )	,
    	);
    }

    protected function _isThisMemberOnly( $hp_page_id )
	{
		$hpPage	= App::make(HpPageRepositoryInterface::class)				;
		return $this->_isMemberOnly( $hpPage, $hp_page_id	)	;
	}

	protected function _isMemberOnly(& $hpPage, $hp_page_id, $level = 1 )
	{
		$result	= false									;
		$row	= $hpPage->fetchRowById( $hp_page_id )	;
		if ( $row == null )
		{
			return false ;
		}
		if ( $row->member_only_flg && ( $level > 1 ) )
		{
			return true ;
		}
		if ( $row->parent_page_id )
		{
			$result = $this->_isMemberOnly( $hpPage, $row->parent_page_id, ++$level ) ;
		}
		return $result ;
	}

    /**
     * ページタイプ名を取得
     * @return STRING
     */
    public function getTypeName() {
    	self::getTypeNameFromType($this->getType());
    }

    /**
     * ページタイプ名（日本語）を取得
     * @return string
     */
    public function getTypeNameJp() {

    	return App::make(HpPageRepositoryInterface::class)->getTypeNameJp($this->getType());
    }

    public function init() {
    	$this->initContents();
    	$this->initMainContents();
    	$this->initSideContents();
    }

    public function initContents() {
    	$tdk = new SectionParts\Tdk(array('hp' => $this->getHp(), 'page'=>$this->getRow()));

    	//プレースフォルダーを設定する
    	$placeholder = new HpPagePlaceholderData();
    	$data = $placeholder->get($this->_row->page_type_code);
        foreach($tdk->getElements() as $name => $element) {
    		if(isset($data[$name])) $element->setAttribute('placeholder', $data[$name]);
    	}

    	if (!$tdk->getElement('filename')->getValue()) {
    		$tdk->getElement('filename')->setValue($this->_default_filename);
    	}
        $tdk->setName('tdk');
    	$this->form->addSubForm($tdk, 'tdk');
    }

    public function initMainContents() {
    	$section = new Section();
    	$section->setTitle('メインコンテンツ');
        // 4425 Add condition check Top page
        $section->setPage($this->_row->page_type_code);
        $section->setName('main');
    	$this->form->addSubForm($section, 'main');
    }

    public function initSideContents() {
        // TOPページ
        if ($this->_row->page_type_code == HpPageRepository::TYPE_TOP) {
            $section = new SideCommon();
            $section->setTitle('サイドコンテンツ（全ページ共通）');
            // 編集可能なサイドレイアウトを設定
            $section->sideLayout = $this->getHp()->getEditableSideLayout();
        }
        // TOPページ以外
        else {
            $section = new Section();
            $section->setTitle('カスタマイズサイドコンテンツ');
        }
        $section->setName('side');
    	$this->form->addSubForm($section, 'side');
    }

    protected function _getRequest() {
    	return app('request');
    }

    protected function _isPreview() {
        return getActionName() == 'previewPage';
    }

    public function load($load_from_request = false) {
        if($this->_forceLoadParts){
            $this->_load();
            return;
        }

    	if ($this->_isPreview() && $load_from_request && !$this->_getRequest()->load) {
    		if (!$this->isValid( $this->_getRequest()->all() )) {
    			throw new \Exception('invalid preview data');
    		} else {
                if ($this->_getRequest()->company_id) {
                    $this->_load();
                }
            }
    	}
    	else if ($this->isNew()) {
    		$this->_setPreset();
    	}
    	else {
    		$this->_load();
    	}
    }

    protected function _setPreset() {
    	foreach ($this->_presetMainParts as $areaNo => $cols) {
    		$area = $this->addArea($areaNo);
    		$area->setColumnCount(count($cols));

    		$partsNo = 0;
    		foreach ($cols as $colNo => $partsTypes) {
    			foreach ($partsTypes as $type) {
    				$parts = $this->createMainParts($type);
    				$parts->setColumn($colNo + 1);
    				$parts->setPreset();

    				$this->_decoratePresetParts($parts, $type);
                    $parts->setName($partsNo);
    				$area->addParts($parts, $partsNo++);
    			}
    		}
    	}
    }

    /**
     *
     * @param  $parts
     * @param int $type
     */
    protected function _decoratePresetParts($parts, $type) {

    }

    protected function _load() {

        $data = $this->_loadParts();
    }

    protected function _loadParts() {
        $where = array(['page_id', $this->getId()], ['hp_id', $this->getHpId()], ['delete_flg', 0]);
        $order = array('sort');

        $parts = array();
        $areas            = App::make(HpAreaRepositoryInterface::class)->fetchAll($where, $order)->toArray();
        $parts['main']['parts']    = App::make(HpMainPartsRepositoryInterface::class)->fetchAll($where, $order)->toArray();
        $parts['main']['elements'] = App::make(HpMainElementRepositoryInterface::class)->fetchAll($where, $order)->toArray();
        $parts['main']['sub-elements'] = App::make(HpMainElementElementRepositoryInterface::class)->fetchAll($where, $order)->toArray();
        $parts['side']['parts']    =App::make(HpSidePartsRepositoryInterface::class)->fetchAll($where, $order)->toArray();
        $parts['side']['elements'] = App::make(HpSideElementsRepositoryInterface::class)->fetchAll($where, $order)->toArray();

        // パーツにエレメントを紐付け
        foreach ($parts as $section => $sectionData) {
            $subElementsByElementId = array();
            if (isset($sectionData['sub-elements'])) {
                foreach ($sectionData['sub-elements'] as $subElelementData) {
                    $subElementsByElementId[ $subElelementData['parts_id'] ][] = $subElelementData;
                }

                unset($sectionData['sub-elements']);
            }


            $elementsByPartsId = array();
            foreach ($sectionData['elements'] as $elementData) {
                if (isset($subElementsByElementId[ $elementData['id'] ])) {
                    $elementData['elements'] = $subElementsByElementId[ $elementData['id'] ];
                }

                $elementsByPartsId[ $elementData['parts_id'] ][] = $elementData;
            }
            unset($subElementsByElementId);
            unset($parts[$section]['elements']);

            foreach ($sectionData['parts'] as $key => $partsData) {
                if (isset($elementsByPartsId[ $partsData['id'] ])) {
                    $parts[$section]['parts'][$key]['elements'] = $elementsByPartsId[ $partsData['id'] ];
                }
            }
        }

        // エリアにメインパーツを紐付け
        $mainPartsByAreaId = array();
        foreach ($parts['main']['parts'] as $mainPartsData) {
            $mainPartsByAreaId[ $mainPartsData['area_id'] ][] = $mainPartsData;
        }
        unset($parts['main']);

        $data = array();
        foreach ($areas as $areaData) {
            if (!isset($mainPartsByAreaId[ $areaData['id'] ])) {
                // パーツのないエリアはスキップ
                continue;
            }

            $areaData['parts'] = $mainPartsByAreaId[ $areaData['id'] ];

            $data['main'][] = $areaData;
        }
        unset($mainPartsByAreaId);

        // サイドパーツ
        $data['side'] = $parts['side']['parts'];

        $this->prepare($data);
        // $this->form->setDefaults($data);
        $this->form->setData($data);
    }

    public function prepare($data) {
    	foreach ($this->form->getSubForms() as $name => $form) {
    		if ($form instanceof Section) {
    			if (isset($data[$name]) && is_array($data[$name])) {

    				if ($name == 'main') {
    					foreach ($data[$name] as $areaNo => $areaData) {

    						if (!is_numeric($areaNo)) {
    							throw new \Exception('invalid format');
    						}

    						if (!isset($areaData['parts']) || !is_array($areaData['parts']) || empty($areaData['parts'])) {
    							// 空のエリアはスキップ
    							continue;
    						}

    						// エリア作成
    						$area = $this->addArea($areaNo);
    						// エリアカラム数設定
    						if (isset($areaData['column_type_code'])) {
    							$area->setColumnCount((int)$areaData['column_type_code']);
    						}


    						if (!isset($areaData['parts'])) {
    							continue;
    						}

    						// パーツ作成
    						foreach ($areaData['parts'] as $partsNo => $partsData) {

    							if (!isset($partsData['parts_type_code'])) {
    								throw new \Exception('invalid parts format');
    							}

    							$parts = $this->createParts($name, $partsData['parts_type_code']);
    							// エリアに追加
                                $parts->setName($partsNo);
    							$area->addParts($parts, $partsNo);

    							// 作成済みメインパーツに追加
    							$this->_hasMainParts[] = (int) $partsData['parts_type_code'];

    							$this->_prepareParts($parts, $partsData);
    						}
    					}
    				}
    				else if ($name == 'side') {

                        // パーツ作成
    					foreach ($data[$name] as $partNo => $partsData) {
    						if (!isset($partsData['parts_type_code'])) {
                                continue;
    						}

    						$parts = $this->createParts($name, $partsData['parts_type_code']);
                            $parts->setName($partNo);
    						$this->form->getSubForm('side')->addSubForm($parts, $partNo);
    						$this->_prepareParts($parts, $partsData);
    					}

    				}
    			}
    		}
        }
    }

    protected function _prepareParts($parts, $data) {
    	if ($parts->hasElement() && isset($data['elements']) && is_array($data['elements'])) {
    		foreach ($data['elements'] as $elementNo => $elementData) {
    			if (!isset($elementData['type'])) {
    				throw new \Exception('invalid parts element format');
    			}
    			$element = $parts->createPartsElement($elementData['type'], $elementNo);
    			if ($element && $element instanceof \Library\Custom\Hp\Page\Parts\AbstractParts\SubParts) {
    				$this->_prepareParts($element, $elementData);
    			}
    		}
    	}
    }

    public function isValid($data) {

    	$this->prepare($data);

    	if ($this->_requiredMainParts) {
    		foreach ($this->_requiredMainParts as $partsType) {
    			if (!in_array($partsType, $this->_hasMainParts, true)) {
    				throw new \Exception('required parts not found');
    			}
    		}
    	}

    	$isValid = true;
    	$subForms = $this->form->getSubForms();
        foreach ($subForms as $name => $form) {
            $form->setData($data);
    		$isValid = $form->isValid(isset($data[$name])?$data[$name]:array(), false) && $isValid;

            // サイド共通パーツの場合はレイアウトをセット
            if (get_class($form) == 'Library\Custom\Hp\Page\Layout\SideCommon') {
                $form->setValidSideLayout(isset($data['sidelayout'])?$data['sidelayout']:[]);
            }
        }
    	return $isValid;
    }

    public function getMessagesById() {
    	return $this->form->setBelongToRecursive()->getMessagesById();
    }

    public function getMainPartsTypeList() {
    	return array_merge($this->_commonMainParts, $this->_mainParts);
    }

    public function getSidePartsTypeList() {
    	return array_merge($this->_commonSideParts, $this->_sideParts);
    }

    /**
     *
     * @param string $sectionName
     * @param int $areaNo
     */
    public function addArea($areaNo) {
    	$area = new Area();
        $area->getElement('sort')->setValue($areaNo);
        $area->setName($areaNo);
    	$this->form->getSubForm('main')->addSubForm($area, $areaNo);
    	return $area;
    }

    public function createParts($section, $type) {
    	switch($section) {
    		case 'main':
    			return $this->createMainParts($type);
    		case 'side':
    			return $this->createSideParts($type);
    		default:
    			throw new \Exception('invalid section');
    	}
    }

    public function createMainParts($type) {
    	if (!in_array((int) $type, $this->getMainPartsTypeList(), true)) {
    		throw new \Exception('invalid parts type');
    	}

    	return $this->_createMainParts($type);
    }

    public function getCreateableMainParts() {
    	$parts = array();
    	foreach ($this->getMainPartsTypeList() as $type) {
            $parts[] = $this->_createMainParts($type)->forTemplate();
        }

    	return $parts;
    }

    protected function _createMainParts($type) {
        $class_name = App::make(HpMainPartsRepositoryInterface::class)->getClass($type);
        $instance = new $class_name(array('hp' => $this->getHp(), 'page'=>$this->getRow(), 'isRequired' => in_array((int)$type, $this->_requiredMainParts, true)));
        $instance->setType($type);

    	return $instance;
    }

    public function createSideParts($type) {
    	if (!in_array((int) $type, $this->getSidePartsTypeList(), true)) {
    		throw new \Exception('invalid parts type');
    	}

    	return $this->_createSideParts($type);
    }
    public function getCreateableSideParts() {
    	$parts = array();
    	foreach ($this->getSidePartsTypeList() as $type) {
    		$parts[] = $this->_createSideParts($type)->forTemplate();
    	}

    	return $parts;
    }

    protected function _createSideParts($type) {
    	$class_name = App::make(HpSidePartsRepositoryInterface::class)->getClass($type);
    	$instance = new $class_name(array('hp' => $this->getHp(), 'page'=>$this->getRow()));
    	$instance->setType($type);

    	return $instance;
    }

    /**
     * 保存
     */
    public function save() {

    	DB::beginTransaction();

    	// 代行ログタイプ：作成、更新
    	if ($this->isNew()) {
    		$logEditType = LogEditType::PAGE_CREATE;
    	}
    	else {
    		$logEditType = LogEditType::PAGE_UPDATE;
    	}

    	if ($id = $this->getId()) {
    		// 未作成フラグOFF
    		// 手動更新更新日時更新
    		$pageTable = App::make(HpPageRepositoryInterface::class);
            if($pageTable->isContactForSearch($this->getType())){//物件お問い合わせの場合filenameを初期値で固定
                $filename = $pageTable->getPageNameJp($this->getType());
                $pageTable->update(array(['id', $id], ['hp_id', $this->getHpId()]), array('filename'=>$filename,'new_flg'=>0,'diff_flg'=>1,'updated_at'=>date('Y-m-d H:i:s')));
            }else{
    		    $pageTable->update(array(['id', $id], ['hp_id', $this->getHpId()]),array('new_flg'=>0,'diff_flg'=>1,'updated_at'=>date('Y-m-d H:i:s')));
            }
    	}
    	else {
    		$this->_row->new_flg = 0;
            $this->_row->diff_flg = 1;
    		$this->_row->updated_at = date('Y-m-d H:i:s');
            $this->_row->create_date = date('Y-m-d H:i:s');
    		if ($this->_row->isDetailPageType() && !$this->_row->isMultiPageType()) {
    			$table = App::make(HpPageRepositoryInterface::class);
    			$sort = $table->countRows(array(['parent_page_id', $this->_row->parent_page_id], ['delete_flg', 0]));
    			$this->_row->sort = $sort + 1;
    		}

    		$this->_row->save();

    		$this->_row->link_id = $this->_row->id;
    		$this->_row->save();
    	}

    	// CMS操作ログ
        CmsOperation::getInstance()->cmsLogPage($logEditType, $this->getId());

        //サイドパースの件数を取っておく
        $where = array(['page_id', $this->getId()], ['hp_id', $this->getHpId()]);
        $parts = App::make(HpSidePartsRepositoryInterface::class)->fetchAll($where, array('sort'))->toArray();

        foreach($parts as &$rec) {
            $elms = App::make(HpSideElementsRepositoryInterface::class)->fetchAll(array(['page_id', $this->getId()], ['parts_id', $rec['id']]), array('sort'))->toArray();
            if(!empty($elms)) {
                $rec['elements'] = $elms;
            }
        }

        // TOPページのlayoutを取得する
        $side_layout = null;
        if($this->_row->page_type_code == HpPageRepository::TYPE_TOP) {
            // 必ず1件のはず
            $hp_row = App::make(HpRepositoryInterface::class)->find($this->getHpId())->toArray();
            $side_layout = $hp_row['side_layout'];
        }

        $hp = $this->getHp();
        $page = $this->getRow();

        if($this->isTopOriginal()){
            $this->beforeSaveTop();
        }
        else $this->beforeSave();

        // 詳細ページ更新 -> 一覧ページの差分フラグon
        if ($page && $page->isDetailPageType()) {
            $pageTable = App::make(HpPageRepositoryInterface::class);
            $pageTable->update(array(['id', $page->parent_page_id], ['hp_id', $this->getHpId()]), array('diff_flg'=>1,'updated_at'=>date('Y-m-d H:i:s')));
        }

        // お知らせ一覧 or 詳細 -> トップページの差分フラグON
        if ($page && ($page->page_type_code == HpPageRepository::TYPE_INFO_INDEX || $page->page_type_code == HpPageRepository::TYPE_INFO_DETAIL)) {
            $pageTable = App::make(HpPageRepositoryInterface::class);
            $pageTable->update(array(['hp_id', $this->getHpId()], ['page_type_code', HpPageRepository::TYPE_TOP]), array('diff_flg'=>1,'updated_at'=>date('Y-m-d H:i:s')));
        }

        $forms = $this->form->getSubForms();
        $side_cnt = 0;
        foreach ($forms as $name => $form) {
        	$form->save($hp, $page);
            if($name == "side" && $form->getSubForms()) $side_cnt++;
        }

        //TOPページでかつ、サイドコンテンツが変更されている場合は全上げする
        if($this->_row->page_type_code == HpPageRepository::TYPE_TOP) {
            $oldParts = $parts;
            $newParts = App::make(HpSidePartsRepositoryInterface::class)->fetchAll($where, array('sort'))->toArray();
            foreach($newParts as &$rec) {
                $elms = App::make(HpSideElementsRepositoryInterface::class)->fetchAll(array(['page_id', $this->getId()], ['parts_id', $rec['id']]), array('sort'))->toArray();
                if(!empty($elms)) {
                    $rec['elements'] = $elms;
                }
            }

            $oldSideLayout = $side_layout;
            $hp_row = App::make(HpRepositoryInterface::class)->fetchAll(array(['id', $this->getHpId()]))->toArray();
            $newSideLayout = $hp_row[0]['side_layout'];

            $side_modified = false;
            if($oldSideLayout != $newSideLayout) {
                // サイドコンテンツのレイアウト変更 or その他のサイドリンク一覧文言変更
                $side_modified = true;
            } else if(count($oldParts) != count($newParts)) {
                // カスタマイズサイドコンテンツの数変更
                $side_modified = true;
            } else {
                // カスタマイズサイドコンテンツの数が同じ場合内容比較
                foreach($oldParts as &$part) {
                    // ID, 作成日等は異なるためNULLに変更
                    $part['id'] = null;
                    $part['copied_id'] = null;
                    $part['create_id'] = null;
                    $part['create_date'] = null;
                    $part['update_id'] = null;
                    $part['update_date'] = null;
					if(isset($part['elements'])) {
                        foreach($part['elements'] as &$elm) {
                            $elm['id'] = null;
                            $elm['parts_id'] = null;
                            $elm['create_id'] = null;
                            $elm['create_date'] = null;
                            $elm['update_id'] = null;
                            $elm['update_date'] = null;
                        }
                    }
                }
                foreach($newParts as &$part) {
                    // ID, 作成日等は異なるためNULLに変更
                    $part['id'] = null;
                    $part['copied_id'] = null;
                    $part['create_id'] = null;
                    $part['create_date'] = null;
                    $part['update_id'] = null;
                    $part['update_date'] = null;
					if(isset($part['elements'])) {
                        foreach($part['elements'] as &$elm) {
                            $elm['id'] = null;
                            $elm['parts_id'] = null;
                            $elm['create_id'] = null;
                            $elm['create_date'] = null;
                            $elm['update_id'] = null;
                            $elm['update_date'] = null;
                        }
                    }
                }
                if($oldParts !== $newParts) {
                    $side_modified = true;
                }
            }

            if($side_modified) {
                // 予約がある場合は、更新不可
                if(getInstanceUser('cms')->getCurrentHp()->hasReserve()){
                    throw new TopSaveException('公開予約中のページがあるため更新できませんでした');
                }
                $hp->all_upload_flg = 1;
                $hp->setAllUploadParts('topside', 1);

                $hp->save();
            }
        }

        $this->afterSave();

        DB::commit();
    }

    /**
     * 保存前の処理
     */
    protected function beforeSave() {
    	$this->deleteAllParts();
    }

    protected function beforeSaveTop() {
        //no delete
        $this->deleteAllPartsForTop();
    }

    /**
     * 保存の後処理
     */
    protected function afterSave() {
    	$this->updateUsedImage();
    	$this->updateUsedFile2();
    }

    public function updateUsedFile2()
    {
    	// 使用ファイル２保存
    	$file2Ids	= array()						;
    	$forms		= $this->form->getSubForms()	;
        foreach ($forms as $name => $form) {
    		$ids = $form->getUsedFile2s() ;
    		if ( $ids ) {
    			$file2Ids = array_merge( $file2Ids, $ids ) ;
    		}
    	}

    	if ( $file2Ids )
    	{
    		$file2Ids = array_unique( $file2Ids ) ;
    	}

    	App::make(HpFile2UsedRepositoryInterface::class)->saveHpPageFile2s( $this->getHpId(), $this->getId(), $file2Ids, false ) ;
    }

    public function updateUsedImage() {
    	// 使用画像保存
    	$imageIds = array();
    	$forms = $this->form->getSubForms();
        foreach ($forms as $name => $form) {
    		$ids = $form->getUsedImages();
    		if ($ids) {
    			$imageIds = array_merge($imageIds, $ids);
    		}
    	}

    	if ($imageIds) {
    		$imageIds = array_unique($imageIds);
    	}

        App::make(HpImageUsedRepositoryInterface::class)->saveHpPageImages($this->getHpId(), $this->getId(), $imageIds, false);
    }

    /**
     * ページの削除可能かチェック
     * @return boolean
     */
    public function canDelete() {
        $row = $this->getRow();

        if($this->isTopOriginal()){
            if($row->page_type_code == HpPageRepository::TYPE_INFO_INDEX){
                return false;
            }

            //CMS cannot delete page if its global nav
            if(!UserAbstract::getInstance()->isAgency()){
                if($this->isGlobalNav()){
                    return false;
                }
            }
        }

        // 必須ページの場合削除不可
    	if ($row->isRequiredType()) {
    		if ($row->isUniqueType()) {
    			return false;
    		}

    		// 一意でない必須ページは最後の1ページは削除不可
    		$where = array(['hp_id', $this->getHpId()], ['page_type_code', $row->page_type_code]);
    		if (App::make(HpPageRepositoryInterface::class)->countRows($where) <= 1) {
    			return false;
    		}
    	}

		// 物件検索お問い合わせは削除不可
		if ($row->isContactForSearch()) {
			return false;
		}

    	$id = $this->getId();
        if ($id && $this->isPublic()) {
            return false;
        }

        // 一覧の場合、公開中の子がないかチェック
        if ($id && $row->hasDetailPageType() && $row->hasPublicChild()) {
        	return false;
        }



        //#4274 Change spec form FDP contact
        // if($row->page_type_code == HpPageRepository::TYPE_FORM_FDP_CONTACT){
        //     return false;
        // }

        return true;
    }

    /**
     * ページの削除可能かチェック(物件リクエスト)
     */
    public function canDeleteEstateRequest()
    {
        $row = $this->getRow();
        // 物件検索設定で物件リクエストの「利用する」にチェックがある場合は削除不可
        if ($row->cannotEstateRequest($this->getHp(), $row->page_type_code)) {
            return false;
        }
        return true;
    }

    public function canDeleteCategoryArticlePage($pages) {
        $id = $this->getId();
        if (!$this->checkExistChild($pages, $id)) {
            return false;
        }
        return true;

    }

    public function canDeleteArticlePage($pages) {
        $row = $this->getRow();
        $pages = array_filter($pages, function($page) use ($row) {
            return $page['parent_page_id'] == $row->parent_page_id;
        });
        if (count($pages) > 1) {
            return true;
        }

        return false;
    }

    public function checkExistChild($pages, $id) {
        foreach($pages as $key=>$page) {
            if ($page['parent_page_id'] == $id) {
                return false;
            }
        }

        return true;

    }

    public function getThisPageAndChildId($pages) {
        foreach($pages as $key=>$page) {
            if ($page['parent_page_id'] == $parentId) {
                $result[] = $page;
            }
        }
    }

    /**
     * ページの削除
     */
    public function deletePage() {

    	if (!$id = $this->getId()) {
    		return;
    	}

        $table = App::make(HpPageRepositoryInterface::class);
//         	$adapter = $table->getAdapter();
//         	$adapter->beginTransaction();

        // 公開予約削除対象page_id一覧
        $scPageIds = [];
        $scPageIds[] =$id;  // 削除ページ自身のid
        // 内部リンク
        $linkingPages = $table->fetchAll(array(['delete_flg' , 0], ['link_page_id' , $this->getRow()->link_id], ['hp_id' , $this->getHpId()]));
        foreach($linkingPages as $lpage) {
            $scPageIds[] = $lpage->id;
        }

        App::make(HpPageRepositoryInterface::class)->delete(array(['id' , $id]));
        App::make(HpPageRepositoryInterface::class)->delete(array(['link_page_id' , $this->getRow()->link_id], ['hp_id' , $this->getHpId()]));
        
        $this->deleteAllParts();

        $isTopOriginal = $this->isTopOriginal();
        $pagetype = $this->getRow()->page_type_code == HpPageRepository::TYPE_INFO_DETAIL;

        if ($isTopOriginal && $pagetype) {
            $assocHpPage = App::make(AssociatedHpPageAttributeRepositoryInterface::class);
            $row = $assocHpPage->fetchRowById($id);
            if ($row) {
                $assocHpPage->update(['delete_flg' => 1], array(['hp_page_id' , $id]));
            }
        }

        // #4139 お知らせ（一覧のみ追加）
        if ($table->notIsPageInfoDetail($this->getRow()->page_type_code, $this->getRow()->page_flg)) {
            App::make(HpInfoDetailLinkRepositoryInterface::class)->update(['delete_flg' => 1], array(['page_id',$id]));
        }

        // -- 子階層を階層外へ移動処理(サイトマップ：メニューから削除と同じ)
        // 詳細の場合、兄弟がいない場合は親削除
        $row = $this->getRow();
        $row->setTable($table);
        if ($table->isDetailPageType($row->page_type_code) && $row->parent_page_id) {
        	$siblings = $table->fetchAll(array(['id',$row->id], ['parent_page_id',$row->parent_page_id], ['hp_id',$row->hp_id]));
        	if (!count($siblings)) {
        	    // don't delete TYPE_INFO_DETAIL if is top original
        	    if(!($isTopOriginal && $row->page_type_code == HpPageRepository::TYPE_INFO_DETAIL)) {
                    if ($parentRow = $table->fetchRow(array(['id',$row->parent_page_id], ['hp_id',$row->hp_id]))) {
					    $parentRow->delete_flg = 1;
					    $parentRow->save();
                        App::make(HpPageRepositoryInterface::class)->delete(array(['link_page_id' , $parentRow->link_id], ['hp_id' , $this->getHpId()]));

                        // 削除する親も公開予約削除対象
                        $scPageIds[] = $parentRow->id;
                    }
                }
        	}
        }
        if (!$this->isArticlePage()) {
            $children = $row->fetchAllChild();
            foreach ($children as $child) {
                $child->removePageFromMenuRecursive();
            }
            // -- 子階層を階層外へ移動処理(サイトマップ：メニューから削除と同じ)
        }

        // 公開予約の削除
        if(count($scPageIds)) {
            App::make(ReleaseScheduleRepositoryInterface::class)->delete(array(['delete_flg' , 0], 'whereIn' => ['page_id', $scPageIds]), true);
        }

        //CMS操作ログ
        CmsOperation::getInstance()->cmsLogPage(LogEditType::PAGE_DELETE, $this->getId());

//             $adapter->commit();
    }

    public function deleteAllParts() {
    	$where = array(['hp_id', $this->getHp()->id], ['page_id', $this->getId()]);
    	App::make(HpAreaRepositoryInterface::class)->delete($where, true);
    	App::make(HpMainPartsRepositoryInterface::class)->delete($where, true);
    	App::make(HpMainElementRepositoryInterface::class)->delete($where, true);
    	App::make(HpMainElementElementRepositoryInterface::class)->delete($where, true);
    	App::make(HpSidePartsRepositoryInterface::class)->delete($where, true);
    	App::make(HpSideElementsRepositoryInterface::class)->delete($where, true);
    }

    public function deleteAllPartsForTop() {
        /** @var App\Models\Hp $topPage */
        $hpId = $this->getHp()->id;
        $topPage = App::make(HpPageRepositoryInterface::class)->getTopPageData($hpId);
        $where = array(['hp_id', $hpId], ['page_id', $this->getId()]);
        $notDeleteMainPart = App::make(HpMainPartsRepositoryInterface::class)->fetchAll(array_merge($where,array(
            'whereIn' => [
                'parts_type_code', Original::$NOT_DELETE_PARTS
            ],
            ['page_id', $topPage->id],
        )));


        $areaWhere = $where;
        if($notDeleteMainPart){
            $areaIds = array_map(function($part){
                return $part['area_id'];
            },$notDeleteMainPart->toArray());
            if(!empty($areaIds)){
                $areaWhere = array_merge($where,array(
                    'whereNotIn' => [
                        'id', $areaIds
                    ]
                ));
            }
        }

        App::make(HpAreaRepositoryInterface::class)->delete($areaWhere, true);

        App::make(HpMainPartsRepositoryInterface::class)->delete(array_merge($where,array(
            'whereNotIn' => [
                'parts_type_code', Original::$NOT_DELETE_PARTS
            ])), true);
        App::make(HpMainElementRepositoryInterface::class)->delete($where, true);
        App::make(HpMainElementElementRepositoryInterface::class)->delete($where, true);
        App::make(HpSidePartsRepositoryInterface::class)->delete($where, true);
        App::make(HpSideElementsRepositoryInterface::class)->delete($where, true);
    }


    /**
     * @param Library\Custom\Form $form
     */
    public function setFiltersForPublish($form = null)
    {
        if (is_null($form)){
            $form = $this->form;
        }

        foreach ($form->getElements() as $element) {
            if ($element instanceof Textarea) {
                $element->setValue(nl2br($element->getValue()));
                // $element->addFilter(new Custom_Filter_Nl2br());
            }
        }

        foreach ($form->getSubForms() as $sub) {
            $this->setFiltersForPublish($sub);
        }
    }

    public function removeSearchElementsAllParts()
    {
        $company    = App::make(CompanyRepositoryInterface::class);
        $companyRow = $company->fetchRowByHpId($this->_hp->id);
        if ($companyRow['cms_plan'] <= config('constants.cms_plan.CMS_PLAN_LITE')) {
            if (($key = array_search(HpSidePartsRepository::PARTS_FREEWORD, $this->_commonSideParts)) !== false) {
                unset($this->_commonSideParts[$key]);
            }
            if (($key = array_search(HpMainPartsRepository::PARTS_ESTATE_KOMA, $this->_commonMainParts)) !== false) {
                unset($this->_commonMainParts[$key]);
            }
            if (($key = array_search(HpMainPartsRepository::PARTS_FREEWORD, $this->_commonMainParts)) !== false) {
                unset($this->_commonMainParts[$key]);
            }
        }
    }

    public function isArticlePage() {
        return $this->_row->isArticlePage();
    }

}