<?php
namespace Library\Custom\View\Helper;

class IncludePartial extends  HelperAbstract {

    public function includePartial($partial, $html = null) {

        if (getActionName() == 'previewPage') {

            echo $html;
            return;
        }

        echo '<?php $'.$partial.'_error = $this->viewHelper->includeCommonFile("'.$partial.'");?>';

    }

}
