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
<?php $footernav_error = $this->viewHelper->includeCommonFile("footernav"); ?><?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>
<div class=loading style=display:none><img src=/pc/imgs/img_loading.gif alt=""></div>
<div class=fav-done-message><p class=heading>「お気に入り」に追加しました</p>
  <p class=tx1>保存した物件を見る場合は、ページ上部の<br>「お気に入り」からご覧いただけます。</p>
  <p class=tx2>
    <input type=checkbox id=fav-done-next><label for=fav-done-next>次回以降このメッセージを表示しない</label>
  </p>
  <p class=btn-close><a href=#>閉じる</a></p>
</div>
<div class="box-overlay"></div>
<?php echo $view->api->hidden; ?>

</body>
<?php $this->viewHelper->includeCommonFile("company_info"); ?>
</html>
