<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Support\Facades\App;
use App\Rules\StringLength;
use Library\Custom\Model\Master\Theme;
use Library\Custom\Model\Master\Color;
use Library\Custom\Model\Master\Layout;
use App\Rules\Theme as ValidateTheme;

class Design extends Form {
	
	protected 	$_company_id		;
	protected 	$_messages			;
	
	public function __construct( $company_id = NULL )
	{
		$this->_company_id	= $company_id	;
		parent::__construct()	;
        $master =new Theme();
        $element = new Element\Radio('theme_id');
        $element->addValidator( new ValidateTheme( $this->_company_id ) ) ;
        $element->setLabel('テーマ');
        $element->setValueOptions($master->getOptions());
        
        $element->setValue($master->getRowset()[0]->id);
        $element->setRequired(true);

        $themeName = $master->getRowset()[0]->name;

        // カスタムテーマ
        $customTheme = Theme::getInstance()->getCustomRowsetByGroup(getInstanceUser('cms')->getProfile()->id);

        if (count($customTheme) > 0) {
            $row = $this->getFirstCustomTheme($customTheme);
            $element->setValue($row->id);
            $themeName = $row->name;
        }
        $this->add($element);

        $master = Color::getInstance();
        $element = new Element\Radio('color_id');
        $element->setLabel('ベースカラー');
        $element->setregisterInArrayValidator(false);
        $element->setValueOptions($master->getOptionsByTheme($themeName));
        $element->setValue($master->getFirstIdByTheme($themeName));
        $element->setRequired(true);
        $this->add($element);

        //カラー情報設定
        $min = 6;
        $max = 6;
        $element = new Element\Text('color_code');
        $element->setLabel('カラーコード');
        $element->addValidator(new StringLength(array($min, $max)));
        $element->setAttributes([
            'Required'=>true,
            'class' => array('watch-input-count'),
            'width' => '100px',
            'maxlength' => $max,

        ]);
        $this->add($element);
        
        //レイアウト設定
        $master = Layout::getInstance();
        $element = new Element\Radio('layout_id');
        $element->setLabel('レイアウト');
        $element->setregisterInArrayValidator(false);
        $element->setValueOptions($master->getOptionsByTheme($themeName));
        $element->setValue($master->getFirstIdByTheme($themeName));
        $element->setAttributes([
            'Required'=>true,

        ]);
        $this->add($element);
	}
	

    public function getMessages()
    {
    	$this->_messages = array() ;
    	
    	foreach ( $this->getElements() as $name => $element ) {
    		if ( !$element->hasErrors() ) {
    			continue ;
    		}
    		
    		$this->_messages[ $name ] = $this->getGroupErrors( array( $name ) ) ;
    	}
    	
    	return $this->_messages ;
    }
    
    public function isValid($data, $checkError = true): bool {
    	if (isset($data['theme_id'])) {
            $themeName = Theme::getInstance()->get($data['theme_id']);
            if(strpos($themeName, "custom_color") === false)
            $this->color_id->setValueOptions(Color::getInstance()->getOptionsByTheme($themeName));
            $this->layout_id->setValueOptions(Layout::getInstance()->getOptionsByTheme($themeName));
            
            // $data['color_id']=Color::getInstance()->getOptionsByTheme($themeName);
            // $data['layout_id']=(Layout::getInstance()->getOptionsByTheme($themeName));
        }
        return parent::isValid($data);
    }

    /**
     * 最初のカスタムテーマを取得
     *
     * @param $custom
     * @return mixed
     */
    private function getFirstCustomTheme($custom) {

        foreach ($custom as $rowset) {
            foreach ($rowset as $row) {
                return $row;
            }
        }
    }
}