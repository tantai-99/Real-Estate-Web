<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if (!$part instanceof Library\Custom\Hp\Page\Parts\EventDetail) {
                continue;
            }
            foreach ($part->elements->getSubForms() as $event) {
                if (!isset($parts[$page->getRow()->id])) {
                    $parts[$page->getRow()->link_id] = array();
                }
                $parts[$page->getRow()->link_id][] = $event;
            }
        }
    }
}
$index = 0;
$last_index = count($parts) - 1;
?>
<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php foreach ($parts as $page_id => $part): ?>
    <?php foreach ($part as $event): ?>
        <section>
            <?php if ($event->getValue('heading')): ?>
            <?php echo $view->partial('main-parts/heading.blade.php', array('element' => $event, 'link' => $view->hpLink($page_id), 'level' => 1)) ?>
            <?php endif; ?>

            <div class="element <?php if ($event->getValue('image1')) echo 'element-tximg1'; ?>">
                <?php if ($event->getValue('image1')): ?>
                    <p class="element-right">
                        <img src="<?php echo $view->hpImage($event->getValue('image1')) ?>"  alt="<?php echo h($event->getValue('image1_title')) ?>"/>
                    </p>
                <?php endif ?>
                <div class="<?php if ($event->getValue('image1')) echo 'element-left' ?>">
                    <?php echo $event->getValue('comment') ?>
                </div>
            </div>

            <div class="element">
                <table class="element-table element-table1">
                    <?php if ($event->start->getValue() || $event->end->getValue()): ?>
                        <tr>
                            <th>
                                開催期間
                            </th>
                            <td>
                                <?php
                                if ($event->start->getValue()) {
                                    echo h($event->start->getValue());
                                }
                                if ($event->start->getValue() && $event->end->getValue()) {
                                    echo '〜';
                                }
                                if ($event->end->getValue()) {
                                    echo h($event->end->getValue());
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif ?>
                    <?php if ($event->adress->getValue()): ?>
                        <tr>
                            <th>
                                所在地
                            </th>
                            <td>
                                <?php echo h($event->adress->getValue()) ?>
                            </td>
                        </tr>
                    <?php endif ?>
                </table>
            </div>
        </section>
    <?php endforeach ?>
    <?php $index++ ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
