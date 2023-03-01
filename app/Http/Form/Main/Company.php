<?php
namespace App\Http\Form\Main;

    class Company extends AbstractMain {

        public function init() {

            parent::init();

        }

        /**
         * メインコンテンツの共通パーツをプリセットに設定
         *
         * @return string
         *
         */
        public function setDefaultCommonParts() {

            $col = 1;
            $types = array(1 => array('map'));

            $html = $this->setDefaultMainCommonParts($col, $types);

            return $html;
        }

    }