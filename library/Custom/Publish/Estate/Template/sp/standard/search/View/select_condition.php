<!DOCTYPE html>
<html lang="ja">
<head>
  <?= $view->api->head; // title, meta?>
  <?php include('header/meta.blade.php'); ?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <script>var search_config = <?= $view->_config;?>;</script>
</head>
<body class="top btn-footer">

<div id="fb-root"></div>

<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>
<div class="contents">
  <?php $header_error = $this->viewHelper->includeCommonFile("gnav"); ?>
  <?= $view->api->content; ?>
  <div class="fixed-pagefooter btn-search-submit">
    <?php if ($view->api->display_freeword) :?>
    <ul class="inline">
        <li class="fulltext_count">
            <i>0</i>
            <i>0</i>
            <i>0</i>
            <i>0</i>
            <i>0</i>
        </li>
        <span class="number_txt">件</span>
    </ul>
    <?php endif;?>
    <ul>
      <li class="btn-lv3"><a href="<?= "{$view->backUrl}"; ?>">検索</a></li>
    </ul>
  </div>
</div>

<?php $this->viewHelper->includeCommonFile("company_info"); ?>

<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>

<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>

</body>
</html>
