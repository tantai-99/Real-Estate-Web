<?php

require_once(APPLICATION_PATH.'/../script/Theme/sp/_Abstract.php');

class Theme_Simple02CustomColor extends Theme_Abstract {

    public function run() {

        $this->customTag();
        return $this->doc->htmlOuter();
    }
}