<?php
use Library\Custom\Publish\Render\AbstractRender;
$siteUrl = AbstractRender::protocol($view->mode).AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain;

if ($view->isPreview){
    $page = $view->getPage($view->pages, $view->pageId);
}
$cmsini = getConfigs('cms');
?>
<?php if ($cmsini->header->mark->class):?>
	<?php if($cmsini->header->mark->label === '検証HP2'): ?>
		<div class="h-mark testing2"><?php echo $cmsini->header->mark->label ?></div>
	<?php else:?>
		<div class="h-mark <?php echo $cmsini->header->mark->class ?>"><?php echo $cmsini->header->mark->label ?></div>
	<?php endif;?>
<?php endif;?>
<header class="page-header" role="banner">
        <?php if ($view->isPreview): ?>
        <h1 class="tx-explain">
            <?php if (!$view->isTop) : ?><?php echo h('<<'. htmlspecialchars($page['title']).'>>'); ?><?php endif; ?>
            <?php echo h($view->hp->outline); ?>
        </h1>
        <?php else : ?>
            <?php echo file_get_contents($view->getScriptPath('script/h1.blade.php')); ?>
        <?php endif; ?>
    <div class="header-main">
        <p class="logo">
            <a href="<?php echo $siteUrl ;?>">
                <?php if (h($view->hp->logo_sp)) : ?><span class="company-img"><img src="<?php if ($view->isPreview): ?>/image/site-logo-sp?id=<?php echo $view->hp->logo_sp ?><?php else : ?>/images/logo_sp.<?php echo $view->siteImageSp->extension; ?><?php endif; ?>" alt="<?php echo h($view->hp->logo_sp_title); ?>"></span>
                <?php endif; ?>
                <?php if (h($view->hp->logo_sp_text)) : ?>
                    <span class="company-tx"><?php echo h($view->hp->logo_sp_text); ?></span>
                <?php endif; ?>
            </a>
        </p>
        <p class="header-menu"><span>メニュー</span></p>
    </div>
</header>