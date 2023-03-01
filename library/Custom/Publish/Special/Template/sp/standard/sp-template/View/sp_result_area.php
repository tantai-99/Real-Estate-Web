<!DOCTYPE html>
<html lang="ja">
<head>
  <?= $view->api->head; // title, meta    ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/meta.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/link.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/seoTag.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/stylesheet.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/script.blade.php'); ?>
  <script>var search_config = <?= $view->_config;?>;</script>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/tag.blade.php'); ?>
</head>
<body class="top"><?php include(APPLICATION_PATH.'/sp/search/View/header/tag_under_body_tag.blade.php'); ?>
<div id="fb-root"></div>
<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>
<div class="articlelist-main">
  <?php $header_error = $this->viewHelper->includeCommonFile("gnav"); ?>
  <?= $view->api->content; ?>
</div>
<?php $this->viewHelper->includeCommonFile("company_info"); ?>
<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>
<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
<?php include(APPLICATION_PATH.'/sp/search/View/header/tag_above_close_body_tag.blade.php'); ?></body>
</html>
