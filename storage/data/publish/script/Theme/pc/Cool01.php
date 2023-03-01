<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Cool01 extends Theme_Abstract {

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

    /**
     * パンくず要素を.inner配下から、.gnav直下の位置に移動
     *
     */
    protected function moveBreadcrumb() {

        if ($this->doc['.breadcrumb']->parent()->hasClass('inner')) {
            $this->doc['.gnav']->after($this->doc['.breadcrumb']);
        }
    }

    /**
     * .contents.inner の classに inner-api を追加
     */
    protected function addClassInnerApi() {

        $this->doc['.contents']->children('.inner')->addClass('inner-api');

    }
}