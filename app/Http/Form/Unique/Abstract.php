<?php

    abstract class Default_Form_Unique_Abstract extends Custom_Form {

        // area name
        public $_areaName;

        public function SetAreaName($areaName) {
            $this->_areaName = $areaName;
        }

        //area sort
        public $areaSort = 0;

        //column number
        public $columnNum = 1;

        //is display
        public $displayFlg = true;

        //part name
        public $partType;

        //input name attr
        public $partName;

        /**
         * 各要素のname属性を設定
         *
         * @return string
         */
        public function setNameAttr() {
            return $this->_areaName.'['.$this->partType.']['.$this->partName.']';
        }

        /**
         * 各要素のインスタンス生成時にidを設定
         *
         * @return string
         */
        public function getElem() {
            return $this->_areaName.'_'.$this->partType.'_'.$this->partName;
        }

        public $_error = array();

        /**
         * エラーメッセージ取得
         *
         * @return array
         */
        protected function getErrorMsg() {
            return $this->$_error;
        }
    }