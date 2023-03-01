<?php

namespace Library\Custom\Model\Lists;

use Illuminate\Support\Facades\App;
use App\Repositories\OriginalSetting\OriginalSettingRepositoryInterface;
use DateTime;
use DateTimeZone;
use Exception;
use Library\Custom\DirectoryIterator;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Models\HpArea;
use App\Models\HpMainPart;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use Library\Custom\Registry;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Library\Custom\Hp\Page\Parts\EstateKoma;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use Library\Custom\Model\Estate;

class Original extends ListAbstract
{
    protected $companyRepository;
    public static $CATEGORY_COLUMN;
    public static $ORIGINAL_URLS;
    public static $ORIGINAL_TITLES;
    const NEWS_INDEX_ID = 'attr_1';
    const KOMA_COLUMN = 30;
    const KOMA_ROW = 10;

    public function __construct() {
        self::$CATEGORY_COLUMN = array(
            // 'parent_page_id'    => self::NEWS_INDEX_ID,
            'parent_page_id' => config('constants.original.NEWS_INDEX_ID'),
            'title' => 'attr_2',
            'class' => 'attr_3'
        );
        self::$ORIGINAL_URLS = [
            config('constants.original.ORIGINAL_EDIT_CMS') => '/admin/company/original-edit?company_id=%s',
            config('constants.original.ORIGINAL_EDIT_NAVIGATION') => '/admin/company/navigation-tag-list?company_id=%s',
            config('constants.original.ORIGINAL_EDIT_SPECIAL') => '/admin/company/top-housing-block?company_id=%s',
            config('constants.original.ORIGINAL_EDIT_NOTIFICATION') => '/admin/company/top-notification?company_id=%s',
            config('constants.original.ORIGINAL_EDIT_FILE') => '/admin/company/top-list-file-edit?company_id=%s',
            config('constants.original.ORIGINAL_EDIT_AGENCY') => '/creator',
        ];
        
        self::$ORIGINAL_TITLES = [
            config('constants.original.ORIGINAL_EDIT_NAVIGATION') => 'グロナビの設定 / オリジナルタグ編集・一覧',
            config('constants.original.ORIGINAL_EDIT_SPECIAL') => '物件特集コマ編集',
            config('constants.original.ORIGINAL_EDIT_NOTIFICATION') => 'お知らせ設定',
            config('constants.original.ORIGINAL_EDIT_FILE') => '編集ファイル一覧',
            config('constants.original.ORIGINAL_EDIT_AGENCY') => '制作代行'
        ];
    }

    static public function getOriginalSettingTitle()
    {
        return 'TOPオリジナル設定';
    }

    static public function getOriginalEditTitle()
    {
        return 'TOPオリジナル編集';
    }

    static public function getEffectMeasurementTitle()
    {
        return "効果測定タグ設定";
    }

    static public function getOriginalEditSubTitle() {
        return 'TOPオリジナルの各種設定画面に遷移メニュー';
    }

    /**
     * Check Plan Can Use Top Original
     * @param string $plan
     * @return bool
     */
    public static function checkPlanCanUseTopOriginal($plan)
    {
        if ($plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
            return true;
        }

        return false;
    }

    /**
     * ATHOME_HP_DEV-3826
     * Cancel TOP Setting
     * Describe: If you used Advance (reserve_cms_plan) and use plan #Advance => cancel, update original_setting
     * Fix 2019-04-12 check reserve_cms_plan != 0
     * @param App\Models\OriginalSetting $row
     * @throws Exception
     */
    public static function _setAutoCancel( $row)
    {

        $originalSetting = $row->originalSetting()->first();
        if ($originalSetting != null) {
            if (
                ($row->cms_plan            ==  config('constants.cms_plan.CMS_PLAN_ADVANCE')) &&
                ($row->reserve_cms_plan    <=  config('constants.cms_plan.CMS_PLAN_STANDARD')) &&
                ($row->reserve_cms_plan    >   config('constants.cms_plan.CMS_PLAN_NONE')) &&
                ($originalSetting->start_date          !=  null) &&
                ($originalSetting->end_date            ==  null)
            ) {
                $before_reserve_start_date                        = strftime('%Y-%m-%d', strtotime('-1 day', strtotime($row->reserve_start_date)));
                $originalSetting->applied_end_date                = $row->reserve_applied_start_date;
                $originalSetting->end_date                        = $before_reserve_start_date;
                $originalSetting->cancel_staff_id                = $row->reserve_contract_staff_id;
                $originalSetting->cancel_staff_name                = $row->reserve_contract_staff_name;
                $originalSetting->cancel_staff_department        = $row->reserve_contract_staff_department;
            }
            if (
                ($row->cms_plan                    !=  config('constants.cms_plan.CMS_PLAN_NONE')) &&
                ($originalSetting->start_date        !=  null) &&
                ($originalSetting->end_date        ==  null)
            ) {
                $originalSetting->applied_end_date                = $row->applied_end_date;
                $originalSetting->end_date                        = $row->end_date;
                $originalSetting->cancel_staff_id                = $row->cancel_staff_id;
                $originalSetting->cancel_staff_name                = $row->cancel_staff_name;
                $originalSetting->cancel_staff_department        = $row->cancel_staff_department;
            }
            $originalSetting->save();
        }
    }

    // const NEWS_INDEX_ID = 'attr_1';
    

    public static $EXTEND_INFO_LIST = array(
        'page_id'               => 'attr_4',
        'cms_disable'           => 'attr_5',
        'notification_type'     => 'attr_6'
    );

    static public function getOriginalSettingSubTitle() {
        return config('constants.original.CONTRACT_TITLE');
    }

    public static function checkDate($date){
        $timezone = new DateTimeZone('Asia/Tokyo');
        $start  = new DateTime($date, $timezone);
        $today  = new DateTime(date("Y-m-d"), $timezone);
        $today->format('Y-m-d');
        if($start->getTimestamp() == $today->getTimestamp()){
            return config('constants.original.CURRENT_DATE');
        }
        if($start->getTimestamp() > $today->getTimestamp()){
            return config('constants.original.FUTURE_DATE');
        }
        return config('constants.original.PAST_DATE');
    }

    /**
     * TopOriginalEvent
     * @param App\Models\Company $row
     * @param bool $topTo
     * @param bool $topBefore
     * @param bool $downPlan
     * @throws Exception
     */
    public static function callTopOriginalEvent($row, $topTo = false, $topBefore = false, $downPlan = false){
        // set company event
        self::setCompanyTopEvent($row);

        // get current top or not
        $originalSetting= App::make(OriginalSettingRepositoryInterface::class)->getDataForCompanyId($row->id);

        // if down plan, reset original_setting, reset hp cms + agency
        if($downPlan){
            $originalSetting->all_update_top = 0;
            $originalSetting->save();
            // if advance previous use top original
            if($topBefore == true){
                self::resetAssociatedCompanyHp($row->id);
            }
        }
        else {
            //update setting
            $isRunBatch = (int)$topTo;
            if($originalSetting->all_update_top != $isRunBatch){
                $originalSetting->all_update_top = $isRunBatch ;
                $originalSetting->save();
            }

            // normal behavior: if current is top and gonna down top
            if($topBefore && $topTo == false){
                self::resetAssociatedCompanyHp($row->id);
            }
        }

        //update current hp
        $hp = $row->getCurrentHp();
        $row->topOriginalEvent($hp,$topTo,$topBefore);

        // update creator hp
        /** @var App\Models\Hp $hpCreator */
        $hpCreator = $row->getCurrentCreatorHp();
        $row->topOriginalEvent($hpCreator,$topTo,$topBefore);

        //update backup hp
        /** @var App\Models\Hp $hpBackup */
        $hpBackup = $row->getBackupHp();
        $row->topOriginalEvent($hpBackup,$topTo,$topBefore);


        #2018/01/09 remove design if not top
        if($topTo == false){
            // delete design
            self::removeDesignDir($row->id);
        }
    }

    /**
     * Ticket 3680
     * @param integer $companyId
     */
    public static function resetAssociatedCompanyHp($companyId){
        $table = App::make(AssociatedCompanyHpRepositoryInterface::class);
        $where = [['company_id', $companyId]];
        $table->update($where,['space_hp_id' => null, 'backup_hp_id' => null, 'current_hp_id'=> null]);
    }

    public static function setCompanyTopEvent($company){
        if(!get_class($company ) == "App\Models\Company"){
            throw new Exception('Not Company Row');
            return;
        }
        Registry::set(config('constants.original.companyTopEvent'),$company);
    }

    public static function getCompanyTopEvent(){
        return Registry::get(config('constants.original.companyTopEvent'));
    }

    public static function hasCompanyTopEvent(){
        if(Registry::isRegistered(config('constants.original.companyTopEvent'))){
            return true;
        }
        return false;
    }

    public static function removeDesignDir($companyId){
        if ('' == $companyId && !is_numeric($companyId)) {
            throw new Exception( "No Company Data. " );
            exit;
        }
    
        $di = new DirectoryIterator();
        $di->removeDir(self::getOriginalImportPath($companyId));
    }

    /**
     * Get absolute path to import html file
     * @param int $company_id
     * @param string $folder_name
     * @return string
     */
    static public function getOriginalImportPath($company_id = '', $folder_name=''){
        $path = sprintf(
            's3://'.env('AWS_BUCKET').'/'. config('constants.original.ORIGINAL_IMPORT_TOPROOT') . '%s%s',
            '' !== $company_id ? '/' . $company_id : $company_id,
            '' !== $folder_name ? '/' . $folder_name : $folder_name
        );
        return $path;
    }

    public static $PAGE_TITLE = array(
        HpPageRepository::TYPE_INFO_INDEX   => 'お知らせ%s一覧',
        HpPageRepository::TYPE_INFO_DETAIL  => 'お知らせ%s',
    );

    public static function getInfoPageName($type = 1){
        $data = self::$PAGE_TITLE;
        foreach($data as $k=>$v){
            $data[$k] = sprintf($v,$type);
        }
        return $data;
    }

    /**
     * Re-sort main parts top page
     * @param App\Models\HpPage $topPage
     * @param App\Models\Hp $hp
     */
    public static function reSortAreaOriginal($topPage,$hp){
        $areaMaster = new HpArea;
        $mainPartMaster = App::make(HpMainPartsRepositoryInterface::class);
        $infoListType = HpMainPartsRepository::PARTS_INFO_LIST;
        $estateKomaType = HpMainPartsRepository::PARTS_ESTATE_KOMA;

        $orderByString = "parts_type_code = ". $infoListType." DESC, parts_type_code = ".$estateKomaType ." DESC";
        $orderByString .= ", CASE WHEN parts_type_code = ".$infoListType. " THEN ABS(".Original::$EXTEND_INFO_LIST['notification_type']. ") END ASC";
        $orderByString .= ", CASE WHEN parts_type_code = ".$estateKomaType. " THEN ABS(".EstateKoma::SPECIAL_ID_ATTR. ") END DESC";

        // re-sort all top page main part
        $mainParts = HpMainPart::where([
            ['page_id', $topPage->id],
            ['hp_id', $hp->id]
        ])->orderByRaw($orderByString)->get();
        $areaMaster = new $areaMaster;

        if($mainParts && $mainParts->count() > 0){
            $ids = array_map(function($item){
                return $item['id'];
            },$mainParts->toArray());

            $areaIds = array_map(function($item){
                return $item['area_id'];
            },$mainParts->toArray());

            if($areaIds && !empty($areaIds)){
                $areaMaster->whereIn('id', $areaIds)->update(['delete_flg' => 1]);
            }

            foreach ($ids as $k => $id) {
                $area = App::make(HpAreaRepositoryInterface::class)->save($topPage, $k + 1, 1, null);
                $mainPartMaster->update(array(['id', $id]), array('area_id' => $area->id));
                // HpMainPart::where('id', $id)->update(['area_id' => $area->id]);
            }
        }
    }

    /**
     * @param $id
     * @param string $company_id
     * @return string
     */
    static public function getScreenUrl($id, $company_id = ''){
        return sprintf(self::$ORIGINAL_URLS[$id],$company_id);
    }

    /**
     * @param $id
     * @return string
     */
    static public function getScreenTitle($id){
        return self::$ORIGINAL_TITLES[$id];
    }

    static public function getOriginalName($id) {
        return [
            config('constants.original.ORIGINAL_EDIT_NAVIGATION') => array(
                'url' => self::getScreenUrl(config('constants.original.ORIGINAL_EDIT_NAVIGATION'), $id),
                'name' => self::getScreenTitle(config('constants.original.ORIGINAL_EDIT_NAVIGATION'))
            ),
            config('constants.original.ORIGINAL_EDIT_NOTIFICATION') => array(
                'url' => self::getScreenUrl(config('constants.original.ORIGINAL_EDIT_NOTIFICATION'), $id),
                'name' => self::getScreenTitle(config('constants.original.ORIGINAL_EDIT_NOTIFICATION'))
            ),
            config('constants.original.ORIGINAL_EDIT_FILE') => array(
                'url' => self::getScreenUrl(config('constants.original.ORIGINAL_EDIT_FILE'), $id),
                'name' => self::getScreenTitle(config('constants.original.ORIGINAL_EDIT_FILE'))
            ),
            config('constants.original.ORIGINAL_EDIT_SPECIAL') => array(
                'url' => self::getScreenUrl(config('constants.original.ORIGINAL_EDIT_SPECIAL'), $id),
                'name' => '物件コマ編集'
            ),
            config('constants.original.ORIGINAL_EDIT_AGENCY') => array(
                'url' => self::getScreenUrl(config('constants.original.ORIGINAL_EDIT_AGENCY'), $id),
                'name' => self::getScreenTitle(config('constants.original.ORIGINAL_EDIT_AGENCY')),
                'open_new_tab' => true
            )
        ];
    }

    /**
     * Get url to import html file
     * @param int $company_id
     * @param string $sub_dir
     * @return string
     */
     static public function getOriginalImportUrl($company_id = '', $sub_dir=''){
        return sprintf(
            self::$ORIGINAL_URLS[config('constants.original.ORIGINAL_EDIT_FILE')] . '%s',
            $company_id,
            '' !== $sub_dir ? "&sub_dir={$sub_dir}" : $sub_dir
        );
    }

    // #3809
    /** @var array  */
    public static $MEDIA_FILES = array(
        'pdf',
        'xls',
        'xlsx',
        'doc',
        'docx',
        'ppt',
        'pptx',
        'ico'
    );

    public static $MEDIA_FOLDERS = array('root','css','js','img');

    /**
     * Get absolute path to import html file
     * @param int $company_id
     * @param string $sub_dir
     * @return array
     */
    static public function getOriginalImportDataInfo($company_id){
        $dataCSS = array(
            'key'   => config('constants.original.ORIGINAL_IMPORT_TOPCSS'),
            'name'   => config('constants.original.ORIGINAL_IMPORT_TOPCSS'),
            'link'  => self::getOriginalImportUrl($company_id, config('constants.original.ORIGINAL_IMPORT_TOPCSS')),
            'direction'  => self::getOriginalImportPath($company_id, config('constants.original.ORIGINAL_IMPORT_TOPCSS')),
            'accepted_exts'  => array('css'),
            'accepted_files' => array('pc_second_layer.css', 'pc_header.css', 'pc_footer.css','sp_second_layer.css', 'sp_header.css', 'sp_footer.css'),
            'extra_files' => false,
            'can_edit_name' => true,
            'can_edit_data' => false
        );
        
        $dataJS = array (
            'key'   => config('constants.original.ORIGINAL_IMPORT_TOPJS'),
            'name'   => config('constants.original.ORIGINAL_IMPORT_TOPJS'),
            'link'  => self::getOriginalImportUrl($company_id, config('constants.original.ORIGINAL_IMPORT_TOPJS')),
            'direction'  => self::getOriginalImportPath($company_id, config('constants.original.ORIGINAL_IMPORT_TOPJS')),
            'accepted_exts'  => array('js'),
            'accepted_files' => array('pc_second_layer.js', 'pc_header.js', 'pc_footer.js', 'sp_second_layer.js', 'sp_header.js', 'sp_footer.js'),
            'extra_files' => false,
            'can_edit_name' => true,
            'can_edit_data' => false
        );
        
        $dataIMG = array (
            'key'   => config('constants.original.ORIGINAL_IMPORT_TOPIMAGE'),
            'name'   => config('constants.original.ORIGINAL_IMPORT_TOPIMAGE'),
            'link'  => self::getOriginalImportUrl($company_id, config('constants.original.ORIGINAL_IMPORT_TOPIMAGE')),
            'direction'  => self::getOriginalImportPath($company_id, config('constants.original.ORIGINAL_IMPORT_TOPIMAGE')),
            'accepted_exts'  => array('png', 'jpg', 'gif'),
            'accepted_files' => array(),
            'extra_files' => false,
            'can_edit_name' => true,
            'can_edit_data' => false
        );
        
        $dataKOMA = array (
            'key'   => config('constants.original.ORIGINAL_IMPORT_TOPKOMA'),
            'name'   => config('constants.original.ORIGINAL_IMPORT_TOPKOMA'),
            'link'  => self::getOriginalImportUrl($company_id, config('constants.original.ORIGINAL_IMPORT_TOPKOMA')),
            'direction'  => self::getOriginalImportPath($company_id, config('constants.original.ORIGINAL_IMPORT_TOPKOMA')),
            'accepted_exts'  => array('html'),
            'accepted_files' => array('special*_pc.html', 'special*_sp.html'),
            'extra_files' => true,
            'can_edit_name' => false,
            'can_edit_data' => false
        );
        
        $dataROOT = array (
            'key'   => config('constants.original.ORIGINAL_IMPORT_TOPROOT'),
            'name'   => config('constants.original.ORIGINAL_IMPORT_TOPROOT'),
            'link'  => self::getOriginalImportUrl($company_id),
            'direction'  => self::getOriginalImportPath($company_id),
            'accepted_exts'  => array('html'),
            'accepted_files' => array('pc_header.html', 'pc_footer.html', 'pc_news1.html', 'pc_news2.html',
                                        'sp_header.html', 'sp_footer.html', 'sp_news1.html', 'sp_news2.html',
                                        'pctop_index.html', 'sptop_index.html'),
            'extra_files' => false,
            'can_edit_name' => false,
            'can_edit_data' => false
        );

        //3809
        $folders = array_map(function($value) { return 'data'.strtoupper($value); }, self::$MEDIA_FOLDERS);
        foreach($folders as $folder){
            ${$folder}['accepted_exts'] = array_merge(self::$MEDIA_FILES,${$folder}['accepted_exts']);
        }

        return array(
            config('constants.original.ORIGINAL_IMPORT_TOPROOT')   => $dataROOT,
            config('constants.original.ORIGINAL_IMPORT_TOPCSS')    => $dataCSS,
            config('constants.original.ORIGINAL_IMPORT_TOPJS')     => $dataJS,
            config('constants.original.ORIGINAL_IMPORT_TOPIMAGE')  => $dataIMG,
            config('constants.original.ORIGINAL_IMPORT_TOPKOMA')   => $dataKOMA
        );
    }

    /**
     * @param boolean $realTitle
     * @param array $page
     * @param App\Models\HpEstateSetting $estateSetting
     * @return mixed|string
     */
    public static function getPageTitle(array $page, $estateSetting, $realTitle = true){
        $title = $page['title'];
        switch ($page['page_type_code']) {
            case HpPageRepository::TYPE_TOP:
                if($realTitle){
                    $title = config('constants.original.TOP_CONTENT');
                }
                break;
            case HpPageRepository::TYPE_ALIAS:
                $page_link_id = $page['link_page_id'];
                $hp_id = $page['hp_id'];
                $data = App::make(HpPageRepositoryInterface::class)->fetchRowByLinkId($page_link_id, $hp_id);
                if($data){
                    $title = $data->title;
                }
                break;
            case HpPageRepository::TYPE_ESTATE_ALIAS:
                $page_link_id = $page['link_estate_page_id'];

                // 物件検索TOPへのリンク追加
                if (preg_match("/^estate_top/", $page_link_id)) {
                    $title  = $estateSetting->getTitle('物件検索トップ','shumoku',false);
                } elseif (preg_match("/^estate_rent/", $page_link_id)) {
                    $title  = $estateSetting->getTitle('賃貸物件検索トップ','rent',false);
                } elseif (preg_match("/^estate_purchase/", $page_link_id)) {
                    $title = $estateSetting->getTitle('売買物件検索トップ','purchase',false);
                }
                // 物件検索種目へのリンク追加
                elseif (preg_match("/^estate_type_/", $page_link_id)) {
                    $searchSettings = $estateSetting->getSearchSettingAll();
                    foreach ($searchSettings as $searchSetting) {
                        foreach ($searchSetting->getLinkIdList(false) as $linkId => $label) {
                            if ($page_link_id == $linkId) {
                                $title  = $label;
                            }
                        }
                    }
                }
                else if (preg_match("/^estate_special_/", $page_link_id, $matches )) {
                    $specials = $estateSetting->getSpecialAll();
                    foreach ($specials as $special) {
                        if ($page_link_id == $special->getLinkId()) {
                            $title = $special->getTitle();
                        }
                    }
                }

                break;
            default:
        };

        return $title;
    }

    public static function cloneData($hp, $oldHp){

        $hp->global_navigation = $oldHp->global_navigation;
        $hp->save();

        /** @var $mainPartMaster */
        $mainPartMaster = App::make(HpMainPartsRepositoryInterface::class);
        $pageMaster = App::make(HpPageRepositoryInterface::class);
        $attributeMaster = App::make(AssociatedHpPageAttributeRepositoryInterface::class);
        $categories = $mainPartMaster->fetchAll([[
            'hp_id', $oldHp->id],
            ['parts_type_code', $mainPartMaster::NEWS_CATEGORY
        ]],['id']);
        if($categories){
            foreach($categories as $category){
                $data = $category->toArray();
                $oldId = $data['id'];
                unset($data['id']);
                unset($data['create_id']);
                unset($data['create_date']);
                unset($data['update_id']);
                unset($data['update_date']);
                unset($data['delete_flg']);
                $data['hp_id'] = $hp->id;
                $data['copied_id'] = $oldId;
                // Cannot find any bulk insert in zend
                $mainPartMaster->create($data);
            }
        }

        unset($categories);

        // clone attributes, get all attributes of old hp
        $attributes = $attributeMaster->fetchAll([
            ['hp_id',$oldHp->id]
        ]);

        $mainPartIds = array_map(function($item){
            return $item['hp_main_parts_id'];
        },$attributes->toArray());

        $pageIds = array_map(function($item){
            return $item['hp_page_id'];
        },$attributes->toArray());

        // get current categories
        $categoriesData = $mainPartMaster->fetchAll([
            ['hp_id', $hp->id],
            ['parts_type_code', $mainPartMaster::NEWS_CATEGORY]
        ],['id']);



        if($attributes){
            foreach($attributes as $item){
                $attribute = $item->toArray();
                $mainPartId = null;
                foreach($categoriesData as $v){
                    if($attribute['hp_main_parts_id'] == $v->copied_id){
                        $mainPartId = $v->id;
                        break;
                    }
                }
                if($mainPartId == null) continue;
                unset($attribute['id']);
                unset($attribute['delete_flg']);
                unset($attribute['create_date']);
                unset($attribute['update_date']);
                $attribute['hp_id'] = $hp->id;
                $attribute['hp_main_parts_id'] = $mainPartId;
                $attributeMaster->create($attribute);
            }
        }
    }

    /**
     * Read specials from Agency
     * @param App\Models\Company $company
     */
    public static function readSpecial($company){
        /** @var App\Models\Hp $agencyHp */
        $agencyHp = $company->getCurrentCreatorHp();

        $topPage = App::make(HpPageRepositoryInterface::class)->getTopPageData($agencyHp->id);
        $agencySetting = $agencyHp->getEstateSetting();

        $estateClass = new EstateKoma(array(
            'hp' => $agencyHp,
            'page' => $topPage,
            'isTopOriginal' => true
        ));

        $mainPartDb = App::make(HpMainPartsRepositoryInterface::class);

        $specialPart = HpMainPartsRepository::PARTS_ESTATE_KOMA;
        $hasNew = false;
        $specialId = EstateKoma::SPECIAL_ID_ATTR;
        $specialSettings = $topPage->fetchParts($specialPart);
        $ids = array_map(function ($ar) use ($specialId) {
            return $ar[$specialId];
        }, $specialSettings->toArray());

        $orderBy = config('constants.special_estate.ORDER_CREATED_DESC_ID_DESC');
        $specials = $agencySetting->getSpecialAllWithPubStatus($orderBy);

        $defaultData = EstateKoma::$DEFAULT_DATA;
        foreach ($specials as $special) {
            if (in_array($special->origin_id, $ids)) {
                continue;
            }
            $data = $defaultData;
            $data['special_id'] = $special->origin_id;
            $topPage->createMainPartTopPage(
                $specialPart,
                $data,
                $agencyHp,
                null
            );
            $hasNew = true;
        }

        if ($hasNew) {
           self::reSortAreaOriginal($topPage,$agencyHp);
        }

        // read from CMS
        $hp = $company->getCurrentHp();
        $topPageCMS = App::make(HpPageRepositoryInterface::class)->getTopPageData($hp->id);
        $specialsCMS = $topPageCMS->fetchParts($specialPart);
        $specialsCMSData = [];
        // filter data, only get col/row/sort option
        foreach($specialsCMS->toArray() as $special){
            $specialsCMSData[$special[$specialId]] = $estateClass->getDataByCMSField($special);
        }

        // get setting again, update cms
        $specialSettings = $topPage->fetchParts($specialPart);
        if($specialSettings && $specialSettings->count() > 0){
            foreach($specialSettings as $specialSetting){
                $id = $specialSetting->$specialId;
                if(!isset($specialsCMSData[$id])) continue;
                $mainPartDb->update(array(['id', $specialSetting->id]), $specialsCMSData[$id]);
            }
        }
    }

    public static function getAdminById($id){
        $index = 'AdminManager_'.$id;
        $data = null;
        if(!Registry::isRegistered($index)){
            $manager = new \App\Models\Manager;
            $data = $manager->getDataForId($id);
            // Registry::set($index,$data);
        }
        // return Registry::get($index);
        return $data;
    }

    public static function getChangedTitlePages(){
        return array_keys(self::$PAGE_TITLE);
    }

    public static $SORT_MAIN_PARTS = array(
        HpMainPartsRepository::PARTS_ESTATE_KOMA,
        HpMainPartsRepository::PARTS_INFO_LIST
    );

    public static $NOT_DELETE_PARTS = array(
        HpMainPartsRepository::PARTS_ESTATE_KOMA,
        HpMainPartsRepository::PARTS_INFO_LIST,
        HpMainPartsRepository::NEWS_CATEGORY
    );

    const MAX_TYPE_INFO_INDEX = 2;
    public static $MULTI_PAGES = array(
        HpPageRepository::TYPE_INFO_INDEX => self::MAX_TYPE_INFO_INDEX
    );

    /**
     * @param int $hpId
     * @param DateTime $datetime
     * @return int
     */
    public static function checkGlobalNavigationChange($hpId,$datetime){
        $table = App::make(HpRepositoryInterface::class);
        return $table->countRows(array(
            ['id', $hpId],
            ['update_date', '>', $datetime->format('Y-m-d H:i:s')],
        ), 'id');
    }

    /**
     * @param array $page
     * @param App\Models\HpEstateSetting $estateSetting
     * @return mixed|string
     */
    public static function getPageFileName(array $page, $estateSetting){
        $fileName = $page['filename'];
        switch ($page['page_type_code']) {
            case HpPageRepository::TYPE_LINK:
                $fileName = $page['link_url'].'" rel="nofollow';
                break;
            case HpPageRepository::TYPE_LINK_HOUSE:
                if(is_string($page['link_house']) && is_array($jsonData = json_decode($page['link_house'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                    $fileName = $jsonData['url'];
                } else {
                    $fileName = $page['link_house'];
                }
                break;
            case HpPageRepository::TYPE_ALIAS:
                $page_link_id = $page['link_page_id'];
                $hp_id = $page['hp_id'];
                $data = \App::make(HpPageRepositoryInterface::class)->fetchRowByLinkId($page_link_id, $hp_id);
                if($data)
                {
                    $fileName = $data->filename;
                    if($data['parent_page_id'] > 0)
                    {
                        $urls= $data->getPageUrl($data['parent_page_id']);
                        if(count($urls) > 0)
                        {
                            foreach($urls as $key => $val) {
                                if($val != null) {
                                    $fileName = $val."/".$fileName;
                                }
                            }
                            if(count($urls) <= 2 )
                            {
                                $fileName =$fileName ."/";
                            }

                        }        
                    }
                }
                break;
            case HpPageRepository::TYPE_ESTATE_ALIAS:
                $page_link_id = $page['link_estate_page_id'];
                
                // 物件検索TOPへのリンク追加
                if (preg_match("/^estate_top/", $page_link_id)) {
                    $fileName  = 'shumoku.html';
                } elseif (preg_match("/^estate_rent/", $page_link_id)) {
                    $fileName  = 'rent.html';
                } elseif (preg_match("/^estate_purchase/", $page_link_id)) {
                    $fileName = 'purchase.html';
                }
                // 物件検索種目へのリンク追加
                elseif (preg_match("/^estate_type_/", $page_link_id)) {
                    $searchSettings = $estateSetting->getSearchSettingAll();
                    foreach ($searchSettings as $searchSetting) {
                        foreach ($searchSetting->getLinkIdList(false) as $linkId => $label) {
                            if ($page_link_id == $linkId) {
                                $fileName  = Estate\TypeList::getInstance()->getUrl(str_replace('estate_type_', '', $page_link_id));
                            }
                        }
                    }
                }
                else if (preg_match("/^estate_special_/", $page_link_id, $matches )) {
                    $specials = $estateSetting->getSpecialAll();
                    foreach ($specials as $special) {
                        if ($page_link_id == $special->getLinkId()) {
                            $fileName = $special->getFilename();
                        }
                    }
                }

                break;
            default:
        };
        //dd($fileName);
        if($page['link_target_blank'] == 1){
            $fileName = $fileName.'" target="_blank';
        }

        return $fileName;
    }
}
