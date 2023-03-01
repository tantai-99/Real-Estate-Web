<link rel="stylesheet" href="/pc/css/slick.css" media="all"/>
<link rel="stylesheet" href="/pc/css/style.css" media="all"/>
<link rel="stylesheet" href="/pc/css/freeword.css" media="all"/>
<?php if($view->color->name !== false) : ?>
<link rel="stylesheet" href="/pc/css/color-<?= $view->color->name ;?>.css" media="all" data-theme="<?= $view->theme->name ;?>" data-color="<?= $view->color->name ;?>" class="css-theme-color"/>
<?php else : ?>
<link rel="stylesheet" href="/pc/css/color-setting.css" media="all" data-theme="<?= $view->theme->name ;?>" data-color="<?= $view->color->name ;?>" class="css-theme-color"/>
<?php endif; ?>