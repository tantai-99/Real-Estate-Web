<?php
namespace Library\Custom\Hp\Page\SideParts\Element;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\LinkType;
use Library\Custom\Model\Master\PageList;
use App\Rules\StringLength;
use App\Rules\Url;

class Link extends SideEleAbstract {

	protected $_columnMap = array(
			'link_type'		=> 'attr_1',
			'link_page_id'	=> 'attr_2',
			'link_url'		=> 'attr_3',
			'link_label'	=> 'attr_4',
			'link_target_blank' => 'attr_5',
			'file2'				=> 'attr_6'	,
            'file2_title'		=> 'attr_7'	,
            'link_house'		=> 'attr_8'	,
			'link_house_title'	=> 'attr_9'	,
	);

	public function init() {

		parent::init();

		$element = new ELement\Radio('link_type', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(LinkType::getInstance()->getAll());
		$element->setRequired(true);
		$element->setValue(1);
		$element->setSeparator("\n");
		$this->add( $element ) ;
		
		$element = new ELement\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
		$element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
		$this->add( $element ) ;
		
		$max = 2000;
		$element = new ELement\Text('link_url', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(['min' => null,'max' => $max]));
		$element->addValidator(new Url());
        $element->setAttributes([
            'class' => 'watch-input-count',
            'placeholder' => 'URL',
            'data-maxlength' => $max
        ]);
		$this->add( $element ) ;
		
		$max = 100;
		$element = new ELement\Text('link_label', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new StringLength(['min' => null,'max' => $max]));
        $element->setAttributes([
            'class' => 'watch-input-count',
            'placeholder' => 'リンク名',
            'data-maxlength' => $max
        ]);
		$this->add( $element ) ;
		
		$element = new ELement\Button(	'link_file'		, array( 'disableLoadDefaultDecorators'	=> true	) ) ;
		$this->add( $element	) ;

		$element = new ELement\Hidden(	'file2'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
		$this->add( $element ) ;
		
		$max = 100 ;
		$element = new ELement\Text(		'file2_title'	, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
		$element->addValidator(new StringLength(['min' => null,'max' => $max])) ;
        $element->setAttributes([
            'class' => 'watch-input-count',
            'placeholder' => 'リンク名',
            'data-maxlength' => $max
        ]);
		$this->add( $element ) ;
		
		$element = new ELement\Checkbox('link_target_blank', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('別窓で開く');
        $element->setChecked(true);
        $this->add($element);
        
        if (!$this->isLite()) {
            $element = new ELement\Hidden(	'link_house'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $this->add( $element ) ;
            
            $max = 100 ;
            $element = new ELement\Text(		'link_house_title'	, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $element->addValidator(new StringLength(['min' => null,'max' => $max])) ;
            $element->setAttributes([
                'class' => 'watch-input-count',
                'placeholder' => 'リンク名',
                'data-maxlength' => $max
            ]);
            $this->add( $element ) ;

            $element = new ELement\Radio('search_type', array('disableLoadDefaultDecorators'=>true));
            $element->setValueOptions(array('0' => '条件で探す', '1' => '物件番号で探す'));
            $element->setAttribute('class', 'search-method');
            $this->add($element);
            
            $element = new ELement\Text('house_no');
            $element->setAttributes([
                'class' => 'input-house-no',
                'placeholder' => '物件番号（8・10・11桁）を入力してください',
                'id' => 'house_no'
            ]);
            $this->add($element);

            $element = new ELement\Hidden('link_house_type');
            $this->add($element);
        }
	}

	public function getUsedFile2s()
	{
		$result		= array()					;
		$file2id	= $this->getElement('file2')->getValue() 	;
        if ( ( $this->getElement('link_type')->getValue() == config('constants.link_type.FILE') ) && ( $file2id > 0 ) )
        {
            if ( $file2id ) {						// ファイルが指定されていたら
                $result	= array( $file2id )			;
            }
        }
		return $result	;
	}
	
	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

		if (isset($_data['link_type']) == true) {
			switch ( $_data['link_type'] ) {
			  case config('constants.link_type.PAGE'):
				$this->getElement('link_page_id')->setValidRequired(true);
				break ;
			  case config('constants.link_type.URL'):
			  	$this->getElement('link_url')->setValidRequired(true);
			  	break ;
			  case config('constants.link_type.FILE'):
                if($_data['file2'] == '') {
                    $this->getElement('file2')->setValidRequired(true) ;
                }
                break ;
              case config('constants.link_type.HOUSE'):
                if($_data['link_house'] == '') {
                    $this->getElement('link_house')->setValidRequired(true);
                }
                break ;
			}
		}

        $isValid = parent::isValid($data);
        if ($isValid) {
            if (array_key_exists('file2_title', $_data) && (string)$_data['file2_title'] == '') {
                $this->getElement('file2_title')->setMessages('リンク名を入力してください。');
                $isValid    = false     ;
            }
            if (array_key_exists('link_house_title', $_data) &&(string) $_data['link_house_title'] == '') {
                $this->getElement('link_house_title')->setMessages('リンク名を入力してください。');
                $isValid    = false     ;
            }
            if (array_key_exists('link_label', $_data) && (string)$_data['link_label'] == '') {
                    $this->getElement('link_label')->setMessages('リンク名を入力してください。');
                    $isValid	= false		;
            }

        }

        return $isValid;
	}

    protected function _beforeSave( $data )
    {
        if ( $this->getElement('link_type')->getValue() != config('constants.link_type.FILE') )
        {
            $data[ $this->_columnMap[ 'file2' ] ] = null ;
        }
        if ( $this->getElement('link_type')->getValue() != config('constants.link_type.HOUSE'))
        {
            $data[ $this->_columnMap[ 'link_house' ] ] = null ;
            $data[ $this->_columnMap[ 'link_house_title' ] ] = null ;
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

        return $data;
    }
}