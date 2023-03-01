<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Natural extends Theme_Abstract {

    public function run() {

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }else{
            $this->addLeaf();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }

    /**
     * 'header'
     *
     */
    private function addLeaf() {
        $this->doc['body']->children('.page-header')->wrap('<div class="leaf">');
    }

}