<div class="element">
    <p><?php echo $view->element->getValue('description') ?></p>
</div>

<?php foreach ($view->element->elements->getSubForms() as $item): ?>
<h3 class="heading-lv3"><span><?php echo h($item->getValue('title')) ?></span></h3>
<div class="element">
    <?php if ($item->getValue('image')): ?>
    <p class="element-img-left element-inline">
        <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
    </p>
    <?php endif ?>
    <p><?php echo $item->getValue('description') ?></p>
</div>
<?php endforeach ?>
