<?php
$elementClass = 'element-parts-list';
$outside_header = $view->area->getColumnCount() === 1;
if ($outside_header) {
    echo '<section>';
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element));
    $elementClass = 'element';
}
?>
    <div class="<?php echo $elementClass ?>">
        <?php if (!$outside_header) {
            echo '<section>';
            echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'inside_division' => true));
        }
        ?>

        <div class="panorama">
            <?php echo $view->element->getValue('code') ?>
        </div>

        <?php if (!$outside_header) {
            echo '</section>';
        }
        ?>
    </div>
<?php
if ($outside_header)
    echo '</section>';
?>