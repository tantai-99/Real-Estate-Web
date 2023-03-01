<?php
namespace Modules\Admin\Http\Form;
use Library\Custom\Form;
use Library\Custom\Form\Element;

class TopFreewordSetting extends Form {

    public function init() {
        
        try{

            //company id
            $this->add(new Element\Hidden('company_id'));
            $this->add(new Element\Hidden('hd_id'));

        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function isValid($data, $checkError = true)
    {
        // 各配列(type_no, display_name, place_holder) が5つ
        if(count($data['type_no']) != 5 || count($data['display_name']) != 5 || count($data['place_holder']) != 5) {
            return false;
        }
        // type_no が 0〜4であること
        $typeNos = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
        ];
        foreach($data['type_no'] as $typeNo) {
            if(isset($typeNos[ $typeNo ]) && $typeNos[ $typeNo ] == 0) {
                $typeNos[ $typeNo ] = 1;
            } else {
                return false;
            }
        }
        // display_name の文字数(max:50)チェック
        foreach($data['display_name'] as $displayName) {
            $len = mb_strlen($displayName, 'UTF-8');
            if($len == 0 || $len > 50) {
                return false;
            }
        }
        // place_holder の文字数(max:100)チェック
        foreach($data['place_holder'] as $placeHolder) {
            $len = mb_strlen($placeHolder, 'UTF-8');
            if($len == 0 || $len > 100) {
                return false;
            }
        }
        
		return true;
    }
}