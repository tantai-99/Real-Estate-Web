<?php
$elementClass = 'element-parts-list';
$outside_header = $view->area->getColumnCount() === 1;
if ($outside_header) {
    echo '<section>';
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element));
    $elementClass = 'element';
}
$isPreview = false;
if (getActionName() === 'previewPage') {
    $isPreview = true;
}

?>

    <div class="<?php echo $elementClass ?>">
        <?php if (!$outside_header) {
            echo '<section>';
            echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'inside_division' => true));
        }
        ?>
        <div class="<?php echo $view->area->getColumnCount() > 1 ? 'parts_map_canvas' : 'element-map-canvas' ?>"
             data-gmap-pin-lat="<?php echo $view->element->getValue('pin_lat') ?>"
             data-gmap-pin-long="<?php echo $view->element->getValue('pin_lng') ?>"
             data-gmap-center-lat="<?php echo $view->element->getValue('center_lat') ?>"
             data-gmap-center-long="<?php echo $view->element->getValue('center_lng') ?>"
             data-gmap-zoom="<?php echo $view->element->getValue('zoom') ?>"
             data-api-key="<?php echo $isPreview ? \Library\Custom\Hp\Map::getGooleMapKey() : \Library\Custom\Hp\Map::getGooleMapKeyForUserSite( $view->company ) ?>"
             data-api-channel="<?php echo \Library\Custom\Hp\Map::getGoogleMapChannel($view->company) ?>"
            ></div>
        <?php if (!$outside_header) {
            echo '</section>';
        }
        ?>
    </div>
<?php
if ($outside_header)
    echo '</section>';
?>