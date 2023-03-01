<!DOCTYPE html>
<html lang="ja">

<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/meta.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/link.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/seoTag.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/stylesheet.blade.php'); ?>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/script.blade.php'); ?>
  <?php $gmapApiChannel = $view->apiConfig->get('gmap_api_channel');?>
  <?php $gmapApiChannel = ($gmapApiChannel) ? "&".$gmapApiChannel : "";?>
  <script src="https://maps.googleapis.com/maps/api/js?v=quarterly&<?php echo($view->apiConfig->get('gmap_api_id').$gmapApiChannel) ?>&libraries=geometry"></script>
  <script>var search_config = <?php echo $view->_config;?>;</script>
  <?php include(APPLICATION_PATH.'/sp/search/View/header/tag.blade.php'); ?>
</head>
<body id="top"><?php include(APPLICATION_PATH.'/sp/search/View/header/tag_under_body_tag.blade.php'); ?>

<div id="fb-root"></div>
<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>
<?php $gnav_error = $this->viewHelper->includeCommonFile("gnav"); ?>
<?php echo $view->api->content; ?>

<div class=loading style=display:none><img src=/pc/imgs/img_loading.gif alt=""></div>
<div class="box-overlay"></div>
<?php echo $view->api->hidden; ?>
<?php if($view->map_condition !== null){ ?>
  <input type="hidden" name="map_condition" value=true >
  <input type="hidden" name="center" value=<?php  echo $view->center?> >
  <input type="hidden" name="zoom" value=<?php  echo $view->zoom?> >
<?php } ?>
<?php include('header/tag_above_close_body_tag.blade.php'); ?> </body>
</html>
