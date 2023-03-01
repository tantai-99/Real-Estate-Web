<?php

    class Default_Form_Unique_Company extends Default_Form_Unique_Abstract {


        public function init() {

            parent::init();

            $method = 'get'.ucfirst($this->_areaName);
            $this->$method();
        }

        public function isValid($params = array()) {

            $validateFlg = parent::isValid($params);

            if (count($params) < 1) {
                return $validateFlg;
            };

            $keys = array_keys($params);

            $target = array();
            foreach ($keys as $key) {
                if (strpos($key, 'free_', 0) === 0) {
                    $target[] = $key;
                }
            }

            // free text
            foreach ($target as $free) {
                $num = (int)str_replace('free_', '', $free);
                if (!$this->validFreetext($params[$free]['head'], $params[$free]['body'], $num)) {
                    $validateFlg = false;
                };
            }

            return $validateFlg;
        }

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


        // 保証協会の選択肢
        private $associations = array(
            '（公社）全国宅地建物取引業協会連合会', '（公社）不動産保証協会', '営業保証金供託',
        );

        /**
         * 会社概要form生成
         * @throws Zend_Form_Exception
         */
        public function getOutline() {

            $partsList = App_Model_DbTable_HpUniqueParts_Company::slave()->getPartsList();

            // init
            $this->areaSort = 0;
            $this->columnNum = 1;
            $this->displayFlg = true;

            $this->setAttrib('sort', $this->areaSort);
            $this->setAttrib('displayFlag', $this->displayFlg);

            /* 見出し */
            $this->partType = 'heading';

            // タイプ
            $this->partName = 'type';

            $element = new Zend_Form_Element_Select($this->getElem());
            $element->setAttrib('name', $this->setNameAttr());
            $element->addMultiOptions(array(
                'h1' => '大見出し', 'h2' => '中見出し', 'h3' => '小見出し'
            ));
            $this->addElement($element);

            // 見出し本文
            $this->partName = 'body';

            $element = new Zend_Form_Element_Text($this->getElem());
            $element->setAttrib('name', $this->setNameAttr());
            $element->setAttrib('class', array('watch-input-count'));
            $element->setAttrib('maxlength', 30);
            $element->setAttrib('placeholder', '会社概要');
            $element->addValidator(new Zend_Validate_StringLength(NULL, 5));
            $element->setValue('会社概要');
            $this->addElement($element);

            /* 会社名 〜 PRコメント */
            $Zend_Form_Element_XXX = 'Zend_Form_Element_Text';
            $maxlength = 30;

            $exclude = array(
                'heading', 'corp_logo', 'corp_image', 'free',
            );

            foreach ($partsList as $type => $jp) {

                if (is_numeric(array_search($type, $exclude))) {
                    continue;
                }

                $this->partType = $type;
                $this->partName = 'head';

                $element = new Zend_Form_Element_Text($this->getElem());
                $element->setAttrib('name', $this->setNameAttr());
                $element->setAttrib('class', array('watch-input-count'));
                $element->setAttrib('maxlength', 30);
                $element->setAttrib('placeholder', $jp);
                $element->addValidator(new Zend_Validate_StringLength(NULL, 30));
                $element->setValue($jp);
                $this->addElement($element);


                $this->partName = 'body';

                // 役員以降 => textarea
                if ($type == 'executive') {
                    $Zend_Form_Element_XXX = 'Zend_Form_Element_Textarea';
                    $maxlength = 250;
                }

                // 保証協会 => select
                if ($type == 'guarantee_association') {
                    $element = new Zend_Form_Element_Select($this->getElem());
                    $element->setAttrib('name', $this->setNameAttr());
                    $element->addMultiOptions($this->associations);
                    $this->addElement($element);
                    continue;
                }

                $element = new $Zend_Form_Element_XXX($this->getElem());
                $element->setAttrib('name', $this->setNameAttr());
                $element->setAttrib('class', array('watch-input-count'));
                $element->setAttrib('maxlength', $maxlength);
                $element->addValidator(new Zend_Validate_StringLength(NULL, 30));

                // textarea
                if ($Zend_Form_Element_XXX == 'Zend_Form_Element_Textarea') {
                    $element->addValidator(new Zend_Validate_StringLength(NULL, 250));
                    $element->setAttrib('rows', 6);
                }
                $this->addElement($element);
            }

            /* 会社ロゴ */
            $this->partType = 'logo';

            // id
            $this->partName = 'id';

            $element = new Zend_Form_Element_Hidden($this->getElem());
            $element->setAttrib('name', $this->setNameAttr());
            $element->setLabel('会社ロゴ');
            // $element->setAttrib('placeholder', '会社ロゴ');
            $this->addElement($element);


            /* 画像 */
            $this->partType = 'img';

            // id
            $this->partName = 'id';

            $element = new Zend_Form_Element_Hidden($this->getElem());
            $element->setAttrib('name', $this->setNameAttr());
            $element->setLabel('画像');
            // $element->setAttrib('placeholder', '画像');
            $this->addElement($element);

            //title
            $this->partName = 'title';

            $element = new Zend_Form_Element_Text($this->getElem());
            $element->setAttrib('name', $this->setNameAttr());
            $element->setAttrib('class', array('watch-input-count'));
            $element->setAttrib('maxlength', 30);
            $element->addValidator(new Zend_Validate_StringLength(NULL, 30));
            $this->addElement($element);

        }

        private function validFreetext($type, $text, $num) {

            $validateFlg = true;

            //@todo バリデーション処理

            //            if (true) {
            //                $message = 'hogehoge---------';
            //                $this->setErrorMsg($message, 'outline', 'free', 'body', $num);
            //                $validateFlg = false;
            //            }

            return $validateFlg;
        }

    }