<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Katame01 extends Theme_Abstract {

    public function run() {

        $this->moveCompanyinfo();

        $this->moveBreadcrumb();


        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }

    /**
     * "会社情報"を.inner直下から、.device-chageの後ろに移動
     *
     */
    private function moveCompanyinfo() {

        if ($this->doc['.company-info']->parent()->hasClass('inner')) {
            $this->doc['.device-change']->after($this->doc['.company-info']);
        }
    }

}