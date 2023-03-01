<?php
namespace App\Http\Form\EstateSetting;
use Library\Custom\Form;
use Library\Custom\Model\Estate;
use Library\Custom\Form\Element;
use App\Rules\SpecialPublishEstate;
use App\Rules\SpecialTesuryoKokokuhi;

class SpecialMethod extends Form {

    protected $_hpId = 0;
    protected $_settingId = 0;
    protected $_searchSettings;
    protected $_specialId = null;
    protected $_method = null;
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

    public function setSearchSettings($searchSettngs) {
        $this->_searchSettings = $searchSettngs;
        return $this;
    }

    public function setMethod($method) {
        $this->_method = $method;
        return $this;
    }

    public function setSearchType($searchType) {
        $this->_searchType = $searchType;
        return $this;
    }

    public function init($searchSettings = null) {
        if ($this->_method == 1) {
            if ($this->_searchSettings) {
                $getPrefs = [];
                foreach ($this->_searchSettings as $key => $value) {
                    $getPrefs = $value->getPrefs();
                }
                $options = Estate\PrefCodeList::getInstance()->pick($getPrefs);
            }
            else {
                $options = Estate\PrefCodeList::getInstance()->getAll();
            }
            $element = new Element\Checkbox('pref');
            $element->setValueOptions($options);
            $element->setSeparator('');
            $element->setRequired(true);
            $this->add($element);

            $options = Estate\SearchTypeList::getInstance()->getAll();
            $element = new Element\Checkbox('search_type_method');
            $element->setSeparator('</li><li>');
            if ($this->_searchType != 1) {
                $element->setRequired(true);
            }
            $element->setValueOptions($options);
            $this->add($element);
        } else {
            $options = Estate\SearchTypeList::getInstance()->getAll();
            $element = new Element\Checkbox('search_type_method');
            $element->setSeparator('</li><li>');
            $element->setValueOptions($options);
            $this->add($element);   
        }

        $options = Estate\SpecialPublishEstateList::getInstance()->getAll();
        $element = new Element\Checkbox('publish_estate');
        $element->setValueOptions($options);
        $element->addValidator(new SpecialPublishEstate());
        $element->setSeparator('<br>');
        // $element->setIsArray(false);
        $this->add($element);

        $options = Estate\SpecialTesuryoKokokuhiList::getInstance()->getAll();
        $element = new Element\Checkbox('tesuryo_kokokuhi');
        $element->setValueOptions($options);
        $element->addValidator(new SpecialTesuryoKokokuhi());
        $element->setSeparator('<br>');
        $this->add($element);
    }
}