<?php foreach ($view->element->elements->getSubForms() as $el) : ?>
    <section class="sellingcase">
        <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $el->heading->getValue(), 'level' => 1, 'element' => null)); ?>

        <?php if ($el->getValue('comment')): ?>
            <div class="element comment">
                <p class="element-tx">
                    <?php echo $el->getValue('comment') ?>
                </p>
            </div>
        <?php endif ?>

        <?php echo $view->partial('main-parts/multi-images.blade.php', array('element' => $el)) ?>

        <div class="element">
            <table class="element-table element-table1">
                <tr>
                    <th>物件種目</th>
                    <td class="structure_type">
                        <?php echo implode('/', $view->optionValues($el->structure_type->getValueOptions(), $el->structure_type->getValue())) ?>
                    </td>
                </tr>
                <?php if ($el->getValue('adress')): ?>
                    <tr>
                        <th>所在地</th>
                        <td>
                            <?php echo h($el->getValue('adress')) ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($el->getValue('price')): ?>
                    <tr>
                        <th>売却価格</th>
                        <td class="price">
                            <?php echo h($el->getValue('price')) ?>円
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($el->layout->getValue()): ?>
                    <tr>
                        <th>間取り</th>
                        <td>
                            <?php echo h($el->getValue('rooms')) ?><?php echo implode('/', $view->optionValues($el->layout->getValueOptions(), $el->layout->getValue())) ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($el->getValue('area')): ?>
                    <tr>
                        <th>面積</th>
                        <td>
                            <?php echo h($el->getValue('area')) ?>ｍ2
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($el->getValue('age_of_a_building')): ?>
                    <tr>
                        <th>築年数</th>
                        <td>
                            <?php echo h($el->getValue('age_of_a_building')) ?>年
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($el->getValue('time')): ?>
                    <tr>
                        <th>時期</th>
                        <td>
                            <?php echo h($el->getValue('time')) ?>
                        </td>
                    </tr>
                <?php endif ?>
            </table>
        </div>
    </section>
<?php endforeach ?>

