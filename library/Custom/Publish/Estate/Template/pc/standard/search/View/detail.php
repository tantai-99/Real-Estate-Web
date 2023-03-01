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

<!--hidden-->
<div class="fav-done-message">
  <p class="heading">「お気に入り」に追加しました</p>
  <p class="tx1">
    保存した物件を見る場合は、ページ上部の<br>
    「お気に入り」からご覧いただけます。
  </p>
  <p class="tx2">
    <input type="checkbox" id="fav-done-next"><label for="fav-done-next">次回以降このメッセージを表示しない</label>
  </p>
  <p class="btn-close"><a href="#">閉じる</a></p>
</div>

<div class="box-overlay" style="display:none;"></div>
<div class="floatbox gallery" style="top:50px;left:0;">
  <div class="gallery-view">
    <p class="tx-heading"></p>
    <p class="tx-caption"></p>
    <p class="count"></p>
    <?php echo $view->api->hidden; //p.photo-zoom ?>
  </div>
  <ul class="btn-move">
    <li class="prev"><a href="javascript:void(0)">前へ</a></li>
    <li class="next"><a href="javascript:void(0)">次へ</a></li>
  </ul>
  <p class="btn-close">閉じる</p>
</div>
<!--end hidden-->
 </body>
</html>
