<?php
$images = [];
if ($view->element->getValue('image1') && $view->element->getValue('image1_title')) {
    $images[] = ['id' => $view->element->getValue('image1'), 'title' => $view->element->getValue('image1_title')];
}
if ($view->element->getValue('image2') && $view->element->getValue('image2_title')) {
    $images[] = ['id' => $view->element->getValue('image2'), 'title' => $view->element->getValue('image2_title')];
}

$image_count = count($images);
$element_class = $image_count > 1 ? 'element-' . $image_count . 'division' : 'tac';
?>
<?php if ($image_count > 0): ?>
    <div class="element <?php echo $element_class ?>">
        <?php foreach ($images as $image): ?>
            <?php if ($image_count > 1): ?>
                <div class="element-parts">
            <?php endif ?>

            <img src="<?php echo $view->hpImage($image['id']) ?>"  alt="<?php echo h($image['title']) ?>"/>

            <?php if ($image_count > 1): ?>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
