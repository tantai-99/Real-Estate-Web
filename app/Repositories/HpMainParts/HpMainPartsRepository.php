<?php

namespace App\Repositories\HpMainParts;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Library\Custom\Model\Lists\Original;
use App\Repositories\HpPage\HpPageRepositoryInterface;


class HpMainPartsRepository extends BaseRepository implements HpMainPartsRepositoryInterface
{
    /* 共通パーツタイプコード定義 */
    const PARTS_TEXT      = 1;
    const PARTS_LIST      = 2;
    const PARTS_TABLE     = 3;
    const PARTS_MAP       = 4;
    const PARTS_IMAGE     = 5;
    const PARTS_STRUCTURE = 6;
    const PARTS_YOUTUBE   = 7;
    const PARTS_PANORAMA  = 8;
    
    // 会社概要
    const PARTS_COMPANY_OUTLINE = 10;
    
    // お知らせ
    const PARTS_INFO_DETAIL = 11;
    
    // TOP お知らせ
    const PARTS_INFO_LIST = 12;
    
    // プライバシーポリシー
    const PARTS_PRIVACYPOLICY = 13;
    
    // サイトポリシー
    const PARTS_SITEPOLICY = 14;
    
    // 会社沿革
    const PARTS_HISTORY = 15;
    
    // 代表あいさつ
    const PARTS_GREETING = 16;
    
    // 店舗案内
    const PARTS_SHOP_DETAIL = 17;
    
    // スタッフ紹介
    const PARTS_STAFF_DETAIL = 18;
    
    // 採用情報
    const PARTS_RECRUIT = 19;
    
    // ブログ
    const PARTS_BLOG_DETAIL = 20;
    
    // ○○向け サービス内容
    const PARTS_FOR_SERVICE = 21;
    
    // ○○向け サービス紹介
    const PARTS_FOR_SERVICE_INTRODUCTION = 22;
    
    // ○○向け 実例
    const PARTS_FOR_EXAMPLE = 23;
    
    // ○○向け 事例・実例
    const PARTS_FOR_CASE = 24;
    
    // オーナーの声
    const PARTS_FOR_OWNER_REVIEW = 25;
    
    // 法人の声
    const PARTS_FOR_CORPORATION_REVIEW = 26;
    
    // ○○向け サポート体制
    const PARTS_FOR_SUPPORT = 27;
    
    // ○○向け 申請書ダウンロード
    const PARTS_FOR_DOWNLOAD_APPLICATION = 28;
    
    // お客様の声
    const PARTS_CUSTOMERVOICE_DETAIL = 29;
    
    // 売却事例
    const PARTS_SELLINGCASE_DETAIL = 30;
    
    // 街情報
    const PARTS_CITY = 31;
    
    // イベント情報
    const PARTS_EVENT_DETAIL = 32;
    
    // QA
    const PARTS_QA = 33;
    
    // リンク
    const PARTS_LINKS = 34;
    
    // 学区情報
    const PARTS_SCHOOL = 35;
    
    // 内見時のチェックポイント
    const PARTS_PREVIEW = 36;
    
    // 住まいを借りる契約の流れ
    const PARTS_RENT = 37;
    
    // 住まいを貸す契約の流れ
    const PARTS_LEND = 38;
    
    // 住まいを買う契約の流れ
    const PARTS_BUY = 39;
    
    // 住まいを売却する契約の流れ
    const PARTS_SELL = 40;
    
    // 引越しのチェックポイント
    const PARTS_MOVING = 41;
    
    // 不動産用語集
    const PARTS_TERMINOLOGY = 42;
    
    // 説明
    const PARTS_DESCRIPTION = 43;
    
    // 物件小間
    const PARTS_ESTATE_KOMA = 44;

    //CMSテンプレートパターンの追加
    // 事業内容
    const PARTS_BUSINESS_CONTENT = 45;
    // コラム詳細
    const PARTS_COLUMN_DETAIL    = 46;
    // 当社の思い・強み
    const PARTS_COMPANY_STRENGTH = 47;
    // 雛形
    // 不動産「買取り」について
    const PARTS_PURCHASING_REAL_ESTATE = 48;
    // 「買い換えローン」と「住宅ローン」の違い
    const PARTS_REPLACEMENTLOAN_MORTGAGELOAN = 49;
    // 買い換えは売却が先？
    const PARTS_REPLACEMENT_AHEAD_SALE = 50;
    // 中古戸建ての「建物評価」の仕組み
    const PARTS_BUILDING_EVALUATION = 51;
    // 一戸建てを買い手が見学するとき、気にするポイント
    const PARTS_BUYER_VISITS_DETACHEDHOUSE = 52;
    // マンションの売却を有利にするポイント（専有部分）
    const PARTS_POINTS_SALE_OF_CONDOMINIUM = 53;
    // マンションと一戸建て どちらを選ぶ？
    const PARTS_CHOOSE_APARTMENT_OR_DETACHEDHOUSE = 54;
    // 新築？中古？ 選ぶときの考え方
    const PARTS_NEWCONSTRUCTION_OR_SECONDHAND = 55;
    // 建売住宅と注文住宅の違いと選び方
    const PARTS_ERECTIONHOUSING_ORDERHOUSE = 56;
    // 住宅購入のベストタイミングは？
    const PARTS_PURCHASE_BEST_TIMING = 57;
    // ライフプランを立ててみましょう
    const PARTS_LIFE_PLAN = 58;
    // 住宅ローンの種類
    const PARTS_TYPES_MORTGAGE_LOANS = 59;
    // 資金計画を立てましょう
    const PARTS_FUNDING_PLAN = 60;
    // 賃貸管理でお困りのオーナー様へ
    const PARTS_TROUBLED_LEASING_MANAGEMENT = 61;
    // 賃貸管理業務メニュー
    const PARTS_LEASING_MANAGEMENT_MENU = 62;
    // 空室対策（概論的）
    const PARTS_MEASURES_AGAINST_VACANCIES = 63;
    // 競合物件に差をつける住戸リフォーム
    const PARTS_HOUSE_REMODELING = 64;
    // 土地活用をお考えのオーナー様へ（事業化の流れ含む）
    const PARTS_CONSIDERS_LAND_UTILIZATION_OWNER = 65;
    // 土地活用の方法について（賃貸M・AP経営、等価交換M、高齢者向け住宅）
    const PARTS_UTILIZING_LAND = 66;
    // 不動産の購入と相続税対策（税務専門的）
    const PARTS_PURCHASE_INHERITANCE_TAX = 67;
    // 収入から払える家賃の上限はどれくらい？
    const PARTS_UPPER_LIMIT = 68;
    // 賃貸住宅を借りるときの「初期費用」とは
    const PARTS_RENTAL_INITIAL_COST = 69;
    // 候補物件のしぼり方
    const PARTS_SQUEEZE_CANDIDATE = 70;
    // 引越し時の不用品・粗大ゴミなどの処分方法
    const PARTS_UNUSED_ITEMS_AND_COARSEGARBAGE = 71;
    // 快適に暮らすための居住ルール（不動産会社視点）
    const PARTS_COMFORTABLELIVING_RESIDENT_RULES = 72;
    // 店舗探し・自分でできる商圏調査
    const PARTS_STORE_SEARCH = 73;
    // お店成功のためには事業計画書が大切
    const PARTS_SHOP_SUCCESS_BUSINESS_PLAN = 74;
    //CMSテンプレートパターンの追加

    // 特集コマ（検索エンジンレンタル用）
    const PARTS_ESTATE_KOMA_SEARCH_E_R = 75;
    // search freeword ER
    const PARTS_SEARCH_FREEWORD_ENGINE_RENTAL = 80;
    // search freeword
    const PARTS_FREEWORD = 81;
    // category notification
    const NEWS_CATEGORY = 100;

    const PARTS_ARTICLE_TEMPLATE = 110;
    const PARTS_SET_LINK_AUTO = 111;
    const PARTS_ORIGINAL_TEMPLATE = 112;

    /* カラム定義 */
    const COL_TEXT_HEADING_TYPE = 'attr_4';
    const COL_TEXT_HEADING      = 'attr_1';
    const COL_TEXT_BODY         = 'attr_7';

    const COL_LIST_HEADING_TYPE = 'attr_4';
    const COL_LIST_HEADING      = 'attr_1';

    const COL_TABLE_HEADING_TYPE = 'attr_4';
    const COL_TABLE_HEADING      = 'attr_1';

    const COL_IMAGE_HEADING_TYPE = 'attr_4';
    const COL_IMAGE_HEADING      = 'attr_1';
    const COL_IMAGE_ID           = 'attr_8';
    const COL_IMAGE_TITLE        = 'attr_2';
    const COL_IMAGE_LINK_TYPE    = 'attr_5';
    const COL_IMAGE_LINK_PAGE_ID = 'attr_9';
    const COL_IMAGE_LINK_URL     = 'attr_3';
    const COL_IMAGE_LINK_OPEN    = 'attr_6';

    const COL_MAP_HEADING_TYPE = 'attr_4';
    const COL_MAP_HEADING      = 'attr_1';
    const COL_MAP_LAT          = 'attr_10';
    const COL_MAP_LNG          = 'attr_11';
    const COL_IMAGE_LINK_HOUSE = 'attr_12';


    /* その他 */

    // 表示フラグ
    const VISIBLE      = 1;
    const DISPLAY_NONE = 0;

    // リンクタイプ
    const OWN_PAGE = 1;
    const URL      = 2;
    const FILE     = 3;
    const HOUSE    = 4;
    
    // 新規タブ
    const BLANK = 1;

    public function getModel()
    {
        return \App\Models\HpMainPart::class;
    }

    public function getSettingForNotification($linkId,$hpId){
        return $this->model->where([['hp_id', $hpId], [Original::$EXTEND_INFO_LIST['page_id'], $linkId], ['parts_type_code', self::PARTS_INFO_LIST], ['delete_flg', 0]])->first();
    }

    public function getSingleSettingForNotification($hpId, $type = 1){
        $where = array(
            ['hp_id', $hpId],
            ['parts_type_code', self::PARTS_INFO_LIST],
            ['delete_flg', 0],
            [Original::$EXTEND_INFO_LIST['notification_type'], $type],
        );
        return $this->model->where($where)->first();
    }

    static protected $_classMap;
    
    static public function getClassMap() {
        if (self::$_classMap) {
            return self::$_classMap;
        }
        
        $reflect = new \ReflectionClass('\App\Repositories\HpMainParts\HpMainPartsRepository');
        $consts  = $reflect->getConstants();
        
        $map = array();
        foreach ($consts as $name => $type) {
            if (strpos($name, 'PARTS_', 0) !== false) {
                if ($name == "PARTS_LIST") {
                    $name .= 's';
                }
                $map[$type] = 'Library\Custom\Hp\Page\Parts\\' . pascalize(str_replace('PARTS_', '', $name));
            }
        }
        
        self::$_classMap = $map;
        return $map;
    }
    
    static public function getClass($type) {
        $map = self::getClassMap();
        return isset($map[$type]) ? $map[$type] : null;
    }

    protected $timezone = 'Asia/Tokyo';

    function hasChangedNotification($hp_id, $datetime)
    {
        if ($this->timezone !== $datetime->getTimezone()->getName()) {
            $datetime->setTimezone($this->timezone);
        }
        
        return $this->countRows(array(
            ['hp_id', $hp_id],
            ['parts_type_code', self::NEWS_CATEGORY],
            ['update_date', '>', $datetime->format('Y-m-d H:i:s')],
        ), 'id');
    }

    /**
     * function check page parts changed
     *
     * @param integer $hp_id
     * @param string $datetime
     */
    function hasChangedParts($hp_id, $datetime)
    {
        if ($this->timezone !== $datetime->getTimezone()->getName()) {
            $datetime->setTimezone($this->timezone);
        }
        
        $select = $this->model->selectRaw('COUNT(id) as cnt');
        $select->where('hp_id', $hp_id);
        $select->where('update_date', '>', $datetime->format('Y-m-d H:i:s'));
        $select->whereIn('parts_type_code', array(self::PARTS_INFO_LIST, self::PARTS_ESTATE_KOMA));
        
        $row = $select->first();
        return (int) $row->cnt;
    }

    /**
     * function check bukka koma have changed
     *
     * @param integer $hp_id
     * @param string $datetime
     */
    function hasChangedEstateKoma($hp_id, $datetime)
    {
        if ($this->timezone !== $datetime->getTimezone()->getName()) {
            $datetime->setTimezone($this->timezone);
        }
        
        return $this->countRows(array(
            ['hp_id', $hp_id],
            ['parts_type_code', self::PARTS_ESTATE_KOMA],
            ['update_date', '>', $datetime->format('Y-m-d H:i:s')],
        ), 'id');
    }

    /**
         * パーツ名一覧を取得
         *
         * @return array[CONST NAME] = id
         */
        public function getPartsList(){

            $reflect = new ReflectionClass(get_class());
            $consts  = $reflect->getConstants();
            foreach ($consts as $name => $num) {
                if (strpos($name, 'PARTS_', 0) === false) {
                    unset($consts[$name]);
                }
            }
            return $consts;

        }

        /**
         * パーツ一覧を取得（ページ内）
         *
         * @param $pageId
         *
         * @return App\Collections\CustomCollection
         *
         */
        public function getParts($pageId) {

            $select = $this->model->select();
            $select->where('page_id', $pageId);
            $select->orderBy('sort');
            return $select->get();
        }

        /**
         * @param $pageId
         * @param $type
         */
        public function getPartsByType($pageId,$type) {
            $select = $this->model->select();
            $select->where('page_id', $pageId);
            $select->where('parts_type_code', $type);
            $select->orderBy('sort');
            return $select->get();
        }

        /**
         * @param $pageId
         */
        public function getAllNotificationSettings($pageId){
            $partCode = self::PARTS_INFO_LIST;
            $select = $this->model->select();
            $select->where('page_id', $pageId);
            $select->where('parts_type_code', $partCode);
            $select->orderBy(Original::$EXTEND_INFO_LIST['notification_type']);
            return $this->fetchAll($select);
        }

        private $_hpId;
        private $_pageId;
        private $_areaId;
        private $_col;
        private $_partsSort;

        /**
         * 保存
         *
         * @param       $sort
         * @param array $val
         * @param       $hpId
         * @param       $pageId
         *
         * @return mixed
         */
        public function save($areaRow, $col, $partsSort, $parts) {

            $this->_hpId = $areaRow->hp_id;
            $this->_pageId = $areaRow->page_id;
            $this->_areaId = $areaRow->id;
            $this->_col = (int)$col;
            $this->_partsSort = $partsSort;

            $methodName = 'save'.ucfirst(key($parts));
            return $this->$methodName($parts[key($parts)]);
        }


        private function saveList($parts) {

            $data = array(
                'parts_type_code'           => self::PARTS_LIST,
                'sort'                      => $this->_partsSort,
                'column_sort'               => $this->_col,
                'area_id'                   => $this->_areaId,
                'page_id'                   => $this->_pageId,
                'hp_id'                     => $this->_hpId,
                'display_flg'               => $parts['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
                self::COL_LIST_HEADING_TYPE => str_replace('h', '', $parts['heading_type']),
                self::COL_LIST_HEADING      => $parts['heading'],
            );

            return $this->insertRow($data);
        }


        private function saveTable($parts) {

            $data = array(
                'parts_type_code'            => self::PARTS_TABLE,
                'sort'                       => $this->_partsSort,
                'column_sort'                => $this->_col,
                'area_id'                    => $this->_areaId,
                'page_id'                    => $this->_pageId,
                'hp_id'                      => $this->_hpId,
                'display_flg'                => $parts['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
                self::COL_TABLE_HEADING_TYPE => str_replace('h', '', $parts['heading_type']),
                self::COL_TABLE_HEADING      => $parts['heading'],
            );

            return $this->insertRow($data);
        }

        private function saveText($parts) {

            $data = array(
                'parts_type_code'           => self::PARTS_TEXT,
                'sort'                      => $this->_partsSort,
                'column_sort'               => $this->_col,
                'area_id'                   => $this->_areaId,
                'page_id'                   => $this->_pageId,
                'hp_id'                     => $this->_hpId,
                'display_flg'               => $parts['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
                self::COL_TEXT_HEADING_TYPE => str_replace('h', '', $parts['heading_type']),
                self::COL_TEXT_HEADING      => $parts['heading'],
                self::COL_TEXT_BODY         => $parts['body'],

            );

            return $this->insertRow($data);
        }

        private function saveImage($parts) {

            $data = array(
                'parts_type_code'            => self::PARTS_IMAGE,
                'sort'                       => $this->_partsSort,
                'column_sort'                => $this->_col,
                'area_id'                    => $this->_areaId,
                'page_id'                    => $this->_pageId,
                'hp_id'                      => $this->_hpId,
                'display_flg'                => $parts['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
                self::COL_IMAGE_HEADING_TYPE => str_replace('h', '', $parts['heading_type']),
                self::COL_IMAGE_HEADING      => $parts['heading'],
                self::COL_IMAGE_ID           => $parts['image_id'],
                self::COL_IMAGE_TITLE        => $parts['title'],
                self::COL_IMAGE_LINK_TYPE    => $parts['link_type'] == 'own_page' ? self::OWN_PAGE : self::URL,
                self::COL_IMAGE_LINK_PAGE_ID => $parts['link_type'] == 'own_page' ? $parts['page_id'] : '',
                self::COL_IMAGE_LINK_URL     => $parts['link_type'] == 'url' ? $parts['url'] : '',
                self::COL_IMAGE_LINK_OPEN    => $parts['open'] == '_blank' ? self::BLANK : self::SELF,
                self::COL_IMAGE_LINK_HOUSE   => $parts['link_type'] == 'house' && isset($parts['link_house']) ? $parts['link_house'] : '',
            );

            return $this->insertRow($data);
        }

        private function saveMap($parts) {

            $data = array(
                'parts_type_code'          => self::PARTS_MAP,
                'sort'                     => $this->_partsSort,
                'column_sort'              => $this->_col,
                'area_id'                  => $this->_areaId,
                'page_id'                  => $this->_pageId,
                'hp_id'                    => $this->_hpId,
                'display_flg'              => $parts['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
                self::COL_MAP_HEADING_TYPE => str_replace('h', '', $parts['heading_type']),
                self::COL_MAP_HEADING      => $parts['heading'],
                self::COL_MAP_LAT          => $parts['lat'],
                self::COL_MAP_LNG          => $parts['lng'],
            );

            return $this->insertRow($data);
        }

        /**
         * レコードを作成
         *
         * @param $data
         *
         * @return App\Models\Model
         */
        private function insertRow($data) {

            $newRow = $this->createRow($data);

            $newRow->save();
            return $newRow;
        }

        public function getPartsByParentId($parentId) {
            $select = $this->model->select();
            $select->where('attr_4', $parentId);
            return $select->get();
        }
        
        public function getAllNotificationSelect($pageId){
            $partCode = self::PARTS_INFO_LIST;
            $select = $this->model->select('attr_4');
            $select->where('page_id', $pageId);
            $select->where('parts_type_code', $partCode);
            $select->orderBy(Original::$EXTEND_INFO_LIST['notification_type']);
            
            $page = \App::make(HpPageRepositoryInterface::class);
            $pageSelect = $page->model()->select();
            $pageSelect->whereIn('id', $select->get());
            // dd($pageSelect->__toString());
             return $pageSelect->get();
        }

        public function getNotificationClassDetail($hpId) {
            $sql = '
                SELECT associated_hp_page_attribute.hp_id, hp_main_parts.attr_2, hp_main_parts.attr_3,associated_hp_page_attribute.hp_main_parts_id, associated_hp_page_attribute.hp_page_id AS link_id, hp_page.id AS page_id
                FROM `hp_main_parts` 
                RIGHT JOIN associated_hp_page_attribute
                ON hp_main_parts.id = associated_hp_page_attribute.hp_main_parts_id
                INNER JOIN hp_page 
                ON associated_hp_page_attribute.hp_page_id = hp_page.link_id
                WHERE hp_page.page_flg = 1
                AND hp_page.delete_flg = 0
                AND associated_hp_page_attribute.hp_id = ?
                AND hp_page.hp_id = ?
            ';
            return \DB::select($sql, array($hpId, $hpId));
        }
}
