<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Publish\Estate\Make;

class GetPublishEstateInstance extends  HelperAbstract {

    public function getPublishEstateInstance() {

        $is_preview = getActionName() === 'previewPage';

        if ($is_preview) {
            return Make\Preview::getInstance();
        }

        return Make\Publish::getInstance();
    }
}