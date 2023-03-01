<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include('header/meta.blade.php'); ?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <script>var search_config = <?php echo $view->_config;?>;</script>
</head>
<body class="top">

<div id="fb-root"></div>

<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>

<div class="contents">
  <?php $header_error = $this->viewHelper->includeCommonFile("gnav"); ?>
  <h2 class="heading-lv1"><span>お問い合わせ エラー</span></h2>
  <div class="element element-error">
    <h3 class="element-error-heading">サーバーに接続できません</h3>
    <p class="element-error-tx">
      ただいまシステム上のエラーがでております。<br>
      ご不便おかけして申し訳ございません。
    </p>
  </div>
</div>

<?php $this->viewHelper->includeCommonFile("company_info"); ?>

<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>

<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
</body>
</html>
