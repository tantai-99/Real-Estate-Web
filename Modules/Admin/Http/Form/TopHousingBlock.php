<?php
namespace Modules\Admin\Http\Form;
use Library\Custom\Form;
use Library\Custom\Form\Element;

class TopHousingBlock extends Form {

    public function init() {
        
        try{

            $fields = array('origin_id','alias','title','id','publish_status','is_public','type','create_special_date');
            foreach($fields as $field){
                $input = new Element\Hidden($field);
                $input->setAttribute('disabled','disabled');
                $this->add($input);
            }

        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}