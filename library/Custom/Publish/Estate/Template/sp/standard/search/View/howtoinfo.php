<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include('header/meta.blade.php'); ?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <?php include('header/howToInfo.blade.php'); ?>
  <script>var search_config = <?php echo $view->_config;?>;</script>
</head>
<?php include("howtoinfo/{$view->tpl}.blade.php"); ?>
</html>