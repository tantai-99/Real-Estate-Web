<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Tegaki01 extends Theme_Abstract {

    public function run() {

        $this->addBgTree();

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }
    /**
     * "bg-tree"をbody配下に追加
     *
     */
    private function addBgTree() {
        $this->doc['body']->wrapInner('<div class="bg-tree">');
        /*
        $this->doc['body']->prepend('<div class="bg-tree">');
        $this->doc['.bg-tree']->append($this->doc['body']->children('#fb-root'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('.page-header'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('.gnav'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('.contents'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('.guide-nav'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('footer'));
        $this->doc['.bg-tree']->append($this->doc['body']->children('.copyright'));
        */
    }
}