<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Elegant extends Theme_Abstract {

    public function run() {

        $this->moveBreadcrumb();
        $this->addClassInnerApi();

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }
}