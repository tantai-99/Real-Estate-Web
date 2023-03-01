<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class Testsite extends Form {

    private $releaseAtList = [];
    public  $_errors;

    public function init() {

        if (count($this->releaseAtList) < 1) {
            return;
        }

        $cnt = 0;
        foreach ($this->releaseAtList as $releaseAt) {
            $element = new Element\Radio('releaseAt'.++$cnt);
            $element->setAttribute('name', 'releaseAt');
            $element->setValueOptions(array($releaseAt => ''));
            if ($cnt == 1) {
                $element->setAttribute('checked', 'checked');
            }
            $this->add($element);
        }
    }

    public function setReleaseAtList($releaseAtList) {

        $this->releaseAtList = $releaseAtList;
    }

    public function isValid($params, $checkErrors = true) {

        $validateFlg = parent::isValid($params);

        return $validateFlg;

    }
}