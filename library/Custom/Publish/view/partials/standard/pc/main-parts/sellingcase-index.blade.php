<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php foreach ($view->pages as $page): ?>
    <?php foreach ($page->form->getSubForm('main')->getSubForms() as $area): ?>
        <?php foreach ($area->parts->getSubForms() as $part): ?>
            <?php if (!$part instanceof \Library\Custom\Hp\Page\Parts\SellingcaseDetail) continue; ?>
            <?php foreach ($part->elements->getSubForms() as $el): ?>
                <?php if (!$el->getValue('heading')) continue; ?>
                <section>
                    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $el->getValue('heading'), 'level' => 1, 'link' => $view->hpLink($part->getPage()->link_id), 'element' => null)) ?>
                    <div class="element <?php if ($el->getValue('image1')) echo 'element-tximg1'; ?>">
                        <?php if ($el->getValue('image1')): ?>
                            <p class="element-right">
                                <img src="<?php echo $view->hpImage($el->getValue('image1')) ?>" alt="<?php echo $el->getValue('image1_title') ?>"/>
                            </p>
                        <?php endif ?>
                        <?php if ($el->getValue('comment')): ?>
                            <div class="<?php if ($el->getValue('image1')) echo 'element-left' ?>">
                                <?php echo $el->getValue('comment') ?>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="element">
                        <table class="element-table element-table1">
                            <tr>
                                <th>物件種目</th>
                                <td>
                                    <?php echo implode('/', $view->optionValues($el->structure_type->getValueOptions(), $el->structure_type->getValue())) ?>
                                </td>
                            </tr>
                            <?php if ($el->getValue('price')): ?>
                                <tr>
                                    <th>売却価格</th>
                                    <td>
                                        <?php echo h($el->getValue('price')) ?>円
                                    </td>
                                </tr>
                            <?php endif ?>
                        </table>
                    </div>
                </section>
            <?php endforeach ?>
        <?php endforeach ?>
    <?php endforeach ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
