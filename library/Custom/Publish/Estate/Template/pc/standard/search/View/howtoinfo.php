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
<div class="howto-see">
  <h1 class="tx-explain">情報の見方ページ</h1>
  <p class="company-name"><span><?php echo $view->company ?></span></p>
  <?php  ?>
  <?php include("howtoinfo/{$view->tpl}.html"); ?>
  <footer class="cr">
    <p>
      <small><?php echo $view->copyright ?></small>
    </p>
  </footer>

</div>
</body>
</html>
