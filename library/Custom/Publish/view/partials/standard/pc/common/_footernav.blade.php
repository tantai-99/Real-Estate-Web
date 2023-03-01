<?php
include_once($view->getScriptPath('script/footer.blade.php'));
$footer = new footer($view);
?>

<div class="guide-nav">
  <div class="inner">
    <?php foreach ($view->footernav as $page_id => $child): ?>
      <?php $page = $view->pages[$page_id];; ?>
      <div class="guide-nav-element">
        <p class="guide-nav-heading"><a <?= $view->hpHref($page); ?>><?php echo h($page['title']); ?></a>
        </p>
        <?php if (is_array($child) && count($child) > 0) : ?>
          <?php $footer->child($child, $view->pages); ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

