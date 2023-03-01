<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Validation\Factory as ValidatorFactory;
use Library\Custom\Model\Estate\AreaCategoryList;
use Library\Custom\Model\Estate\PrefCodeList;

class CompanySecondEstateArea extends Form {

    public function init() {

        //都道府県
		$areaCategory = AreaCategoryList::getInstance()->getAll();
		foreach ($areaCategory as $key => $value) {
			$element = new Element\Checkbox('pref'.$key);
			$element->setLabel($value);
			$element->setSeparator('');
			$options = PrefCodeList::getInstance()->getByArea($key);
			$element->setValueOptions($options);
            $this->add($element);

        }
    }

    public function isValid($params, $checkErrors = true) {

		$validFlg = true;
        $validFlg = parent::isValid($params);

		// 都道府県設定の必須チェック
        if(!array_key_exists('secondEstateArea', $params)){
			$this->setMessages( array("エリアが設定されていません。") );
			$validFlg = false;
        }
		return $validFlg;
	}

    public function simpleCheckBox($name, $echo = true) {
        $element = $this->getElement($name);
        $html =  '';
        $options = $element->getValueOptions();
        $selected = $element->getValue();
        reset($options);
        foreach($options as $value=>$label) {
            $html .= '<li><label>
            <input type="checkbox" name="secondEstateArea['.h($name).'][]" 
            id="secondEstateArea-'.h($name).'-'.h($value).'"
            value="'.h($value).'"'.(isset($selected) && in_array($value, $selected) ? ' checked="checked"' : '').'>'.h($label).'</label></li>';
        }
        echo $html;
    }

}