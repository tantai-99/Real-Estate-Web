<?php

    class Default_Form_Unique extends Default_Form_Unique_Abstract {

        public function init() {
            parent::init();
            $this->useViewScript('unique.phtml');
        }
    }