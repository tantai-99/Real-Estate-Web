<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if ($part instanceof \Library\Custom\Hp\Page\Parts\ShopDetail) {
                $part->title = $page->form->getSubForm('tdk')->title;
                $parts[$page->getRow()->id] = $part;
            }
        }
    }
}

$index = 0;
$last_index = count($parts) - 1;
?>
<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php foreach ($parts as $page_id => $part): ?>
    <section>
        <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $part->title->getValue(), 'level' => 1, 'link' => $view->hpLink($part->getPage()->link_id), 'element' => null)) ?>
        <div class="element <?php if ($part->getValue('image1')) echo 'element-tximg1'; ?>">
            <?php if ($part->getValue('image1')): ?>
                <p class="element-right">
                    <img src="<?php echo $view->hpImage($part->getValue('image1')) ?>" alt="<?php echo $part->getValue('image1_title') ?>"/>
                </p>
            <?php endif ?>
            <?php if ($part->getValue('pr')): ?>
                <div class="element-left">
                    <?php echo $part->getValue('pr'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="element">
            <table class="element-table element-table1">
                <?php foreach ($part->elements->getSubForms() as $element): ?>
                    <?php if (!in_array($element->getValue('type'), ['adress', 'tel'])) continue ?>
                    <tr>
                        <th><?php // echo h($element->title ? $element->getValue('title') : $element->getTitle()) ?>
                            <?php  echo h($element->getTitle()) ?>
                        </th>
                        <td><?php echo h($element->getValue('value')) ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </section>
    <?php $index++ ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
