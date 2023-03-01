<?php
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Library\Custom\Model\Lists\InformationMainImageSlideShow;
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

<body id="top" <?php if ($view->isTop) : ?>class="top"<?php endif; ?>>

<?php $tag = $view->fetch_tag; ?>
<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->under_body_tag) echo trim($tag->under_body_tag); ?>
<?php echo $view->partial('common/tag/_under_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>

<?php if ($view->hp->fb_like_button_flg || $view->hp->fb_timeline_flg) echo $view->partial('common/_fb_under_body.blade.php'); ?>


<?php $view->includePartial('header', $view->{'header'}); ?>
<?php if( !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?>
<?php $view->includePartial('gnav', $view->{'gnav'}); ?>
<?php endif; ?>

<?php 
    $sliderThumb = '';
    if($view->page->page_type_code == HpPageRepository::TYPE_TOP &&
       $view->company->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')){
        if(isset($view->imageConfig) && $view->imageConfig->nav_slideshow == InformationMainImageSlideShow::NAVIGATION_THUMBNAIL){
            $sliderThumb = ' slider-thumb';
        }
    }
?>
<div class="contents<?php echo $sliderThumb; ?><?php if( in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?> contents-form<?php endif; ?>">
    <div class="inner">
<?php if (!$view->isTop && !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM])) $view->includePartial('breadcrumb', $view->{'breadcrumb'}); ?>

<?php if( $hpPageTable->isEstateContactPageType($view->page->page_type_code)): ?>
<div class="contents-main-1column">
<?php elseif( in_array($view->theme->name, array('standard02_custom_color','natural02_custom_color','simple02_custom_color')) && 
         $hpPageTable->getCategoryByType($view->page->page_type_code) == HpPageRepository::CATEGORY_FORM) : ?>
<div class="contents-main-1column">
<?php else: ?>
<div class="contents-main<?php if( !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?> contents-<?php echo $view->layout->name == 'right' ? 'left' : 'right'; ?><?php endif; ?>" role="main">
<?php endif; ?>
<?php echo $view->contents; ?>
</div>

    <?php if( !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?>
    <div class="contents-side contents-<?php echo $view->layout->name; ?>" role="complementary">

        <?php $view->includeSide()->captureStart();?>
            <?php if (isset($view->page->page_type_code) && 
                ($view->page->page_type_code == HpPageRepository::TYPE_BLOG_INDEX
                || $view->page->page_type_code == HpPageRepository::TYPE_COLUMN_INDEX)) : ?>
                <?php $name = 'sideblog_'.$view->page->id; ?>
                <?php $view->includePartial($name, $view->{$name}); ?>
            <?php elseif (isset($view->page->page_type_code) && 
                ($view->page->page_type_code == HpPageRepository::TYPE_BLOG_DETAIL
                || $view->page->page_type_code == HpPageRepository::TYPE_COLUMN_DETAIL)) : ?>
                <?php $name = 'sideblog_'.$view->page->parent_page_id; ?>
                <?php $view->includePartial($name, $view->{$name}); ?>
            <?php endif; ?>
            <?php $view->includePartial('sidenav', $view->{'sidenav'}); ?>
        <?php $view->includeSide()->captureEnd('other_link');?>
        <?php $view->includeSide()->captureStart();?>
            <?php if ($view->hasCommonSideParts || ($view->sideunique && !$view->isTop && !$view->isSitemap)):?>
            <div class="side-others">
                <?php $view->includePartial('sidecommon', $view->{'sidecommon'}); ?>
                <?php if (!$view->isTop && !$view->isSitemap) echo $view->sideunique; ?>
            </div>
            <?php endif;?>
        <?php $view->includeSide()->captureEnd('customized_contents');?>
        <?php $view->includeSide()->captureStart();?>
        <?php $view->includePartial('sidearticlelink', $view->{'sidearticlelink'}); ?>
        <?php $view->includeSide()->captureEnd('article_link');?>
        <?php $view->includeSide()->flush($view->side);?>

    </div>
    <?php endif; ?>
    <?php if (!$view->isTopOriginal): ?>
    <p class="pagetop"><a href="#top">ページの先頭へ</a></p>
    <?php endif; ?>
    </div>
</div>

<?php if( !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ) : ?>
<?php $view->includePartial('footernav', $view->{'footernav'}); ?>
<?php endif; ?>
<?php $view->includePartial('footer', $view->{'footer'}); ?>


<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->above_close_body_tag) echo trim($tag->above_close_body_tag); ?>
<?php echo $view->partial('common/tag/_above_close_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>

</body>
</html>
