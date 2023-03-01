<!DOCTYPE html>
<html lang="ja">

<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <script>var search_config = <?php echo $view->_config;?>;</script>
</head>
<body id="top">

<div id="fb-root"></div>
<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>
<?php $gnav_error = $this->viewHelper->includeCommonFile("gnav"); ?>
<?php echo $view->api->content; ?>
<?php $footernav_error = $this->viewHelper->includeCommonFile("footernav"); ?>
<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
</body>
</html>
