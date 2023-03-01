<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
$device = 'pc';
$scriptPath = $view->getScriptPath('script/sidenav.blade.php');
//cms server
if ($view->isPreview && isset($view->pages[$view->pageId])) {

    $publishType = $view->mode;
    $filename = $view->pages[$view->pageId]['filename'];
    $thisPage = $view->pages[$view->pageId];
    $themeId = $view->theme->id;
    $isTopOriginal = $view->isTopOriginal;
    $sideLayout = $view->hp->getSideLayout();
    $sidebarOtherLinkTitle = $sideLayout['3']['title'];

    $pages = array();
    foreach ($view->pages as $page) {
        // 未作成を省く
        if (($page['new_flg'] && !$page['public_flg']) || in_array($page['page_category_code'], \App::make(HpPageRepositoryInterface::class)->getCategoryCodeArticle())) {
            continue;
        }
        $pages[] = $page;
    }
    include_once($scriptPath);

}
// gmo server
else {
    $sideLayout = $view->hp->getSideLayout();
    $sidebarOtherLinkTitle = $sideLayout['3']['title'];

    echo '<?php $device = "'.$device.'" ;?>';
    echo '<?php $publishType = '.$view->mode.' ;?>';
    echo '<?php $themeId = '.$view->theme->id.' ;?>';
    echo '<?php $isTopOriginal = '.(int)$view->isTopOriginal.' ;?>';
    echo '<?php $sidebarOtherLinkTitle = "'.str_replace("\"", "\\\"", $sidebarOtherLinkTitle).'" ;?>';

    echo file_get_contents($scriptPath);
};
?>
