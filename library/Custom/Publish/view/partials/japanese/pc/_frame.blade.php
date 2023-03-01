<?php
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
if(!is_object($view->page) ) {
  $view->page = new \stdClass();
  $view->page->page_type_code = 0;
}
$isContactPage   = in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]);
$isEstateContact = $hpPageTable->isEstateContactPageType($view->page->page_type_code);
?>
<?php if ($view->isMemberOnly) $view->includePartial('script_before_head', $view->{'script_before_head'}); ?>
<!DOCTYPE html>
<html lang="ja">

<?php echo $view->partial('common/_head.blade.php'); ?>

<body id="top" <?php if ($view->isTop) : ?>class="top"<?php endif; ?>>

<?php $tag = $view->fetch_tag; ?>
<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->under_body_tag) echo trim($tag->under_body_tag); ?>
<?php echo $view->partial('common/tag/_under_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>
<div class="bg">

<?php if ($view->hp->fb_like_button_flg || $view->hp->fb_timeline_flg) echo $view->partial('common/_fb_under_body.blade.php'); ?>

<?php $view->includePartial('header', $view->{'header'}); ?>
<?php if( !$isContactPage ) : ?>
<?php $view->includePartial('gnav', $view->{'gnav'}); ?>
<?php endif; ?>

<div class="contents<?php if( $isContactPage ) : ?> contents-form<?php endif; ?>">
    <div class="inner">

      <?php if (!$view->isTop && !$isContactPage) $view->includePartial('breadcrumb', $view->{'breadcrumb'}); ?>

      <?php
      // 物件お問い合わせ
      if ($isEstateContact) {
        $classAttr     = 'contents-main-1column';
      }
      // 通常お問い合わせ
      else if ($isContactPage) {
        $classAttr = 'contents-main';
      }
      else {
        $direction = 'right';
        if ($view->layout->name === 'right') {
          $direction = 'left';
        }
        $classAttr = "contents-main contents-{$direction}";
      }; ?>

      <div class="<?= $classAttr ?>" <?php if (!$isEstateContact): ?>role="main"<?php endif; ?>>
        <?php if (!$isEstateContact && !$isContactPage): ?>
        <div class="inner">
          <?php endif; ?>
          <?= $view->contents; ?>
          <?php if (!$isEstateContact && !$isContactPage): ?>
        </div>
      <?php endif; ?>
      </div>

        <?php if( !$isContactPage ) : ?>
        <div class="contents-side contents-<?php echo $view->layout->name; ?>" role="complementary">

            <?php $view->includeSide()->captureStart();?>
                <?php if (isset($view->page->page_type_code) && $view->page->page_type_code == HpPageRepository::TYPE_BLOG_INDEX) : ?>
                    <?php $name = 'sideblog_'.$view->page->id; ?>
                    <?php $view->includePartial($name, $view->{$name}); ?>
                <?php elseif (isset($view->page->page_type_code) && $view->page->page_type_code == HpPageRepository::TYPE_BLOG_DETAIL) : ?>
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
    </div>
    <p class="pagetop"><a href="#top">ページの先頭へ</a></p>
</div>

<?php if( !$isContactPage ) : ?>
<?php $view->includePartial('footernav', $view->{'footernav'}); ?>
<?php endif; ?>
<?php $view->includePartial('footer', $view->{'footer'}); ?>

</div>

<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->above_close_body_tag) echo trim($tag->above_close_body_tag); ?>
<?php echo $view->partial('common/tag/_above_close_body.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>
</body>
</html>
