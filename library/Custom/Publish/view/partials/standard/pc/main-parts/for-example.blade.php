<section>
    <?php $element = $view->element; ?>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getTitle(), 'level' => 1, 'element' => null)) ?>

    <?php foreach ($element->elements->getSubForms() as $el): ?>
        <section>
            <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $el->title->getValue(), 'level' => 2, 'element' => null)) ?>
            <div class="element">
                <?php if ($el->getValue('image')): ?>
                    <p class="element-img-right element-inline">
                        <img src="<?php echo $view->hpImage($el->getValue('image')) ?>"  alt="<?php echo h($el->getValue('image_title')) ?>"/>
                    </p>
                <?php endif ?>
                <?php if ($el->getValue('description')): ?>
                    <p><?php echo $el->getValue('description') ?></p>
                <?php endif ?>
            </div>
        </section>
    <?php endforeach ?>
</section>
