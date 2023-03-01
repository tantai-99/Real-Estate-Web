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

<div class="contents">
  <div class="inner">
    <?php $breadcrumb_error = $this->viewHelper->includeCommonFile("breadcrumb");?>
      <div class="contents-main-1column">
      <section>
        <h2 class="heading-lv1-1column">お問い合わせ エラー</h2>

      <section>
      <h3 class="heading-lv2-1column">サーバーに接続できません</h3>
      <div class="element element-form">
        <p class="form-complete-tx">
          ただいまシステム上のエラーがでております。<br>
          ご不便おかけして申し訳ございません。
        </p>
        <p class="tac btn-topback">
          <a href="/" class="btn-lv4">TOPに戻る</a>
        </p>
      </div>
      </section>
    </div>
</div>

<div class="guide-nav">
  <div class="inner"></div>
</div>
<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
</body>
</html>
