<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
$device = 'pc';

$scriptPath = $view->getScriptPath('script/sidearticlelink.blade.php');

//cms server
if ($view->isPreview && isset($view->pages[$view->pageId])) {
    // echo '<div class="side-article-preview"><img src="/images/page-edit/side_article_preview_'.$device.'.png" alt=""></div>';
    $publishType = $view->mode;
    $filename = $view->pages[$view->pageId]['filename'];
    $thisPage = $view->pages[$view->pageId];
    $themeId = $view->theme->id;
    $isTopOriginal = $view->isTopOriginal;
    $sideLayout = $view->hp->getSideLayout();
    $sidebarArticleLinkTitle = $sideLayout['5']['title'];
    $typeSetLink = $sideLayout['5']['type'];

    $pages = array();
    foreach ($view->pages as $page) {
        // 未作成を省く
        if ((!$page['public_flg']) || !in_array($page['page_category_code'], \App::make(HpPageRepositoryInterface::class)->getCategoryCodeArticle())) {
            continue;
        }
        $pages[] = $page;
    }

    include_once($scriptPath);
}
// gmo server
else {
    $sideLayout = $view->hp->getSideLayout();
    $sidebarArticleLinkTitle = $sideLayout['5']['title'];
    $typeSetLink = $sideLayout['5']['type'];

    echo '<?php $device = "'.$device.'" ;?>';
    echo '<?php $publishType = '.$view->mode.' ;?>';
    echo '<?php $themeId = '.$view->theme->id.' ;?>';
    echo '<?php $isTopOriginal = '.(int)$view->isTopOriginal.' ;?>';
    echo '<?php $sidebarArticleLinkTitle = "'.str_replace("\"", "\\\"", $sidebarArticleLinkTitle).'" ;?>';
    echo '<?php $typeSetLink = '. (int)$typeSetLink.' ;?>';

    echo file_get_contents($scriptPath);
};
?>
