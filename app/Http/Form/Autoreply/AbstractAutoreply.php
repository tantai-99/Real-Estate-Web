<?php
namespace App\Http\Form\Autoreply;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

    abstract class AbstractAutoreply extends Form {

        public function init() {

            $this->setName('autoreply');

            $element = new Element\Checkbox('enabled');
            $element->setLabel('自動返信メールを有効にする');
            $this->add($element);

            $element = new Element\Text('mailfrom');
            $element->setLabel('差出人メールアドレス');
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '100',
            ]);
            $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
            $this->add($element);

            $element = new Element\Text('sender');
            $element->setLabel('差出人名');
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '100',
            ]);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
            $this->add($element);

            $element = new Element\Text('subject');
            $element->setLabel('メールの件名');
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '100',
            ]);
            $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
            $this->add($element);

            $element = new Element\Textarea('body');
            $element->setLabel('自動返信メールの本文');
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '100',
            ]);
            $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 500,'min' =>0)));
            $this->add($element);

            $this->useViewScript('autoreply.phtml');
        }


        public $_error;

        public function isValid($params,$check = true) {

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