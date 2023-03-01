<?php
namespace Library\Custom\Hp\Page\Parts;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use Library\Custom\Model\Lists\Original;
use Library\Custom\Form\Element;
use Library\Custom\View\TopOriginalLang;
use App\Rules\InArray;
use Library\Custom\Util;

class InfoList extends PartsAbstract {

	protected $_title = 'お知らせ';
	protected $_template = 'info-list';

	protected $_is_unique = true;

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'page_size'		=> 'attr_3',
	);

    protected $_isTop = false;

	public function init() {
		parent::init();

		//only work for TopPage
        if($this->_isTopOriginal() && $this->_page->page_type_code == HpPageRepository::TYPE_TOP){
            $this->_isTop = true;
            $this->_columnMap = array_merge($this->_columnMap,Original::$EXTEND_INFO_LIST);
            $this->_is_unique = false;
            $this->_template = 'info-list-original';
            $this->setIsRequired(true);
            $this->_topOriginalForm();
            $this->disableDefault(array('sort','column_sort','display_flg'));
            return;
        }

        $this->_init();
	}

	public function _init(){
        $element = new Element\Select('page_size');
        $element->setRequired(true);
        $element->setLabel('表示件数');
        $options = array();
        for ($i=1;$i<=30;$i++) {
            $options[ $i ] = $i;
        }
        $element->setValueOptions($options);
        $element->setAttribute('class', 'w80');
        $element->setValue(1);

        $this->add($element);
    }

    public function _topOriginalForm(){
        try{
            $this->add(new Element\Hidden('id'));

            $this->add(new Element\Hidden('page_id'));

            $this->add(new Element\Hidden('notification_type'));

            $select = new Element\Select('page_size');
            // [
            //     'class'     => 'form-control select-number settings-form',
            //     'multiOptions' => $this->renderSelect(config('constants.original.MAX_NOTIFICATION_PAGE_SIZE')),
            //     'required'=> true
            // ]
            $select->setRequired(true);
            $select->setAttribute('class', 'form-control select-number settings-form');
            $select->setValueOptions($this->renderSelect(config('constants.original.MAX_NOTIFICATION_PAGE_SIZE')));

            $this->add($select);


            // add CMS editable row/column
            $lang = new TopOriginalLang();
            $checkbox = (new Element\Checkbox('cms_disable'));
            $checkbox->setAttribute('class', 'form-control  settings-form');
            $checkbox->setLabel($lang->get('notification_settings.news.cms_editable'));
            $checkbox->addValidator(new InArray(array(0,1)));
            $this->add($checkbox);

            $this->add(new Element\Hidden('heading'));
            $this->add(new Element\Hidden('heading_type'));
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }


    public function setDefaults(array $values){
	    parent::setDefaults($values);
        if($this->_isTop){
            $this->_title .=  $this->getElement('notification_type')->getValue();
        }
    }

    public function save($hp, $page, $areaId = null)
    {

        if ($this->_isTop) {
            $this->saveTop($hp, $page, $areaId);
            return;
        }
        parent::save($hp, $page, $areaId);
    }


    public function saveTop($hp, $page, $areaId = null)
    {
        $allFields = $this->_columnMap;
        $allowKeys = array();
        $data = array();

        $table = $this->getSaveTable();
        $partCode = HpMainPartsRepository::PARTS_INFO_LIST;

        $id = $this->getElement('id')->getValue();
        $notification_type = $this->getElement('notification_type')->getValue();
        $this->getElement('heading')->setValue('NEWS '. $notification_type);

        if ($id) {
            $row = $table->fetchRow(array(
                ['id', $id],
                ['hp_id', $hp->id],
                ['page_id', $page->id],
                ['parts_type_code', $partCode]
            ));
            if (!$row) return false;
        } else $row = $table->create(array(
            'parts_type_code' => $partCode,
            'sort' => 0,
            'column_sort' => 1,
            'display_flg' => 1,
            'hp_id' => $hp->id,
            'page_id' => $page->id,
            'area_id' => ($areaId !== null) ? $areaId : null,
            $this->_columnMap['page_id'] => $this->getElement('page_id')->getValue(),
            $this->_columnMap['notification_type'] => $notification_type,
            $this->_columnMap['page_size'] => config('constants.original.DEFAULT_NOTIFICATION_PAGE_SIZE'),
            $this->_columnMap['cms_disable'] => config('constants.original.DEFAULT_NOTIFICATION_CMS_DISABLE'),
            $this->_columnMap['heading_type'] => 1
        ));

        $cmsDisable = $this->getField('cms_disable');

        $userClass = get_class(getInstanceUser('cms'));
        switch ($userClass) {
            case 'Library\Custom\User\Cms' :
                $isCMS = !getInstanceUser('cms')->isAgency();
                $allowKeys = array('page_size');
                if($isCMS){
                    if ($row->$cmsDisable == 1) {
                        $allowKeys = array();
                    }
                }
                break;
            case 'Library\Custom\User\Agency' :
                $allowKeys = array('page_size');
                break;
            case 'Library\Custom\User\Admin' :
                $allowKeys = array_keys($allFields);
                break;
        }

        // no field can update, return true as success
        if (empty($allowKeys)) {
            if ($areaId !== null) {
                $row->save();
            }
            return true;
        }

        foreach ($this->getElements() as $name => $element) {
            $value = $element->getValue();
            if (Util::isEmpty($value)) {
                continue;
            }

            if (!in_array($name, $allowKeys)) {
                continue;
            }

            if (isset($this->_columnMap[$name])) {
                $name = $this->_columnMap[$name];
            }

            $data[$name] = $value;
        }

        foreach ($data as $k => $v) {
            $row->$k = $v;
        }

        $row->save();
    }

    protected function getPartCode(){
        return HpMainPartsRepository::PARTS_INFO_LIST;;
    }

    public function getField($field){
        return $this->_columnMap[$field];
    }

    public function isValid($data, $checkError = true)
    {
        if($this->_isTop) {
            // $this->removeElement('sort');
            // $this->removeElement('column_sort');
            $this->sort->setRequired(false);
            $this->column_sort->setRequired(false);
            if (isset($data['id'])) {
                $table = $this->getSaveTable();
                $row = $table->fetchRow(array(
                    ['id', $data['id']],
                    ['parts_type_code', $this->getPartCode()]
                ));
                if (!$row) return false;
                $pageId = $this->getField('page_id');
                $data['page_id'] = $row->$pageId;
                $cmsDisable = $this->getField('cms_disable');
                $pageSize = $this->getField('page_size');
                $userClass = get_class(getInstanceUser('cms'));
                switch ($userClass) {
                    case 'Library\Custom\User\Cms' :
                        $isCMS = !getInstanceUser('cms')->isAgency();
                        if($isCMS){
                            if ($row->$cmsDisable == 1) {
                                $data['page_size'] = $row->$pageSize;
                            }
                        }
                        break;
                    case 'Library\Custom\User\Agency' :
                        //
                        break;
                    case 'Library\Custom\User\Admin' :
                        //
                        break;
                }

            }
        }

        return parent::isValid($data, $checkError);
    }

}