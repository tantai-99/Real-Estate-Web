<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class SecondEstateOther extends Form {
    
    public function init() {

        //その他情報枠 #########################
        //備考
        $element = new Element\Textarea( 'remarks' ) ;
        $element->setLabel( '備考' ) ;
        $element->setAttributes( array( 'rows' => 5 ) ) ;
        $element->addValidator( new StringLength( array( 'max' => 1000 ) ) ) ;
        $this->add( $element ) ;
    }
}
