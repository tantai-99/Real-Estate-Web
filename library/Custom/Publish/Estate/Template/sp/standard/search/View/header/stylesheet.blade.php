<link rel="stylesheet" href="/sp/css/slick.css"/>
<link rel="stylesheet" href="/sp/css/style.css"/>
<link rel="stylesheet" href="/sp/css/freeword.css"/>
<?php if($view->color->name !== false) : ?>
<link rel="stylesheet" href="/sp/css/color-<?= $view->color->name ;?>.css" media="all" data-theme="<?= $view->theme->name ;?>" data-color="<?= $view->color->name ;?>" class="css-theme-color"/>
<?php else : ?>
<link rel="stylesheet" href="/sp/css/color-setting.css" media="all" data-theme="<?= $view->theme->name ;?>" data-color="<?= $view->color->name ;?>" class="css-theme-color"/>
<?php endif; ?>
<style>
  .gnav {
    display: none;
    position: absolute;
  }
</style>
