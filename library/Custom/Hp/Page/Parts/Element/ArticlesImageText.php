<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules;
use Library\Custom\Model\Lists\LinkType;
use Library\Custom\Model\Master\PageList;
use Library\Custom\Util;

class ArticlesImageText extends ElementAbstract {

	protected $_columnMap = array(
		'image'			=> 'attr_1',
		'image_title'	=> 'attr_2',
		'art_link_type'		=> 'attr_3',
		'link_page_id'	=> 'attr_4',
		'link_url'		=> 'attr_5',
		'link_target_blank'	=> 'attr_6',
		'file2'				=> 'attr_7',
		'file2_title'		=> 'attr_8',
		'use_image_link' => 'attr_9',
		'description'	=> 'attr_10',
        'link_house'    => 'attr_11',
	);

	public function init() {
		parent::init();

		$element = new Element\Hidden('image', array('disableLoadDefaultDecorators'=>true));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('image_title');
		$element->setLabel('画像タイトル');
		$element->setAttributes(['class'=>'watch-input-count','maxlength'=>$max]);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$element = new Element\Checkbox('use_image_link');
		$element->setLabel('リンクを利用する');
		$element->setChecked();
		$element->setAttributes(array('class'=>'use-image-link'));
		$this->add($element);

		$element = new Element\Radio('art_link_type', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(LinkType::getInstance()->getAll());
		$element->setRequired(true);
		$element->setValue(1);
		$element->setSeparator("\n");
		$this->add($element);

		$element = new Element\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
		$this->add($element);

		$max = 2000;
		$element = new Element\Text('link_url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Rules\Url());
		
		$element->setAttributes(array('class'=>'watch-input-count','maxlength'=>$max));
		$this->add($element);

		$element = new Element\Button(	'link_file'		, array( 'disableLoadDefaultDecorators'	=> true	) ) ;
		$this->add( $element	) ;
		
		$element = new Element\Hidden(	'file2'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
		$this->add( $element ) ;
		
		$element = new Element\Checkbox('link_target_blank', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('別窓で開く');
        $element->setAttributes(['class' => array('ml-link-target-blank')]);
        $element->setChecked(true);
		$this->add($element);

		$element = new Element\Wysiwyg('description', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('rows',6);
        $this->add($element);
        
        if (!$this->isLite()) {
            $element = new Element\Hidden(	'link_house'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $this->add( $element ) ;

            $element = new Element\Radio('search_type', array('disableLoadDefaultDecorators'=>true));
            $element->setValueOptions(array('0' => '条件で探す', '1' => '物件番号で探す'));
			$element->setAttributes(array('class' =>'search-method'));
            $this->add($element);
            
            $element = new Element\Text('house_no');
            $element->setAttributes([
				'id'=>'house_no',
				'class' =>'input-house-no',
				'placeholder' => '物件番号（8・10・11桁）を入力してください'
			]);
            $this->add($element);

            $element = new Element\Hidden('link_house_type');
            $this->add($element);
        }

	}

	protected function _beforeSave( $data )
	{
		if ( $this->getElement('art_link_type')->getValue() != config('constants.link_type.FILE') )
		{
			$data[ $this->_columnMap[ 'file2' ] ] = null ;
        }
        if ( $this->getElement('art_link_type')->getValue() != config('constants.link_type.HOUSE')  )
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
			$data[ $this->_columnMap[ 'art_link_type' ] ] = 1 ;
			$data[ $this->_columnMap[ 'link_target_blank' ] ] = 0 ;
            $data[ $this->_columnMap[ 'file2' ] ] = null ;
            $data[ $this->_columnMap[ 'link_house' ] ] = null ;
		}
		return $data ;
	}
	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());
		if (!isEmptyKey($_data, 'image')) {
			$this->getElement('image_title')->setValidRequired(true);
		}
		$isValid =parent::isValid($data);
		if(array_key_exists('use_image_link',$_data) && $_data['use_image_link']){
			if($_data['art_link_type'] == config('constants.link_type.PAGE') && is_null($_data['link_page_id'])){
				$this->getElement('link_page_id')->setMessages(["ページを選択してください。"]);
				$isValid = false;
			} elseif($_data['art_link_type'] == config('constants.link_type.URL') && is_null($_data['link_url'])){
				$this->getElement('link_url')->setMessages(["URLを入力してください。"]);
				$isValid = false;
			} elseif($_data['art_link_type'] == config('constants.link_type.FILE') && is_null($_data['file2'])){
				$this->getElement('file2')->setMessages(["ファイルを追加してください。"]);
				$isValid = false;
			} elseif($_data['art_link_type'] == config('constants.link_type.HOUSE') && is_null($_data['link_house'])){
				$this->getElement('link_house')->setMessages(["物件を選択してください。"]);
                $isValid = false;
            }
		}
		return $isValid;
	}

	public function getUsedImages() {
		$images = array();
		if ($id = $this->getElement('image')->getValue()) {
			$images[] = $id;
		}
		return $images;
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
}