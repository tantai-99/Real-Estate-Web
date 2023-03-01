<?php

require_once(APPLICATION_PATH.'/../script/Theme/sp/_Abstract.php');

class Theme_House01 extends Theme_Abstract {

    public function run() {

        $this->customTag();
        return $this->doc->htmlOuter();
    }
}