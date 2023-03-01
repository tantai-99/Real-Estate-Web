<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <?php echo $view->api->head; // title, meta?>
  <?php include('header/meta.blade.php'); ?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <script>
    var search_config = <?php echo $view->_config;?>;
  </script>

  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
    }
  </style>
</head>
<body>

<div id="panorama-canvas">
    <iframe src="<?php echo $view->api->content->panoramaUrl;?>" frameborder="0" scrolling="no" class="panorama-frame"></iframe>
</div>

<nav class="page_nav">
    <p class="panorama-link-back"><a href="<?php echo $view->detailUrl; ?>">詳細に戻る</a></p>
</nav>

</body>
</html>
