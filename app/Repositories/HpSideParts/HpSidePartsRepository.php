<?php
namespace App\Repositories\HpSideParts;
use App\Repositories\BaseRepository;
use App\Repositories\HpPage\HpPageRepository;

class HpSidePartsRepository extends BaseRepository implements HpSidePartsRepositoryInterface
{
    const PARTS_LINK  = 1;
    const PARTS_IMAGE = 2;
    const PARTS_TEXT  = 3;
    const PARTS_QR    = 4;
    const PARTS_FB    = 5;
    const PARTS_TW    = 6;
    const PARTS_MAP   = 7;
    const PARTS_LINE_AT_QR   = 8;
    const PARTS_LINE_AT_BTN  = 9;
    //start No.2 add freeword side parts
    const PARTS_FREEWORD = 10;
    //end No.2 

    const PARTS_PANORAMA = 11;

    public function getModel()
    {
        return \App\Models\HpSideParts::class;
    }

    static protected $_classMap;
        
    static public function getClassMap() {
        if (self::$_classMap) {
            return self::$_classMap;
        }
        
        $reflect = new \ReflectionClass('\App\Repositories\HpSideParts\HpSidePartsRepository');
        $consts  = $reflect->getConstants();
    
        $map = array();
        foreach ($consts as $name => $type) {
            if (strpos($name, 'PARTS_', 0) !== false) {
                $map[$type] = 'Library\Custom\Hp\Page\SideParts\\' . pascalize(str_replace('PARTS_', '', $name));
            }
        }
    
        self::$_classMap = $map;
        return $map;
    }
    
    static public function getClass($type) {
        $map = self::getClassMap();
        return isset($map[$type]) ? $map[$type] : null;
    }
    


    /* カラム定義 */
    const COL_LINK_HEADING  = 'attr_1';
    const COL_IMAGE_HEADING = 'attr_1';
    const COL_TEXT_HEADING  = 'attr_1';
    const COL_TEXT_BODY     = 'attr_2';
    const COL_QR_HEADING    = 'attr_1';


    /* その他 */

    // 共通パーツフラグ
    const COMMON_PARTS   = 1;
    const ORIGINAL_PARTS = 0;

    // 表示フラグ
    const VISIBLE      = 1;
    const DISPLAY_NONE = 0;


    public function init() {
        parent::init();
    }

    private $_sort;
    private $_val;
    private $_hpId;
    private $_pageId;
    private $_isCommon = false;

    public function getPartsByHp($id){
        $s = $this->model->select();
        $s->where('hp_id', $id);
        $s->limit(1);
        $row = $s->first();
        return $row;
    }
    /**
     * パーツ名一覧を取得
     * @return array[CONST NAME] = id
     */
    public function getPartsList(){

        $reflect = new \ReflectionClass(get_class());
        $consts  = $reflect->getConstants();
        foreach ($consts as $name => $num) {
            if (strpos($name, 'PART_', 0) === false) {
                unset($consts[$name]);
            }
        }
        return $consts;

    }

    /**
     * パーツ一覧を取得
     * @param $pageId
     *
     */
    public function getParts($pageId) {

        $select = $this->model->select();
        $select->where('page_id', $pageId);
        $select->orderBy('sort');
        return $select->get();
    }

    /**
     * パーツを取得
     * @param $pageId
     * @param $partTypeCode
     * @param int $displayFlg
     */
    public function getPartByPageId($pageId, $partTypeCode, $displayFlg = self::VISIBLE) {
        $select = $this->model->select();
        $select->where('page_id', $pageId);
        $select->where('parts_type_code', $partTypeCode);
        $select->where('display_flg', $displayFlg);
        return $select->first();
    }

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
    public function save($sort, $val = array(), $hpId, $pageRow) {

        $this->_sort = $sort;
        $this->_val = $val;
        $this->_hpId = $hpId;
        $this->_pageId = $pageRow->id;
        if (HpPageRepository::TYPE_TOP == $pageRow->page_type_code) {
            $this->_isCommon = true;
        };

        $methodName = 'save'.ucfirst(key($val));
        return $this->$methodName();
    }
            
    /**
    * 共通のサイドパーツを表示できるか判定
    * @param $sideParts
    * @param $hp
    * @return bool
    */
    public static function isDisplayCommonSideParts($sideParts, $hp) {
        $siteSettingSideParts = [
            self::PARTS_LINE_AT_QR,
            self::PARTS_TW,
            self::PARTS_FB,
            self::PARTS_LINE_AT_BTN
        ];
            foreach ($sideParts as $side) {
                if (isset($side['parts_type_code'])) {
                    // $siteSettingSideParts以外がサイドパーツに含まれている場合はtrueを返す
                    if (!in_array((int)$side['parts_type_code'], $siteSettingSideParts, true)) {
                        return true;
                    }
                    /**
                     * 「初期設定あり」が設定されている場合はtrueを返す
                     */
                    // LINE QR
                    if ($hp->line_at_freiend_qrcode && (int)$side['parts_type_code'] === $siteSettingSideParts[0]) {
                        return true;
                    }
                    // Twitterタイムライン
                    if ($hp->tw_timeline_flg && (int)$side['parts_type_code'] === $siteSettingSideParts[1]) {
                        return true;
                    }
                    // Facebookタイムライン
                    if ($hp->fb_timeline_flg && (int)$side['parts_type_code'] === $siteSettingSideParts[2]) {
                        return true;
                    }
                    // LINE ボタン
                    if ($hp->line_at_freiend_button && (int)$side['parts_type_code'] === $siteSettingSideParts[3]) {
                        return true;
                    }
                }
            }
            return false;
        }


    private function saveLink() {

        $data = array(
            'parts_type_code'  => self::PARTS_LINK,
            self::COL_LINK_HEADING => $this->_val['link']['heading'],
            'display_flg'      => $this->_val['link']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'             => $this->_sort,
            'common_parts_flg' => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'       => '0',
            'hp_id'            => $this->_hpId,
            'page_id'          => $this->_pageId,
        );

        return $this->insertRow($data);
    }


    private function saveImage() {

        $data = array(
            'parts_type_code'   => self::PARTS_IMAGE,
            self::COL_IMAGE_HEADING => $this->_val['image']['heading'],
            'display_flg'       => $this->_val['image']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'              => $this->_sort,
            'common_parts_flg'  => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'        => '0',
            'hp_id'             => $this->_hpId,
            'page_id'           => $this->_pageId,
        );

        return $this->insertRow($data);
    }


    private function saveText() {

        $data = array(
            'parts_type_code'  => self::PARTS_TEXT,
            self::COL_TEXT_HEADING => $this->_val['text']['heading'],
            self::COL_TEXT_BODY    => $this->_val['text']['body'],
            'display_flg'      => $this->_val['text']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'             => $this->_sort,
            'common_parts_flg' => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'       => '0',
            'hp_id'            => $this->_hpId,
            'page_id'          => $this->_pageId,
        );

        return $this->insertRow($data);
    }

    private function saveQr() {

        $data = array(
            'parts_type_code'  => self::PARTS_QR,
            self::COL_QR_HEADING   => $this->_val['qr']['heading'],
            'display_flg'      => $this->_val['qr']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'             => $this->_sort,
            'common_parts_flg' => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'       => '0',
            'hp_id'            => $this->_hpId,
            'page_id'          => $this->_pageId,
        );

        return $this->insertRow($data);
    }

        private function saveFb() {

        $data = array(
            'parts_type_code'  => self::PARTS_FB,
            'display_flg'      => $this->_val['fb']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'             => $this->_sort,
            'common_parts_flg' => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'       => '0',
            'hp_id'            => $this->_hpId,
            'page_id'          => $this->_pageId,
        );

        return $this->insertRow($data);
    }

    private function saveTw() {

        $data = array(
            'parts_type_code'  => self::PARTS_TW,
            'display_flg'      => $this->_val['tw']['display'] == 'visible' ? self::VISIBLE : self::DISPLAY_NONE,
            'sort'             => $this->_sort,
            'common_parts_flg' => $this->_isCommon ? self::COMMON_PARTS : self::ORIGINAL_PARTS,
            'delete_flg'       => '0',
            'hp_id'            => $this->_hpId,
            'page_id'          => $this->_pageId,
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

        $newRow = $this->create($data);

        return $newRow;
    }
}
