<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\Original;
use Library\Custom\View\TopOriginalLang;
use App\Rules\StringLength;
use App\Rules\InArray;
use App\Rules\NotEmpty;
use Library\Custom\Model\Lists\HpPagePartsHeadingType;
use Library\Custom\Util;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;

class PartsAbstract extends Form {

	protected $_is_unique = false;
	protected $_is_required = false;

	protected $_title;
	protected $_template;

	protected $_has_heading = true;


    /** @var App\Models\Hp */
	protected $_hp;

	protected $_page;

    /** @var App\Models\Company */
    protected $_company;

    protected $_isTopOriginal;

    /** @var Library\Custom\View\Helper\TopOriginalLang */
    protected $lang;

	protected $_typeName;

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
	);

	protected $_has_element = false;

	protected $_presetTypes = array();
	protected $_freeTypes   = array();

	protected $_required_force = array();
    
    protected $_isLite;

	public function init() {
		$class_name = get_class($this);
		$this->_typeName = strtolower(str_replace('Library\Custom\Hp\Page\Parts\\', '', $class_name));

		$element = new Element\Hidden('parts_type_code');
		$element->setRequired(true);
		$this->add($element);

		$element = new Element\Hidden('sort',array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('class', 'sort-value');
		$element->setRequired(true);
		$this->add($element);

		$element = new Element\Hidden('column_sort',array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$display_options = array();
		$display_options[1] = '表示';
		if (!$this->isRequired()) {
			$display_options[0] = '非表示';
		}
		
		$element = new Element\Hidden('display_flg',array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new InArray(array_keys($display_options)));
		$element->setAttribute('class', 'display-isDisplayItem');
		$element->setValue(1);
		$this->add($element);

		if ($this->hasHeading()) {
			$element = new Element\Select('heading_type',array('disableLoadDefaultDecorators'=>true));
			$element->setRequired(true);
			$element->setValueOptions(HpPagePartsHeadingType::getInstance()->getAll());
			$this->add($element);

			$max = 100;
			$element = new Element\Text('heading',array('disableLoadDefaultDecorators'=>true));
			$element->addValidator(new StringLength(array('max' => $max)));
			$element->setAttributes([
				'class' => 'watch-input-count',
				'data-maxlength' => $max,
			]);
			$this->add($element);
		}

		if ($this->hasElement()) {
			$this->addSubForm(new Form(), 'elements');
		}

        $lang = new TopOriginalLang();
	}

	public function hasElement() {
		return $this->_has_element;
	}

	public function getTemplate($prefix = '_forms.hp-page.parts.') {
		$template = $prefix;
		if ($this->_template) {
			$template .= $this->_template;
		}
		else {
			$template .= $this->_typeName;
		}
		return $template;
	}

	public function removeHeadingType() {
		if ($this->getElement('heading_type')) {
			$this->removeElement('heading_type');
		}
		return $this;
	}

	public function hasHeading() {
		return $this->_has_heading;
	}

	public function setHp($hp) {
		$this->_hp = $hp;
		return $this;
	}

	public function getHp() {
		return $this->_hp;
	}

	public function setPage($page) {
		$this->_page = $page;
		return $this;
	}

	public function getPage() {
		return $this->_page;
	}

	public function isUnique() {
		return $this->_is_unique;
	}

	public function createPartsElement($type, $elementNo, $assign = true) {
		$element = $this->_createPartsElement($type);

		if ($element) {
			$element->getElement('type')->setValue($type);
			$element->setIsPreset(in_array($type, $this->_presetTypes));
			$element->setName($elementNo);
			if ($assign) {
				$this->getSubForm('elements')->addSubForm($element, $elementNo);
			}
		}

		return $element;
	}

	protected function _createPartsElement($type) {
		return null;
	}

	public function getElementTypes() {
		return array_unique(array_merge($this->_presetTypes, $this->_freeTypes));
	}

	public function setPreset() {
		$no = 0;
		$types = $this->_presetTypes;
		foreach ($types as $key => $type) {
			$this->createPartsElement($type, $no++);
		}
		// while (list($key,$type) = each($types)) {
		// 	$this->createPartsElement($type, $no++);
		// }
		return $this;
	}

	public function forTemplate() {
		$no = 0;
		$types = array_merge($this->_presetTypes, $this->_freeTypes);
		foreach($types as $key => $type) {
			$this->createPartsElement($type, $no++);
		}
		// while (list($key,$type) = each($types)) {
			
		// }
		return $this;
	}

	public function setTitle($title) {
		$this->_title = $title;
		return $this;
	}

	public function getTitle() {
		return $this->_title;
	}

	public function setType($type) {
		$this->getElement('parts_type_code')->setValue($type);
		return $this;
	}

	public function getType() {
		return $this->getElement('parts_type_code')->getValue();
	}

	public function getTypeName() {
		return $this->_typeName;
	}

	public function setColumn($column) {
		$this->getElement('column_sort')->setValue($column);
		// $this->column_sort->setValue($column);
	}

	public function getColumn() {
		// return (int)$this->column_sort->getValue();
		return (int) $this->getElement('column_sort')->getValue();
	}

	public function isValid($data, $checkError = true) {
		
		if (!$this->isRequired()) {
			$ignore = array_merge(array('page_type_code', 'sort', 'column_sort', 'display_flg' ),$this->_required_force);
			foreach ($this->getElements() as $name => $element) {
				if (!in_array($name, $ignore) && ($element->isRequired() || $element->isValidRequired())) {
					NotEmpty::addToHpPagePartsElement($element);
				}
			}
		}

		if (!$this->hasElement()) {
			return parent::isValid($data, false);
		}


		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		// 一旦パーツをはずす
		// $elements = $this->elements;
		// $this->clearSubForms();

		$isValid = parent::isValid($data, false);

		// $this->addSubForm($elements, 'elements');

		$data = $_data;
		$subForms = $this->getSubForm('elements')->getSubForms();
		foreach ($subForms as $name => $form) {
			$isValid = $form->isValid(isset($data['elements'][$name])?$data['elements'][$name]:array(), false) && $isValid;
		}

		return $isValid;
	}

	public function getSaveTable() {
		return \App::make(HpMainPartsRepositoryInterface::class);
	}

	public function save($hp, $page, $areaId = null) {
		$data = array();

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
		if ($areaId !== null) {
			$data['area_id'] = $areaId;
		}
		$data['hp_id']   = $hp->id;
		$data['page_id'] = $page->id;

		$data = $this->_beforeSave($data);
		$table = $this->getSaveTable();
		$id = $table->create($data);

		if ($this->hasElement()) {
			$subForms = $this->getSubForm('elements')->getSubForms();
			foreach ($subForms as $name => $form) {
				$form->save($hp, $page, $id->id);
			}
		}
	}

	protected function _beforeSave($data) {
		return $data;
	}

	public function getUsedImages() {
		if (!$this->hasElement()) {
			return array();
		}

		$images = array();
		$subForms = $this->getSubForm('elements')->getSubForms();
		foreach ($subForms as $name => $form) {
			if ($_images = $form->getUsedImages()) {
				$images = array_merge($images, $_images);
			}
		}
		return $images;
	}

	public function getUsedFile2s()
	{
        // パーツがもつWYSIWYG要素のファイルID
        $file2s = $this->searchFile2IdFromWysiwyg($this);

		if ( !$this->hasElement() )
		{
			return $file2s ;
		}
		
		$subForms = $this->getSubForm('elements')->getSubForms();
		foreach ($subForms as $name => $form) {
			if ( $_file2s = $form->getUsedFile2s() )
			{
				$file2s = array_merge( $file2s, $_file2s ) ;
			}

			// サブパーツでなければ、WYSIWYG要素のファイルIDを取得
            if (!(get_class($form) == 'Library\Custom\Hp\Page\Parts\PartsAbstract')) {
                if ($_file2s = $this->searchFile2IdFromWysiwyg($form)) {
                    $file2s = array_merge( $file2s, $_file2s ) ;
                }
            }
		}
		return $file2s ;
	}

	protected function searchFile2IdFromWysiwyg($form) {
        $file2s = array();

        // パーツがもつWYSIWYG要素のファイルID
        $elements = $form->getElements();
        foreach ($elements as $name => $element) {
            $matches = array();
            if (get_class($element) == 'Library\Custom\Form\Element\Wysiwyg') {
                if (preg_match_all('/###link_file_id:(\d+)###/', $element->getValue(), $matches)) {
                    $file2s = array_merge( $file2s, array_unique($matches[1]) );
                }
            }
        }
        return $file2s;
    }
	
	public function setDefaults(array $values) {

		foreach ($this->_columnMap as $paramName => $colName) {
			if (isset($values[$colName])) {
				$values[$paramName] = $values[$colName];
			}
		}

		// parent::setDefaults($values);
	}
	
	public function isRequired() {
		return $this->_is_required;
	}
	
	public function setIsRequired($isRequired) {
		$this->_is_required = $isRequired;
	}

	public function getColumnMap() {
		return $this->_columnMap;
	}


    protected function renderSelect($data, $place_holder = false){
        $arr = [];
        for($i = 1; $i <= $data; $i++){
            $arr[(string)$i] =  $i;
        }
        return ($place_holder)
            ?array_replace(
                [ '' => $place_holder ],
                $arr
            )
            : $arr;
    }

    /**
     * @return App\Models\Company
     */
    protected function getCompany(){
    	$original = new Original();
        if($original->hasCompanyTopEvent()){
            $this->_company = $original->getCompanyTopEvent();
            return $this->_company;
        }
        if(empty($this->_company) || (!empty($this->_company) && !get_class($this->_company) == "App\Models\Company")){
            $this->_company = $this->_hp->fetchCompanyRow();
        }
        return $this->_company;
    }

    /**
     * @return bool
     */
    protected function _isTopOriginal(){
	    if(!is_bool($this->_isTopOriginal)){
	        $this->_isTopOriginal = $this->getCompany()->checkTopOriginal();
        }
        return $this->_isTopOriginal;
    }

    /**
     * @return bool
     */
    protected function isLite(){
	    if(!is_bool($this->_isLite)){
	        return $this->getCompany()->cms_plan < config('constants.cms_plan.CMS_PLAN_STANDARD');
        }
        return $this->_isLite;
    }


    /**
     * Disable Required Defaults
     * @param array $params
     * @return $this
     */
    public function disableDefault(array $params = array()){
        foreach($params as $v){
            if(isset($this->$v)){
                $this->$v->setRequired(false)->setValidators(array());
            }
        }
        return $this;
    }

    public function isJson($string) {
        if(is_string($string) && is_array($jsonData = json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            return $jsonData['url'];
        }

        return false;
    }
}