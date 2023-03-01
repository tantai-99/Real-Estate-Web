<?php
use Library\Custom\Publish\Render\AbstractRender;
$siteUrl = AbstractRender::protocol($view->mode).AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain;

if ($view->isPreview) {
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
  <div class="page-header-top">
    <div class="inner">
      <?php if ($view->isPreview): ?>
        <h1 class="tx-explain">
          <?php if (!$view->isTop): ?>
            <?php echo h('<<'. htmlspecialchars($page['title']).'>>'); ?>
          <?php endif; ?>
          <?= h($view->hp->outline); ?>
        </h1>
      <?php else : ?>
        <?= file_get_contents($view->getScriptPath('common/script/h1.blade.php')); ?>
      <?php endif; ?>
      <ul class="link">
        <li><a href="<?php echo $siteUrl; ?>/sitemap/">サイトマップ</a></li>
        <li><a <?php echo $view->hpHref($view->pageContact); ?> target="_blank">お問い合わせ</a></li>
      </ul>
      <?php if (count($view->getPublishEstateInstance()->estateTypes) > 0): ?>
        <ul class="link2">
          <li class="link2-fav"><a href="<?php echo $siteUrl; ?>/personal/favorite/">お気に入り物件</a></li>
          <li class="link2-history"><a href="<?php echo $siteUrl; ?>/personal/history/">最近見た物件</a></li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
  <div class="inner">
    <p class="logo">
      <a href="<?php echo $siteUrl; ?>">
        <?php if (h($view->hp->logo_pc)) : ?>
          <span class="company-img"><img src="<?php if ($view->isPreview): ?>/image/site-logo-pc?id=<?php echo $view->hp->logo_pc ?><?php else : ?>/images/logo_pc.<?php echo $view->siteImagePc->extension; ?><?php endif; ?>" alt="<?php echo h($view->hp->logo_pc_title); ?>"></span>
        <?php endif; ?>
        <?php if (h($view->hp->logo_pc_text)) : ?>
          <span class="company-tx"><?php echo h($view->hp->logo_pc_text); ?></span>
        <?php endif; ?>
      </a>
    </p>
    <?php if ($view->hp->fb_like_button_flg || $view->hp->tw_tweet_button_flg) : ?>
      <div class="header-sns">
        <?php if ($view->hp->fb_like_button_flg) : ?><?php echo $view->partial('common/_fblike.blade.php'); ?><?php endif; ?>
        <?php if ($view->hp->tw_tweet_button_flg) : ?><?php echo $view->partial('common/_tweetbtn.blade.php'); ?><?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if ($view->hp->tel || $view->hp->office_hour) : ?>
      <div class="header-info">
        <?php if ($view->hp->tel) : ?>
          <p class="tel"><?php echo h($view->hp->tel); ?></p>
        <?php endif; ?>
        <?php if ($view->hp->office_hour) : ?>
          <p class="time">営業時間/<?php echo h($view->hp->office_hour); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

