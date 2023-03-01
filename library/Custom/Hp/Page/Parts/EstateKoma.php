<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists\Original;
use Library\Custom\Model\Estate\KomaSortOptionList;
use Library\Custom\Registry;
use Library\Custom\View\TopOriginalLang;
use Library\Custom\Util;
use DateTime;
use App\Rules\InArray;
use App\Rules\Digits;
use App\Rules\Between;
use App\Rules\NotEmpty;

class EstateKoma extends PartsAbstract {

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array();

    protected $_title    = '物件コマ';
    protected $_template = 'estate-koma';

    protected $selectHolder = '（未選択）';

    const SPECIAL_ID_ATTR = 'attr_1';

    // protected $_is_unique   = true;

    protected $_has_heading = false;

	protected $_columnMap = array(
        // special_estate.origin_id
        'special_id'  => 'attr_1',
        'rows'        => 'attr_2',
        'sort_option' => 'attr_3',
	);

    const PC_COLUMNS = 'pc_columns';
    const PC_COLUMNS_DISABLE = 'pc_columns_disable';
    const PC_ROWS = 'pc_rows';
    const PC_ROWS_DISABLE = 'pc_rows_disable';
    const SP_COLUMNS = 'sp_columns';
    const SP_COLUMNS_DISABLE = 'sp_columns_disable';
    const SP_ROWS = 'sp_rows';
    const SP_ROWS_DISABLE = 'sp_rows_disable';
    const CMS_DISABLE = 'display_flg';
    const SORT_OPTION = 'sort_option';
    const EDITABLE_TEXT = '_disable';
    const LAST_UPDATE = 'last_update';

    const COLUMN_NUMBER = Original::KOMA_COLUMN;
    const ROW_NUMBER = Original::KOMA_ROW;

    const COLUMN_NUMBER_DEFAULT = 1;
    const ROW_NUMBER_DEFAULT = 1;

    const CMS_DISPLAY_DEFAULT = 1;
    const CMS_DISABLE_DEFAULT = 0;

    protected $selectColumn = [
        self::PC_ROWS => self::ROW_NUMBER,
        self::PC_COLUMNS => self::COLUMN_NUMBER,
        self::SP_ROWS => self::ROW_NUMBER,
        self::SP_COLUMNS => self::COLUMN_NUMBER
    ];

	protected $_topOriginalColumnMap = array(
        'special_id'                => self::SPECIAL_ID_ATTR,
        self::CMS_DISABLE           => 'display_flg',
        self::LAST_UPDATE           => 'attr_2',
        self::SORT_OPTION           => 'attr_3',
        self::PC_COLUMNS            => 'attr_4',
        self::PC_COLUMNS_DISABLE    => 'attr_5',
        self::PC_ROWS               => 'attr_6',
        self::PC_ROWS_DISABLE       => 'attr_7',
        self::SP_COLUMNS            => 'attr_8',
        self::SP_COLUMNS_DISABLE    => 'attr_9',
        self::SP_ROWS               => 'attr_10',
        self::SP_ROWS_DISABLE       => 'attr_11'
    );

	public static $DEFAULT_DATA = array(
        self::CMS_DISABLE           => self::CMS_DISABLE_DEFAULT,
        self::SORT_OPTION           => 1,
        self::PC_COLUMNS            => self::COLUMN_NUMBER_DEFAULT,
        self::PC_COLUMNS_DISABLE    => self::CMS_DISABLE_DEFAULT,
        self::PC_ROWS               => self::ROW_NUMBER_DEFAULT,
        self::PC_ROWS_DISABLE       => self::CMS_DISABLE_DEFAULT,
        self::SP_COLUMNS            => self::COLUMN_NUMBER_DEFAULT,
        self::SP_COLUMNS_DISABLE    => self::CMS_DISABLE_DEFAULT,
        self::SP_ROWS               => self::ROW_NUMBER_DEFAULT,
        self::SP_ROWS_DISABLE       => self::CMS_DISABLE_DEFAULT
    );

	public static $CMS_FIELD = array(
        self::PC_ROWS,
        self::PC_COLUMNS,
        self::SP_ROWS,
        self::SP_COLUMNS,
        self::SORT_OPTION
    );

	public function getDataByCMSField(array $data){
	    $fields = self::$CMS_FIELD;

        $cols = $this->getFillable();

        foreach($cols as $k => $v){
            if(in_array($k,$fields)) continue;
            unset($cols[$k]);
        }

        $cols = array_values($cols);

        foreach($data as $k => $v){
            if(in_array($k,$cols)) continue;
            unset($data[$k]);
        }

        return $data;
    }


    /**
     * @return array
     */
	public function getFillable(){
	    $columns = $this->_topOriginalColumnMap;
	    unset($columns['special_id']);
	    unset($columns['last_update']);
	    return $columns;
    }

    public function getSelects(){
        return array_keys($this->selectColumn);
    }

    /**
     * @throws Zend_Form_Exception|Exception
     */
    public function init() {
        parent::init();

        // 抽出条件
        // $this->_hp = isset($setting['hp']) ? $setting['hp'] : null;
        // $this->_page = isset($setting['page']) ? $setting['page'] : null;
        // $this->_isTopOriginal = isset($setting['isTopOriginal']) ? isset($setting['isTopOriginal']) : null;

        if($this->_isTopOriginal() && $this->_page->page_type_code == HpPageRepository::TYPE_TOP){
            $this->removeElement('display_flg');
            $this->disableDefault(array('sort', 'column_sort'));
            $this->setPlanTopOriginal();
            $this->_template = 'estate-koma-original';
            return;
        }
        $this->_init();
    }

    /**
     * @throws Zend_Form_Exception
     */
    public function _init(){
        /**
         * @var  App\Models\Hp $hp
         */
        $options = [];
        $hp = $this->_hp;
        $setting = $hp->getEstateSetting();
        if ($setting) {
            foreach ($setting->getSpecialAllWithPubStatus() as $special) {
                /**
                 * @var App\Models\SpecialEstate $special
                 */
                $options[$special->origin_id] = $special->getTitle(true);
            }
        }

        try {
            $element = new Element\Select('special_id');
            $element->setValidRequired(true);
            $element->setLabel('抽出条件（特集）');
            $element->setValueOptions($options);
            $this->add($element);

            // 表示方法（行数）
            $element = new Element\Select('rows');
            $element->setRequired(true);
            $element->setLabel('表示方法');
            $options = array();
            for ($i = 1; $i <= 3; $i++) {
                $options[$i] = $i.'行';
            }
            $element->setValueOptions($options);
            $element->setValue(1);
            $this->add($element);

            // 表示順
            $element = new Element\Select('sort_option');
            $element->setRequired(true);
            $element->setLabel('表示順');
            $element->setValueOptions(KomaSortOptionList::getInstance()->getAll());
            $element->setValue(1);
            $this->add($element);
        }
        catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * 特集のパスを取得
     *
     * @return string
     */
    public function getSpecialPath() {

        $res = '';

        $special = $this->fetchThisSpecial();
        if ($special) {
            $res = $special->filename;
        }
        return $res;
    }

    /**
     * 特集のタイトルを取得
     *
     * @return string
     */
    public function getSpecialTitle() {

        $res = '';

        $special = $this->fetchThisSpecial();
        if ($special) {
            $res = $special->title;
        }
        return $res;
    }

    /**
     * @return null
     */
    private function fetchThisSpecial() {

        $origin_id = (int)$this->getValue('special_id');

        $setting = $this->_hp->getEstateSetting();
        if ($setting) {
            foreach ($setting->getSpecialAll() as $special) {
                if ($origin_id === (int)$special->origin_id) {
                    return $special;
                }
            }
        }
        return null;
    }

    /**
     * Set Plan Top Original
     * @throws Exception
     */
    protected function setPlanTopOriginal()
    {
        $this->_columnMap   =  $this->_topOriginalColumnMap;
        // generate form
        $this->formPlanTopOriginal();
    }

    public function setDefaults(array $values)
    {
        parent::setDefaults($values); // TODO: Change the autogenerated stub

        if($this->_isTopOriginal() && $this->_page->page_type_code == HpPageRepository::TYPE_TOP){
            $this->_populateTopOriginal();
        }
    }

    protected function _populateTopOriginal(){
        $userClass = get_class(getInstanceUser('cms'));
        if (!($userClass == 'Library\Custom\User\Admin')) {
            $hp = $this->_hp;
            $setting = $hp->getEstateSetting();
            if ($setting) {

                if(!Registry::isRegistered('specialsData')){
                    $specials = $setting->getSpecialAllWithPubStatus();
                    Registry::set('specialsData', $specials);
                }
                $specials = Registry::get('specialsData');
                $special_id = $this->getElement('special_id')->getValue();

                foreach($specials as $special){
                    if($special->origin_id != $special_id) continue;
                    $this->getElement('special_title')->setValue($special->getTitle(false));
                    $this->getElement('is_public')->setValue($special->is_public);
                    $types = $special->toSettingObject()->getDisplayEstateType();
                    $typesData = implode(' - ', array_map("trim",array_filter($types)));
                    $this->getElement('special_type')->setValue($typesData);
                }
            }
        }
        else {
            $updateId = $this->getElement('update_id')->getValue();
            if($updateId){
                $lastUpdateUser = Original::getAdminById($updateId);
                if($lastUpdateUser){
                    $this->getElement('update_name')->setValue($lastUpdateUser->name);
                }
            }
        }
    }

    /**
     * Generate required fields for top originals
     * @return string
     * @throws Exception
     */
    protected function formPlanTopOriginal(){
        try {
            if (!(get_class(getInstanceUser('admin')) == 'Library\Custom\User\Admin')) {
                $special_title = new Element\Hidden('special_title');
                $this->add($special_title);
                $special_public = new Element\Hidden('is_public');
                $this->add($special_public);
                $special_type = new Element\Hidden('special_type');
                $this->add($special_type);
            }

            $last_update = new Element\Hidden(self::LAST_UPDATE);
            $this->add($last_update);

            $id = new Element\Hidden('id');
            $this->add($id);

            $update_username = new Element\Hidden('update_name');
            $this->add($update_username);

            $update_id = new Element\Hidden('update_id');
            $this->add($update_id);

            $special_id = new Element\Hidden('special_id');
            $special_id->setLabel('抽出条件（特集）');
            $this->add($special_id);

            // add CMS show hide button
            $lang = new TopOriginalLang();
            $cms_display_checkbox = new Element\Checkbox(self::CMS_DISABLE);
            // [
            //     'checkedValue'  => 1,
            //     'uncheckedValue' => 0
            // ]
            $cms_display_checkbox->setAttribute('class', 'form-control');
            $cms_display_checkbox->setLabel($lang->get('special_estate.display_flg'));
            $cms_display_checkbox->addValidator(new InArray(array(0,1)));
            $this->add($cms_display_checkbox);

            //sort options
            $sortArr = KomaSortOptionList::getInstance()->getAll();
            // 表示順
            $select = new Element\Select(self::SORT_OPTION);
            $select->setRequired(true);
            $select->setLabel('表示順');
            $select->setValueOptions($sortArr);
            $select->addValidator(new InArray(array_keys($sortArr)));
            $this->add($select);

            // generate selects
            foreach($this->selectColumn as $k => $v){
                $select = new Element\Select($k);
                $select->addValidator(new Digits());
                $select->addValidator(new Between(['min' => 1, 'max' => $v]));
                $select->addValidator(new NotEmpty());
                $select->setAttribute('class', 'form-control');
                $select->setValueOptions($this->renderSelect($v));
                $select->setRequired(true);
                $select->setValue(1);
                $this->add($select);

                // add CMS editable row/column
                $checkbox = (new Element\Checkbox($k. self::EDITABLE_TEXT));
                $checkbox->setAttribute('class', 'form-control ' .$k. self::EDITABLE_TEXT);
                $checkbox->setLabel($lang->get('special_estate.cms_editable'));
                $checkbox->addValidator(new InArray([0,1]));
                $this->add($checkbox);
            }
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }




    public function save($hp, $page, $areaId = null)
    {
        if($this->_isTopOriginal() && $page->page_type_code == HpPageRepository::TYPE_TOP){
            $this->saveTop($hp,$page,$areaId);
            return;
        }
        parent::save($hp, $page, $areaId);
    }

    public function saveTop($hp, $page, $areaId = null)
    {
        $allFields = $this->getFillable();

        $allowKeys = array();
        $data = array();

        /** @var App\Repositories\HpMainParts\HpMainPartsRepository $table */
        $table = $this->getSaveTable();
        $partCode = HpMainPartsRepository::PARTS_ESTATE_KOMA;

        $id = $this->getElement('id')->getValue();
        $specialId = $this->getElement('special_id')->getValue();

        if($id){
            $row = $table->fetchRow(array(
                ['id', $id],
                ['hp_id', $hp->id],
                ['page_id', $page->id],
                ['parts_type_code', $partCode]
            ));
            if(!$row) return false;
        }
        else {
            $row = $table->create(array(
                'parts_type_code' => $partCode,
                'sort' => 0,
                'column_sort' => 1,
                'display_flg' => 1,
                'hp_id' => $hp->id,
                'page_id' => $page->id,
                self::SPECIAL_ID_ATTR => $specialId,
                'area_id' => ($areaId !== null) ? $areaId : null
            ));
        }

        $userClass = get_class(getInstanceUser('cms'));
        switch($userClass){
            case 'Library\Custom\User\Cms':
            case 'Library\Custom\User\Agency':
                $allowKeys = self::$CMS_FIELD;
                    foreach ($allowKeys as $k=>$v){
                        if($v == self::SORT_OPTION) continue;
                        $disableKey = $allFields[$v.self::EDITABLE_TEXT];
                        if($row->$disableKey == 1){
                            unset($allowKeys[$k]);
                        }
                    }
                break;
            case 'Library\Custom\User\Admin':
                $allowKeys = array_keys($allFields);
                $allowKeys[] = self::LAST_UPDATE;
                $datetime = new DateTime();
                $lastUpdate = $datetime->format('Y-m-d H:i:s');
                $this->getElement(self::LAST_UPDATE)->setValue($lastUpdate);
                $row->update_id = getInstanceUser('admin')->getProfile()->id;
                break;
        }

        // no field can update, return true as success
        if(empty($allowKeys)){
            $row->save();
            return true;
        }

        foreach ($this->getElements() as $name => $element) {
            $value = $element->getValue();
            if (Util::isEmpty($value)) {
                continue;
            }

            if (!in_array($name,$allowKeys)) {
                continue;
            }

            if (isset($this->_columnMap[$name])) {
                $name = $this->_columnMap[$name];
            }

            $data[$name] = $value;
        }

        foreach($data as $k => $v){
            $row->$k = $v;
        }

        $row->save();
    }

    public function getField($field){
        return $this->_columnMap[$field];
    }

    public function isValid($data, $checkError = true)
    {
        if($this->_isTopOriginal()){
            // $this->removeElement('sort');
            // $this->removeElement('column_sort');
            $this->sort->setRequired(false);
            $this->column_sort->setRequired(false);
            if (isset($data['id'])) {
                /** @var App\Repositories\HpMainParts\HpMainPartsRepository $table */
                $table = $this->getSaveTable();
                $row = $table->fetchRow(array(
                    ['id', $data['id']],
                    ['parts_type_code', HpMainPartsRepository::PARTS_ESTATE_KOMA]
                ));

                if(!$row) throw new Exception('No Estate Koma');

                // now display_flg is checkbox only available in admin, we need to add it from db
                $displayFlg = self::CMS_DISABLE;
                $data[$displayFlg] = $row->$displayFlg;
                $userClass = get_class(getInstanceUser('cms'));
                switch($userClass){
                    case 'Library\Custom\User\Cms':
                    case 'Library\Custom\User\Agency':
                            $allowKeys = self::$CMS_FIELD;
                            foreach ($allowKeys as $k=>$v){
                                if($v == self::SORT_OPTION) continue;
                                $disableKey = $this->getField($v.self::EDITABLE_TEXT);
                                $field = $this->getField($v);
                                if($row->$disableKey == 1){
                                    $data[$v] = $row->$field;
                                }
                            }
                        break;
                    case 'Library\Custom\User\Admin':
                        break;
                }
            }
        }
        return parent::isValid($data, $checkError); // TODO: Change the autogenerated stub
    }
}