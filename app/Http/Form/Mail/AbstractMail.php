<?php
namespace App\Http\Form\Mail;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
    abstract class AbstractMail extends Form {

        public function init() {

            $this->setName('mail');

            for ($i = 1; $i <= 5; $i++) {
                $name = 'mailto'.$i;
                $element = new Element\Text($name);
                $element->setLabel('宛先メールアドレス');
                $element->setAttributes([
                    'class' => 'watch-input-count',
                    'maxlength' => '100',
                ]);
                if ($i == 1) {
                    $element->setRequired(true);
                }
                $element->addValidator(new StringLength(array('max' => 100,'min' =>0)));
                $this->add($element);
            }

            $element = new Element\Text('subject');
            $element->setLabel('メールの件名');
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '250',
            ]);
            $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 250,'min' =>0)));
            $this->add($element);

            //$this->useViewScript('mail.phtml');
        }


        public $_error;

        public function isValid($params,$checkErrors = true) {

            $validateFlg = parent::isValid($params);

            if (count($params) < 1) {
                return $validateFlg;
            }


            // 宛先はひとつ以上必須
            $errorFlg = true;
            foreach ($params['mail'] as $key => $val) {

                if (is_string(strstr($key, 'mailto')) && mb_strlen($val) > 0) {
                    $errorFlg = false;
                    break;
                };
            }
            if ($errorFlg) {
                $message = '宛先を最低ひとつ入力してください。';
                $this->setErrorMsg($message, 'mail', 'mailto1');
            }

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