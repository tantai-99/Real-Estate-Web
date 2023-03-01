<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
?>
<?php if ($view->isMemberOnly) $view->includePartial('script_before_head', $view->{'script_before_head'}); ?>
<?php if(!is_object($view->page) ) {
$view->page = new \stdClass();
$view->page->page_type_code = 0;
}?>
<!DOCTYPE html>
<html lang="ja">
<?php echo $view->partial('common/_head.blade.php'); ?>
<?php if( in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?>
<body class="form-page">
<?php else : ?>
<body class="top sptop">
<?php endif; ?>

<?php $tag = $view->fetch_tag; ?>
<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->under_body_tag) echo trim($tag->under_body_tag); ?>
<?php echo $view->partial('common/tag/_under_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>

<?php if ($view->hp->fb_like_button_flg || $view->hp->fb_timeline_flg) echo $view->partial('common/_fb_under_body.blade.php'); ?>

<?php $view->includePartial('header', $view->{'header'}); ?>

<div class="contents">
    <?php $view->includePartial('gnav', $view->{'gnav'}); ?>
    <?php if (!$view->isTop && !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM])) : ?>
        <?php $view->includePartial('breadcrumb', $view->{'breadcrumb'}); ?>
    <?php endif; ?>
    <?php echo $view->contents; ?>
    <!-- /contents -->
</div>

<?php if( !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?>

    <?php $view->includeSide()->captureStart();?>
        <?php if ($view->hasCommonSidePartsSp || $view->sideunique)  : ?>
        <div class="contents-side">
            <?php $view->includePartial('sidecommon', $view->{'sidecommon'}); ?>
            <?php if (!$view->isTop && !$view->isSitemap) echo $view->sideunique; ?>
        </div>
        <?php endif; ?>
    <?php $view->includeSide()->captureEnd('customized_contents');?>
    <?php $view->includeSide()->captureStart();?>
    <?php if (!$view->isTop) : ?>
        <?php $view->includePartial('sidenav', $view->{'sidenav'}); ?>
    <?php endif; ?>
    <?php $view->includeSide()->captureEnd('other_link');?>
    <?php $view->includeSide()->captureStart();?>
    <?php $view->includePartial('sidearticlelink', $view->{'sidearticlelink'}); ?>
    <?php $view->includeSide()->captureEnd('article_link');?>
    <?php $view->includeSide()->flush($view->side);?>

<?php endif; ?>

<?php $view->includePartial('company_info', $view->{'company_info'}); ?>

<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>

<?php $view->includePartial('footer', $view->{'footer'}); ?>

<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->above_close_body_tag) echo trim($tag->above_close_body_tag); ?>
<?php echo $view->partial('common/tag/_above_close_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>
</body>
</html>
