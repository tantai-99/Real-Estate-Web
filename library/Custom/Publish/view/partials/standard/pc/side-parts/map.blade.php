<?php 
$isPreview = false;
if (getActionName() === 'previewPage') {
    $isPreview = true;
}
?>
<?php if ($view->element->getValue('heading')) :?>
<section>
<h3 class="side-others-heading"><?php echo h($view->element->getValue('heading'))?></h3>
<?php endif ;?>
<div class="element-map-canvas" id="map_others_canvas"
     data-gmap-pin-lat="<?php echo $view->element->getValue('pin_lat') ?>"
     data-gmap-pin-long="<?php echo $view->element->getValue('pin_lng') ?>"
     data-gmap-center-lat="<?php echo $view->element->getValue('center_lat') ?>"
     data-gmap-center-long="<?php echo $view->element->getValue('center_lng') ?>"
     data-gmap-zoom="<?php echo $view->element->getValue('zoom') ?>"
     data-api-key="<?php echo $isPreview ? \Library\Custom\Hp\Map::getGooleMapKey() : \Library\Custom\Hp\Map::getGooleMapKeyForUserSite( $view->company ) ?>"
     data-api-channel="<?php echo \Library\Custom\Hp\Map::getGoogleMapChannel($view->company) ?>"
    ></div>
<?php if ($view->element->getValue('heading')) :?>
</section>
<?php endif ;?>