<?php
namespace Library\Custom\Hp\Page\SectionParts;
use Library\Custom\Form;
use Library\Custom\Model\Lists\Original;

class SectionPartsAbstract extends Form {

	protected $_template;

	// protected $_pageType;
	protected $_hp;
    
    protected $_isLite;

	public function setHp($hp) {
	    $this->_hp = $hp;
	}

	public function getHp() {
		return $this->_hp;
	}

	/**
	 * @var App\Models\HpPage
	 */
	protected $_page;

	public function setPage($page) {
	    $this->_page = $page;
	}

	public function getPage() {
		return $this->_page;
	}

	public function getTemplate() {
		$class_name = get_class($this);
		if ($this->_template) {
			$template = $this->_template;
		}
		else {
			$template = strtolower(str_replace('Library\Custom\Hp\Page\SectionParts\\', '', $class_name));
		}

		return '_forms.hp-page.section-parts.'.$template;
	}

	public function save($hp, $page) {

	}

	public function getUsedImages() {
		return array();
	}
	
	public function getUsedFile2s() {
		return array();
    }
    
    protected function genCategory($category) {
        $list = array();
        if (count($category) > 0) {
            $list = array("0" => "設定なし");
            $original = new Original();
            $category_name = $original::$CATEGORY_COLUMN['title'];
            foreach ($category as $value) {
                $list[$value->id] = $value->{$category_name};
            }
        }
        return $list;
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