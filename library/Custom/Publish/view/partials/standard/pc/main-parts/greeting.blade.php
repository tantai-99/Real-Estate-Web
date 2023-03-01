<section>
    <?php $element = $view->element; ?>
    <?php echo $view->partial('main-parts/heading.blade.php',
        array(
            'heading' => $view->element->getValue('title') . ' ' . $view->element->getValue('signature'),
            'level' => 1, 'element' => null)) ?>

    <div class="element">
        <p class="element-img-right element-inline">
            <?php if($element->getValue('image')): ?>
                <img src="<?php echo $view->hpImage($element->getValue('image')) ?>"  alt="<?php echo h($element->getValue('image_title')) ?>"/>
            <?php endif ?>
        </p>
        <p><?php echo $element->getValue('text') ?></p>
    </div>
</section>
