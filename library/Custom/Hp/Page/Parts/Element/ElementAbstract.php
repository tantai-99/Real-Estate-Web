<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\NotEmpty;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;
use App\Repositories\HpMainElementElement\HpMainElementElementRepositoryInterface;
use App\Rules\StringLength;

class ElementAbstract extends Form {

	protected $_is_unique = false;
	protected $_is_preset = false;
	protected $_is_sub_element = false;
	protected $_is_required = false;

	protected $_title;

	protected $_columnMap = array();
	protected $_required_force = array();
	
	protected $_hp;
	protected $_page;
    protected $_isLite;

	public function init() {
		// if (isset($settings)) {
		// 	if (isset($settings['hp'])) $this->setHp($settings['hp']);
		// 	if (isset($settings['page'])) $this->setPage($settings['page']);
		// }
		$element = new Element\Hidden('type', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$element = new Element\Hidden('sort', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('class', 'sort-value');
		$element->setRequired(true);
		$this->add($element);
	}

	public function getType() {
		return $this->getElement('type')->getValue();
	}

	public function setIsUnique($isUnique) {
		$this->_is_unique = $isUnique;
		return $this;
	}

	public function isUnique() {
		return $this->_is_unique;
	}

	public function setIsPreset($isPreset) {
		$this->_is_preset = $isPreset;
		return $this;
	}

	public function isPreset() {
		return $this->_is_preset;
	}
	
	public function isRequired() {
		return $this->_is_required;
	}
	
	public function isReplaceNotEmptyMessage() {
		return !$this->_is_required;
	}
	
	public function setRequired($isRequired) {
		$this->_is_required = $isRequired;
	}

	public function setIsSubElement($bool) {
		$this->_is_sub_element = $bool;
		return $this;
	}

	public function isSubElement() {
		return $this->_is_sub_element;
	}

	public function setTitle($title) {
		$this->_title = $title;
		return $this;
	}

	public function getTitle() {
		return $this->_title;
	}

	public function getSaveTable() {
		if ($this->isSubElement()) {
			return \App::make(HpMainElementElementRepositoryInterface::class);
		}
		else {
			return \App::make(HpMainElementRepositoryInterface::class);
		}
	}

	public function save($hp, $page, $partsId) {
		$data = array();

		foreach ($this->getValues() as $name => $value) {
			if (isEmpty($value)) {
				continue;
			}

			if (isset($this->_columnMap[$name])) {
				$name = $this->_columnMap[$name];
			}

			$data[$name] = $value;
		}

		$data['parts_id'] = $partsId;
		$data['hp_id']   = $hp->id;
		$data['page_id'] = $page->id;

        $data = $this->_beforeSave($data);
		$table = $this->getSaveTable();
		$id = $table->create($data);
	}

	public function getUsedImages() {
		return array();
	}

	public function getUsedFile2s()
	{
		return array();
	}
	
	public function setDefaults(array $values) {

		foreach ($this->_columnMap as $paramName => $colName) {
			if (isset($values[$colName])) {
				$values[$paramName] = $values[$colName];
			}
		}

		parent::setDefaults($values);
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
	
	public function isValid($data, $checkError = true) {
		if ($this->isReplaceNotEmptyMessage()) {
			$ignore = array_merge(array('type', 'sort'),$this->_required_force);
			foreach ($this->getElements() as $name => $elem) {
				if (!in_array($name, $ignore) && ($elem->isRequired() || $elem->isValidRequired())) {
					if($elem->getValue() !=='' && $elem->getValue() !== null && empty(trim($elem->getValue()))){
						$this->getElement($name)->setValidator([]);
					}
					NotEmpty::addToHpPagePartsElement($elem);
				}

			}
		}
		return parent::isValid($data);
	}

    protected function _beforeSave($data) {
		return $data;
	}
    
    public function isLite() {
        if(!is_bool($this->_isLite)){
            $company = $this->_hp->fetchCompanyRow();
            return $company->cms_plan < config('constants.cms_plan.CMS_PLAN_STANDARD');
        }
        return $this->_isLite;
        
    }

    public function isJson($string) {
        if(is_string($string) && is_array($jsonData = json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            return $jsonData['url'];
        }

        return false;
    }
}