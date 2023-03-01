<?php
namespace Library\Custom\Hp\Page\SectionParts\Element;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Url;
use Library\Custom\Model\Lists\LinkType;
use Library\Custom\Model\Master\PageList;

class MainImage extends Form {

	protected $_hp;
	protected $_page;
    protected $_data = array();
    protected $_isLite;

    function __construct($options, $data=false) {
        if($data){
            $this->_data = $data;
        }
        parent::__construct($options);

    }

    public function setDataCustom($flag=false){
        if($flag){
            $this->getElement('image')->setValue($this->_data['image']);
            $this->getElement('image_title')->setValue($this->_data['image_title']);
            $this->getElement('link_type')->setValue($this->_data['link_type']);
            $this->getElement('link_page_id')->setValue($this->_data['link_page_id']);
            $this->getElement('link_url')->setValue($this->_data['link_url']);
            $this->getElement('file2')->setValue($this->_data['file2']);
            // $this->getElement('file2_title')->setValue($this->_data['file2_title']);
            $this->getElement('link_target_blank')->setValue($this->_data['link_target_blank']);
            $this->getElement('sort')->setValue($this->_data['sort']);
            if (!$this->isLite()) {
                $this->getElement('link_house')->setValue($this->_data['link_house']);
                // $this->getElement('link_house_title')->setValue($this->_data['link_house_title']);
            }
        }
    }

	public function init() {

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'maxlength' => $max,
		]);
		// $element->class = array('watch-input-count');
		// $element->maxlength = $max;
		$this->add($element);

		$element = new Element\Radio('link_type', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(LinkType::getInstance()->getAll());
        $element->setAttribute('class', 'input-link_file');
        $element->setRequired(true);
		$element->setValue(1);
		$element->setSeparator("\n");
		$this->add($element);

		$element = new Element\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
		$this->add($element);

		$max = 2000;
		$element = new Element\Text('link_url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Url());
		// $element->class = array('watch-input-count');
		// $element->maxlength = $max;
		$element->setAttributes([
			'class' => 'watch-input-count',
			'maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Button(	'link_file'		, array( 'disableLoadDefaultDecorators'	=> true	) ) ;
		$this->add( $element	) ;
		
		$element = new Element\Hidden(	'file2'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
		$this->add( $element ) ;
		
		$element = new Element\Checkbox('link_target_blank', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('別窓で開く');
		$element->setAttribute('class', 'ml-link-target-blank');
		// $element->setChecked(true);
		$this->add($element);

		$element = new Element\Hidden('sort');
		$element->setRequired(true);
		$element->setAttribute('class', 'sort-value');
		// $element->class = array('sort-value');
		$this->add($element);

		$element = new Element\Checkbox('use_image');
		$element->setAttribute('class', 'use-image-link');
		// $element->class = array('use-image-link');

		$element->setLabel('リンクを利用する');
        $this->add($element);
        
        if (!$this->isLite()) {
            $element = new Element\Hidden(	'link_house'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $this->add( $element ) ;

            $element = new Element\Radio('search_type', array('disableLoadDefaultDecorators'=>true));
            $element->setValueOptions(array('0' => '条件で探す', '1' => '物件番号で探す'));
            $element->setAttribute('class', 'search-method');
            // $element->class = array('search-method');
            $this->add($element);
            
            $element = new Element\Text('house_no');
            $element->setAttributes([
            	'id' => 'house_no',
				'class' => 'input-house-no',
				'placeholder' => '物件番号（8・10・11桁）を入力してください',
			]);
            // $element->setAttribute('id', 'house_no');
            // $element->class = array('input-house-no');
            // $element->placeholder = '物件番号（8・10・11桁）を入力してください';
            $this->add($element);

            $element = new Element\Hidden('link_house_type');
            $this->add($element);
        }
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

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		// 画像が無い場合は保存しない
		if (!isset($_data['image']) || !$_data['image']) {
			return true;
		}

		return parent::isValid($data);
    }
    
    public function isLite() {
        if(!is_bool($this->_isLite)){
            $company = $this->_hp->fetchCompanyRow();
	        return $company->cms_plan < config('constants.cms_plan.CMS_PLAN_STANDARD');
        }
        return $this->_isLite;
        
    }
}