<?php
namespace App\Http\Form\EstateSetting;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Estate;
use App\Rules\SpecialEstateFileName;

class Special extends Form {
	
	protected $_hpId = 0;
	protected $_settingId = 0;
	protected $_searchSettings;
	protected $_specialId = null;
    protected $_searchPage = null;
    protected $_searchType = null;
	
	public function setHpId($id) {
		$this->_hpId = $id;
		return $this;
	}
	
	public function setSettingId($id) {
		$this->_settingId = $id;
		return $this;
	}
	
	public function setSpecialId ($id) {
		$this->_specialId = $id;
		return $this;
	}

    public function setsearchPage($searchPage) {
        $this->_searchPage = $searchPage;
        return $this;
    }

    public function setsearchType($searchType) {
        $this->_searchType = $searchType;
        return $this;
    }
	
	public function setSearchSettings($searchSettngs) {
		$this->_searchSettings = $searchSettngs;
		return $this;
	}
	
	public function init() {
		//-------------------------------------
		// ページの基本設定
		//-------------------------------------
		
		$max = 20;
		$element = new Element\Text('title');
		$element->setAttributes([
			'maxlength' => $max,
		]);
		$element->setRequired(true);
		$element->addValidator(new StringLength(['min' => 0, 'max' =>$max]));
		$this->add($element);
		
		$max = 20;
		$element = new Element\Text('filename');
		$element->addValidator(new StringLength(array('max' => $max, 'messages' => ($max - 3) . ' 文字以内で入力してください。')));
		$element->setAttributes([
			'class' => 'name-special-en',
			'data-initial-count' => strlen('sp-'),
		]);
		$element->addValidator(new SpecialEstateFileName(['hpId'=>$this->_hpId, 'settingId'=>$this->_settingId, 'specialId'=>$this->_specialId]));
		$this->add($element);
		$max = 200;
		$element = new Element\Textarea('comment');
		$element->setAttributes([
			'class' => 'watch-input-count',
			'maxlength' => $max,
			'rows' => '4',
		]);
		$element->addValidator(new StringLength(['min' => 0, 'max' =>$max]));
		$this->add($element);
		
		//-------------------------------------
		// 特集の基本設定
		//-------------------------------------
        if ($this->_searchSettings) {
        	$estateClasses = [];
        	foreach ($this->_searchSettings as $key => $value) {
        		$estateClasses[] = $value->estate_class;
        	}
            $options = Estate\ClassList::getInstance()->pick($estateClasses);
        }
        else {
            $options = Estate\ClassList::getInstance()->getAll();
        }
        $element = new Element\Radio('estate_class');
        $element->setValueOptions($options);
        $element->setSeparator('<br>');
        $element->setRequired(true);
        $this->add($element);

		if ($this->_searchSettings) {
			$estateTypes = [];
        	foreach ($this->_searchSettings as $key => $value) {
        		$estateTypes = array_merge($estateTypes, $value->getEstateTypes());
        	}
			$options = Estate\TypeList::getInstance()->pick($estateTypes);
		}
		else {
			$options = Estate\TypeList::getInstance()->getAll();
		}
		$element = new Element\MultiCheckbox('enabled_estate_type');
		$element->setValueOptions($options);
		$element->setSeparator('');
		$element->setRequired(true);

		$this->add($element);
		
		
		$options = Estate\SpecialSearchPageTypeList::getInstance()->getAll();
		$element = new Element\Radio('has_search_page');
		$element->setValueOptions($options);
		$element->setSeparator('<br>');
        if ($this->_searchPage != 0) {
			$element->setRequired(true);
			
        }
		$this->add($element);
		
		$options = Estate\SearchTypeList::getInstance()->getAll();
		$element = new Element\MultiCheckbox('search_type');
		$element->setSeparator('</li><li>');
        if ($this->_searchPage != 0 && empty($this->_searchType)) {
            $element->setRequired(true);
        }
		$element->setValueOptions($options);
		$this->add($element);
		
        $options = Estate\SpecialMethodSetting::getInstance()->getAll();
        $element = new Element\Radio('method_setting');
        $element->setValueOptions($options);
        $element->setSeparator('<br>');
        $element->setRequired(true);
        $this->add($element);
	}
}