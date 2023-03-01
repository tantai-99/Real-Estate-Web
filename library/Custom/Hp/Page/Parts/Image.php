<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\LinkType;
use Library\Custom\Model\Master\PageList;
use App\Rules\Digits;
use App\Rules\StringLength;
use App\Rules\Url;

class Image extends PartsAbstract {

	protected $_title = '画像';
	protected $_template = 'image';

	protected $_columnMap = array(
			'heading_type'	=> 'attr_1',
			'heading'		=> 'attr_2',
			'image'			=> 'attr_3',
			'image_title'	=> 'attr_4',
			'link_type'		=> 'attr_5',
			'link_page_id'	=> 'attr_6',
			'link_url'		=> 'attr_7',
			'link_target_blank'	=> 'attr_8',
			'file2'				=> 'attr_9'		,
			'file2_title'		=> 'attr_10'	,
			'use_image_link' => 'attr_11',
            'link_house'				=> 'attr_12'		,
			// 'link_house_title'		=> 'attr_13'		,
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
        $element->addValidator(new Digits());
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('画像のタイトル');
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->setAttributes([
			'class' => 'pin_lat watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Checkbox('use_image_link');
		$element->setLabel('リンクを利用する');
		// $element->setCheckedValue("1");
		$element->setAttribute('class', 'use-image-link');
		$this->add($element);

		$element = new Element\Radio('link_type', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(LinkType::getInstance()->getAll());
		$element->setRequired(true);
		$element->setValue(1);
		$element->setIsArray(false);
		$element->setSeparator("\n");
		$this->add($element);

		$element = new Element\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
		$this->add($element);

		$max = 2000;
		$element = new Element\Text('link_url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new Url());
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
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
        
        if (!$this->isLite()) {
            $element = new Element\Hidden(	'link_house'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $this->add( $element ) ;
            
            $element = new Element\Radio('search_type', array('disableLoadDefaultDecorators'=>true));
            $element->setValueOptions(array('0' => '条件で探す', '1' => '物件番号で探す'));
            $element->setAttribute('class', 'search-method');
            $this->add($element);
            
            $element = new Element\Text('house_no');
            $element->setAttributes([
				'class' => 'input-house-no',
				'placeholder' => '物件番号（8・10・11桁）を入力してください',
				'id' => 'house_no',
			]);
            $this->add($element);

            $element = new Element\Hidden('link_house_type');
            $this->add($element);
        }
	}

	public function getUsedFile2s()
	{
		$result		= array()					;
		$file2id	= $this->getElement('file2')->getValue() 	;
		if ( $file2id ) {						// ファイルが指定されていたら
			$result	= array( $file2id )			;
		}
		return $result	;
	}
	
	protected function _beforeSave( $data )
	{
		if ($this->getElement('link_type')->getValue() != config('constants.link_type.FILE') )
		{
			$data[ $this->_columnMap[ 'file2' ] ] = null ;
        }
        if ( $this->getElement('link_type')->getValue() != config('constants.link_type.HOUSE') )
		{
			$data[ $this->_columnMap[ 'link_house' ] ] = null ;
		} else {
            $url = $data[$this->_columnMap[ 'link_house' ]];
            $url = $this->isJson($url) ? $this->isJson($url) : $url;
            $linkHouse = array(
                'url' => $url,
                'search_type' => $data['search_type'] ? $data['search_type'] : 0,
                'house_no' => isset($data['house_no']) ? $data['house_no'] : null,
                'house_type' => isset($data['link_house_type']) ? explode(',', $data['link_house_type']) : null,
            );
            $data[$this->_columnMap[ 'link_house' ]] = json_encode($linkHouse); 
        }
        unset($data['search_type']);
        unset($data['house_no']);
        unset($data['link_house_type']);
		if ( $data[ $this->_columnMap[ 'use_image_link' ] ] == 0 )
		{
			$data[ $this->_columnMap[ 'link_type' ] ] = 1 ;
			$data[ $this->_columnMap[ 'link_target_blank' ] ] = 0 ;
            $data[ $this->_columnMap[ 'file2' ] ] = null ;
            $data[ $this->_columnMap[ 'link_house' ] ] = null ;
            // $data[ $this->_columnMap[ 'link_house_title' ] ] = null ;
        }
		return $data ;
	}

	public function getUsedImages() {
		return array($this->getElement('image')->getValue());
	}

	public function isValid($data, $checkError = true){

		if (array_key_exists('image', $data) && !array_key_exists('image_title', $data)) {
			$data['image_title'] = null;
			$this->getElement('image')->removeValidator('Digits');
		}

		$isValid =parent::isValid($data);

		if(array_key_exists('use_image_link', $data) && $data['use_image_link']){
			if (is_array($data["link_type"])) {
				$data["link_type"] = $data["link_type"][0];
			}
			if($data["link_type"] == config('constants.link_type.PAGE') && empty($data['link_page_id'])){
				$this->getElement('link_page_id')->setMessages(["ページを選択してください。"]);
				$isValid = false;
			} elseif($data["link_type"] == config('constants.link_type.URL') && empty($data['link_url'])){
				$this->getElement('link_url')->setMessages(["URLを入力してください。"]);
				$isValid = false;
			} elseif($data["link_type"] ==config('constants.link_type.FILE') && empty($data['file2'])){
				$this->getElement('file2')->setMessages(["ファイルを追加してください。"]);
				$isValid = false;
			}
            elseif($data["link_type"] == config('constants.link_type.HOUSE') && empty($data['link_house'])){
				$this->getElement('link_house')->setMessages(["物件を選択してください。"]);
                $isValid = false;
            }
		}
		return $isValid;
	}
}