<div class="breadcrumb">
    <?php
    $device = 'sp';
    $scriptPath = $view->getScriptPath('script/breadCrumb.blade.php');

    //cms server
    if ($view->isPreview && isset($view->pages[$view->pageId])) {

        $isTop = $view->isTop;
        $publishType = $view->mode;
        $filename = $view->pages[$view->pageId]['filename'];
        $pages = $view->pages;
        $thisPage = $view->pages[$view->pageId];
        include_once($scriptPath);
    }
    // gmo server
    else {
        echo '<?php $publishType = '.$view->mode.'; ?>';
        echo '<?php $device = "'.$device.'"; ?>';
        echo file_get_contents($scriptPath);
    }; ?>
</div>