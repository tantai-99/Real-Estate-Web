<?php foreach ($view->element->elements->getSubForms() as $category): ?>
    <section>
        <?php if ($category->category->getValue()): ?>
            <?php echo $view->partial('main-parts/heading.blade.php',
                array('heading' => implode('', $view->optionValues($category->category->getValueOptions(), $category->category->getValue())), 'level' => 1, 'element' => null)) ?>
        <?php endif ?>

        <div class="element element-qa">
            <?php foreach ($category->elements->getSubForms() as $qa): ?>
                <?php if (!$qa->getValue('q') && !$qa->getValue('a')) {
                    continue;
                }?>
                <dl>
                    <?php if ($qa->getValue('q')): ?>
                        <dt><span><?php echo h($qa->getValue('q')) ?></span></dt>
                    <?php endif ?>
                    <?php if ($qa->getValue('a') || $qa->getValue('image')): ?>
                        <dd>
                        <span class="element-a">
                            <?php echo $qa->getValue('a') ?>
                            <?php if ($qa->getValue('image')): ?>
                                <img src="<?php echo $view->hpImage($qa->getValue('image')) ?>" alt="<?php echo h($qa->getValue('image_title')) ?>"/>
                            <?php endif ?>
                        </span>
                        </dd>
                    <?php endif ?>
                </dl>
            <?php endforeach ?>
        </div>
    </section>
<?php endforeach ?>
