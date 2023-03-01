<nav class="gnav" role="navigation">
  <ul class="nav-count<?php echo count($view->gnav); ?>">
    <?php foreach ($view->gnav as $page) : ?>
      <li <?php if (mb_strlen($page['title']) >= 10) : ?>class="fs-small"<?php endif; ?>>
        <a <?= $view->hpHref($page); ?>><?php echo h($page['title']); ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
