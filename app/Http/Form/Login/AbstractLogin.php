<?php
namespace App\Http\Form\Login;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

abstract class AbstractLogin extends Form {

    public function init() {

        $this->setName('login');

        $element = new Element\Text('id');
        $element->setLabel('ID');
        $element->setAttributes([
            'class' => 'watch-input-count',
            'maxlength' => '100',
        ]);
        $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
        $this->add($element);

        $element = new Element\Password('password');
        $element->setLabel('パスワード');
        $element->setAttributes([
            'class' => 'watch-input-count',
            'maxlength' => '100',
        ]);
        $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
        $this->add($element);

        $element = new Element\Password('password_confirm');
        $element->setLabel('パスワード（確認）');
        $element->setAttributes([
            'class' => 'watch-input-count',
            'maxlength' => '100',
        ]);
        $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
        $this->add($element);

        $this->useViewScript('login.phtml');
    }


    public $_error;

    public function isValid($params,$checkError = true) {

        $validateFlg = parent::isValid($params);

        return $validateFlg;
    }

    /**
     * エラーメッセージをセット
     * $key = 要素のID属性と一致
     *
     * @param      $name
     * @param      $message
     * @param null $subSort
     */
    private function setErrorMsg($message, $area, $input) {

        $key = $area;
        $key .= '-';
        $key .= $input;

        $this->_error[$key] = array($message);

        return;
    }

    /**
     * エラーメッセージを取得
     * @return array
     */
    protected function getErrorMsg() {
        return $this->_error;
    }
}