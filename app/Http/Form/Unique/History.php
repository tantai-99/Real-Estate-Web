<?php

    class Default_Form_Unique_History extends Default_Form_Unique_Abstract {

        public $mode;
        public $type;

        public function init() {

            parent::init();

            $method = 'get'.ucfirst($this->type);

            $this->$method();

        }

        public function isValid($params = array()) {

            parent::isValid($params);
        }



        /**
         * エラーメッセージをセット
         * $key = 要素のID属性と一致
         *
         * @param      $name
         * @param      $message
         * @param null $subSort
         */
        private function setErrorMsg($message, $areaName, $partsGroup, $partsType, $num = NULL) {

            $key = $areaName;
            $key .= '_';
            $key .= $partsGroup;
            $key .= '_';
            $key .= $partsType;
            if (is_numeric($num)) {
                $key .= '_';
                $key .= $num;
            }

            $this->_error[$key] = array($message);

            return;
        }

        /**
         * エラーメッセージを取得
         * @return array
         */
        protected function getErrorMsg() {
            return $this->$_error;
        }

        public function getHistory(){

        }

        public function SetMode($mode) {

            $this->mode = $mode;

        }

        public function SetType($type) {

            $this->type = $type;

        }

    }