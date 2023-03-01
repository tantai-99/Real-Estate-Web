<section>
    <?php $element = $view->element; ?>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getTitle(), 'level' => 1, 'element' => null)) ?>
    <?php foreach ($element->elements->getSubForms() as $el): ?>
        <section>
            <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $el->title->getValue(), 'level' => 2, 'element' => null)) ?>

            <?php if ($el->getValue('area') || $el->getValue('date')): ?>
                <div class="element">
                    <table class="element-table element-table1">
                        <?php if ($el->getValue('area')): ?>
                            <tr>
                                <th>エリア</th>
                                <td><?php echo h($el->getValue('area')) ?></td>
                            </tr>
                        <?php endif ?>
                        <?php if ($el->getValue('date')): ?>
                            <tr>
                                <th>日付</th>
                                <td><?php echo h($el->getValue('date')) ?></td>
                            </tr>
                        <?php endif ?>
                    </table>
                </div>
            <?php endif ?>

            <?php echo $view->partial('main-parts/multi-images.blade.php', array('element' => $el)) ?>

            <?php if ($el->getValue('review')): ?>
                <div class="element">
                    <p class="element-tx">
                        <?php echo $el->getValue('review') ?>
                    </p>
                </div>
            <?php endif ?>

            <?php if ($el->getValue('staff_comment') || $el->getValue('staff_comment')): ?>
                <div class="element element-comment">
                    <section>
                        <?php if ($el->getValue('staff_name')): ?>
                            <h4 class="element-heading"><?php echo h($el->getValue('staff_name')) ?></h4>
                        <?php endif ?>
                        <?php if ($el->getValue('staff_comment')): ?>
                            <p><?php echo $el->getValue('staff_comment') ?></p>
                        <?php endif ?>
                    </section>
                </div>
            <?php endif ?>
        </section>
    <?php endforeach ?>
</section>
