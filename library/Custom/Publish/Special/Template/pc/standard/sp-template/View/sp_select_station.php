<!DOCTYPE html>
<html lang="ja">

<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include(APPLICATION_PATH.'/pc/search/View/header/link.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/pc/search/View/header/seoTag.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/pc/search/View/header/stylesheet.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/pc/search/View/header/script.blade.php'); ?>
  <script>var search_config = <?php echo $view->_config;?>;</script>
  <?php include(APPLICATION_PATH.'/pc/search/View/header/tag.blade.php'); ?>
</head>
<body id="top">
<?php include(APPLICATION_PATH.'/pc/search/View/header/tag_under_body_tag.blade.php'); ?>
<div id="fb-root"></div>
<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>

<?php $gnav_error = $this->viewHelper->includeCommonFile("gnav"); ?>

<?php echo $view->api->content; ?>

<?php $footernav_error = $this->viewHelper->includeCommonFile("footernav"); ?>
<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
<?php include(APPLICATION_PATH.'/pc/search/View/header/tag_above_close_body_tag.blade.php'); ?>
</body>
</html>
