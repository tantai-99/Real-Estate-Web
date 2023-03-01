<section>
    <?php $element = $view->element; ?>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getTitle(), 'level' => 1, 'element' => null)) ?>

    <?php foreach ($element->elements->getSubForms() as $service): ?>
        <section>
            <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $service->getValue('name'), 'level' => 2, 'element' => null)) ?>
            <div class="element element-tximg1">
                <?php if ($service->getValue('image')): ?>
                    <p class="element-right">
                        <img src="<?php echo $view->hpImage($service->getValue('image')) ?>"  alt="<?php echo h($service->getValue('image_title')) ?>"/>
                    </p>
                <?php endif ?>

                <?php if ($service->getValue('description')): ?>
                    <div class="element-left">
                        <p><?php echo $service->getValue('description') ?></p>
                    </div>
                <?php endif ?>
            </div>

           <?php $item_count = count($service->elements->getSubForms()) ?>
            <?php foreach ($service->elements->getSubForms() as $key => $item): ?>
                <?php $item_count = $item_count - 1; ?>
                <?php // foreach ($items as $key => $item): ?>
                    <?php $line_class = $item_count > $key ? 'element-line' : '' ?>
                    <div class="element element-tximg6 <?php echo $line_class ?>">
                        <?php if ($item->getValue('image')): ?>
                            <div class="element-right">
                                <p>
                                    <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
                                </p>
                            </div>
                        <?php endif ?>
                        <div class="element-left">
                            <section>
                                <h4 class="element-heading"><?php echo h($item->getValue('name')) ?></h4>
                                <?php if ($item->getValue('description')): ?>
                                    <p><?php echo $item->getValue('description') ?></p>
                                <?php endif ?>
                            </section>
                        </div>
                    </div>
                <?php // endforeach ?>
            <?php endforeach ?>
        </section>
    <?php endforeach ?>
</section>