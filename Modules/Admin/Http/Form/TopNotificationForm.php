<?php
namespace Modules\Admin\Http\Form;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\Original;
use Library\Custom\View\TopOriginalLang;
use App\Rules\NotEmpty;
use App\Rules\NewsCategoryClassName;
use Library\Custom\Hp\Page\Parts\PartsAbstract;
use Library\Custom\Util;
use Illuminate\Support\Facades\App;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Rules\InArray;
/**
 * Class Admin_Form_TopNotificationForm
 * @property int page_id
 * @property string title
 * @property string class
 */
class TopNotificationForm extends PartsAbstract {


    const NEWS_INDEX_ID = Original::NEWS_INDEX_ID;

    protected $lang;

    protected $classNameValidator;


    protected $_columnMap = array();

    public function getFillable() {
        return array_values($this->_columnMap);
    }

    /** @var App\Models\HpPage */
    protected $_pages;

    public function setPages($pages) {
        $this->_pages = $pages;
        return $this;
    }

    public function getPages() {
        return $this->_pages;
    }

    protected $parents = array();

    public function getParents(){
        return $this->parents;
    }

    protected $_settings = array();

    public function setSettings($settings) {
        $this->_settings = $settings;
        return $this;
    }

    protected $_id;

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    protected $_parentId;

    public function setParentId($parentId) {
        $this->_parentId = $parentId;
        return $this;
    }

    /**
     */
    public function init() {
        try{

            $original = new Original();
            $this->_columnMap = $original::$CATEGORY_COLUMN;

            $lang = new TopOriginalLang();

            // reset form decorators to remove the 'dl' wrapper
            // $this->setDecorators(['FormElements','Form']);

            $id = new Element\Hidden('id');
            $this->add($id);

            $title = (new Element\Text('title'));
            $title->setAttributes([
                'class' => 'create-form',
                'maxlength' => '30',
            ]);

            $notEmptyTitle = new NotEmpty(array('messages' => $lang->get('notification_settings.title.required')));
            $title->addValidator($notEmptyTitle);
            $this->add($title);




            $this->classNameValidator = new NewsCategoryClassName(array(
                'table' => $this
            ));

            $class = new Element\Text('class');
            $class->setAttribute('maxlength', '30');
            $class->setAttribute('placeholder', $lang->get('notification_settings.class.placeholder'));
            $class->setAttribute('class', 'create-form');

            $notEmpty = new NotEmpty(array('messages' => $lang->get('notification_settings.class.required')));

            $class->addValidator($notEmpty);

            $class->addValidator($this->classNameValidator);

            $this->add($class);


            $arr = array();
            foreach($this->_settings as $k => $v){
                $type = Original::$EXTEND_INFO_LIST['notification_type'];
                $pageId = Original::$EXTEND_INFO_LIST['page_id'];
                $arr[] = [ 'type' =>  $v->$type , 'page_id' =>  $v->$pageId ];
            }


            foreach($arr as $value){
                $this->parents[$value['page_id']] = $lang->get('notification_settings.create.type_'. $value['type']);
            }

            $ids =  array_keys($this->parents);

            $parent_page_id = new Element\Radio('parent_page_id');
            $parent_page_id->setRequired(true);
            $parent_page_id->setAttribute('class', 'create-form');
            $parent_page_id->setValueOptions($this->parents);
            $parent_page_id->setSeparator('');
            $parent_page_id->addValidator(new InArray($ids));

            if(isset($ids[0])){
                $parent_page_id->setValue($ids[0]);
            }
            
            $this->add($parent_page_id);


            // Add the submit button
            // $this->addElement('submit', 'submit', [
            //     'label'    => $lang->get('notification_settings.create.button'),
            //     'class' => 'btn-t-blue btn-noti-create',
            //     'type' => 'submit'
            // ]);

            // Add the submit button
            // $this->addElement('button', 'cancel', [
            //     'label'    => $lang->get('notification_settings.delete.cancel'),
            //     'class' => 'btn-t-gray modal-close-button',
            //     'type' => 'button'
            // ]);


            // $this->addElement('submit', 'update', [
            //     'label'    => $lang->get('notification_settings.update.button'),
            //     'class' => 'btn-t-gray btn-noti-update',
            //     'type' => 'submit'
            // ]);


            // $this->addElement('submit', 'delete', [
            //     'label'    => $lang->get('notification_settings.delete.button'),
            //     'class' => 'btn-t-gray btn-noti-delete',
            //     'type' => 'submit'
            // ]);


            // remove all decorators
            // $this->setElementDecorators([
            //     'ViewHelper',
            //     array('label', array('class' => '', 'placement' => 'APPEND')),
            // ]);
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function _setWherePartType(){
        return array(
            ['parts_type_code', $this->_getPartType()]
        );
    }

    public function _getPartType(){
        return HpMainPartsRepository::NEWS_CATEGORY;
    }

    public function saveData($extData = array()){

        $table = $table = $this->getSaveTable();

        $specKey = self::NEWS_INDEX_ID;

        $data = array();
        $lang = new TopOriginalLang();

        foreach ($this->getElements() as $name => $element) {
            $value = $element->getValue();
            if (Util::isEmpty($value)) {
                continue;
            }

            if (isset($this->_columnMap[$name])) {
                $name = $this->_columnMap[$name];
            }

            $data[$name] = $value;
        }

        $id = $this->getElement('id')->getValue();

        if($id && is_numeric($id)){

            $row = $table->fetchRow(array_merge(array(
                ['id', $id],
                ['hp_id', $this->_hp->id]
            ), $this->_setWherePartType()));

            if($row == null){
                throw new \Exception($lang->get( 'hp.no_current'));
            }

            //if change news id, empty sort;
            if($row->$specKey != $data[$specKey] ){
                $data['sort'] = null;
            }
        }
        else{
            $data['hp_id'] = $this->_hp->id;
            $data['parts_type_code'] = $this->_getPartType();
            $data['sort'] = null;
            $data['column_sort'] = 1;
            $data['area_id'] = 0;
            $data['page_id'] = 0;
            $row = $table->create($data);
        }


        if(!empty($extData)){
            $data = array_merge($data,$extData);
        }

        foreach($data as $k=>$v){
            $row->$k = $v;
        }

        $row->save();

        return $this->_returnData($row);
    }

    public function _returnData($data){
        $res = $this->_columnMap;
        foreach($res as $k => $v){
            $res[$k] = $data->$v;
        }
        $res['id'] = $data->id;
        return $res;
    }

    public function deleteData($id = null){
        $table = $this->getSaveTable();

        if($id == null){
            $id = $this->getElement('id')->getValue();
            if($id == null){
                throw new \Exception('No ID');
            }
        }

        $row = $table->fetchRow(array_merge( array(
            ['id', $id],
            ['hp_id', $this->_hp->id]
        ), $this->_setWherePartType()));
        $lang = new TopOriginalLang();

        if($row == null){
            throw new \Exception($lang->get( 'hp.no_current'));
        }
        $row->delete_flg = 1;
        $row->save();
    }


    /**
     * @param array $data
     * @param $hpId
     */
    public function massSort($hpId,array $data = array()){
        $table = $this->getSaveTable();
        foreach($data as $item){
            if(empty($item['rows'])) continue;
            foreach($item['rows'] as $sortKey => $id){
                $table->update(array_merge(array(
                    ['hp_id', $hpId],
                    ['id', $id]
                ), $this->_setWherePartType()), array('sort' => $sortKey));
            }
        }
    }

    /**
     * Check whenever classname is in use
     * @param $className
     * @param $id
     * @param $parentId
     * @return bool
     */
    public function inUseClassName($className, $id = null, $parentId = null){
        $table = App::make(HpMainPartsRepositoryInterface::class);
        $query = array_merge(array(
            ['hp_id', $this->_hp->id],
            [$this->_columnMap['class'], $className]
        ), $this->_setWherePartType());

        if($id != null){
            $query = array_merge($query, array(
                ['id', '<>', $id]
            ));
        }

        if($parentId != null){
            $query = array_merge($query, array(
                [$this->_columnMap['parent_page_id'], $parentId]
            ));
        }

        $res = $table->fetchRow($query);

        if($res) return true;

        return false;
    }

    public function checkInUseClassNameFromValidator($className){
        return $this->inUseClassName($className, $this->getElement('id')->getValue(), $this->getElement('parent_page_id')->getValue());
    }
}