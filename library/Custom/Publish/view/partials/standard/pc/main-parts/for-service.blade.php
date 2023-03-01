<section>
    <?php $element = $view->element; ?>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getTitle(), 'level' => 1, 'element' => null)) ?>
    <?php foreach ($element->elements->getSubForms() as $item): ?>
        <section>
            <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $item->getValue('name'), 'level' => 2)) ?>
            <div class="element element-tximg1">
                <?php if ($item->getValue('image')): ?>
                    <p class="element-right">
                        <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
                    </p>
                <?php endif ?>

                <div class="element-left">
                    <p><?php echo $item->getValue('description') ?></p>
                </div>
            </div>
        </section>
    <?php endforeach ?>
</section>
