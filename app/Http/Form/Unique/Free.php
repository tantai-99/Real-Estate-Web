<?php

    class Default_Form_Unique_Free extends Default_Form_Unique_Abstract {


        public function init() {

            parent::init();
        }

        public function isValid($params = array()) {

            $validateFlg = parent::isValid($params);
            return $validateFlg;
        }

        private function setErrorMsg() {

        }
    }