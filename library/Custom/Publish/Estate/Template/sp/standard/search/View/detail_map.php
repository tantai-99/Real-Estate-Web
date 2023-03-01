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
  <?php if (isset($view->api->contentFDP)): ?>
  <?php $gmapApiChannel = $view->api->content->apiChannel;?>
  <?php $gmapApiChannel = ($gmapApiChannel) ? "&".$gmapApiChannel : "";?>
  <script src="https://maps.googleapis.com/maps/api/js?v=quarterly&<?php echo($view->api->content->apiKey.$gmapApiChannel) ?>"></script>
  <script type="text/javascript" src="/sp/js/fdp/map_label.js"></script>
  <?php endif; ?>
  <script>
    var search_config = <?php echo $view->_config;?>;
    var latlng = <?php echo json_encode(['lat' => $view->api->content->lat ? $view->api->content->lat : '35.792621', 'lng' => $view->api->content->lng ? $view->api->content->lng : '139.806513',]);?>;
  </script>

  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
    }
    #map-canvas {
      height: 85%;
    }
    .fixfooter{
      height: 15%;
    }
  </style>
</head>
<body>

<?php if (isset($view->api->contentFDP)): ?>
<div id="map-canvas-fdp" data-api-key="<?php echo $view->api->content->apiKey;?>" data-api-channel="<?php echo $view->api->content->apiChannel;?>"></div>
<nav class="page_nav">
  <p class="map-link-back"><a href="<?php echo $view->detailUrl; ?>">詳細に戻る</a></p>
  <p class="map-back-position"><a href="#">元の位置に戻る</a></p>
</nav>
<?php echo $view->api->contentFDP; ?>
    <?php if (isset($view->api->content->mapAnnotationText)): ?>
    <p class="fdp-annotation"><?php echo $view->api->content->mapAnnotationText;?><br>※周辺エリア情報は、情報更新のタイミングによっては、実際の情報と異なる場合があります。参考情報としてのご利用にとどめてください。</p>
    <?php else: ?>
    <p class="fdp-annotation">※地図上に表示されるアイコンは付近住所に所属することを表すものであり、実際の物件所在地とは異なる場合がございます。<br>※周辺エリア情報は、情報更新のタイミングによっては、実際の情報と異なる場合があります。参考情報としてのご利用にとどめてください。</p>
    <?php endif; ?>
<?php else: ?>
<div id="map-canvas" data-api-key="<?php echo $view->api->content->apiKey;?>" data-api-channel="<?php echo $view->api->content->apiChannel;?>"></div>
<nav class="page_nav">
  <p class="map-link-back"><a href="<?php echo $view->detailUrl; ?>">詳細に戻る</a></p>
  <p class="map-back-position"><a href="#">元の位置に戻る</a></p>
</nav>
<p class="fixfooter"><?php echo $view->api->content->mapAnnotationText;?></p>
<?php endif; ?>

</body>
</html>
