<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if ($part instanceof \Library\Custom\Hp\Page\Parts\CustomervoiceDetail) {
                $parts[$page->getRow()->id] = $part;
            }
        }
    }
}
?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php
$index = 0;
$last_index = count($parts) - 1;
?>
<?php foreach ($parts as $page_id => $part): ?>
    <div class="element element-tximg7 <?php if ($index !== $last_index) echo 'element-line' ?>">
        <p class="element-right">
            <?php if ($part->getValue('image')): ?>
                <img src="<?php echo $view->hpImage($part->getValue('image')) ?>"  alt="<?php echo h($part->getValue('image_title')) ?>"/>
            <?php endif ?>
        </p>

        <div class="element-left">
            <?php if ($part->getValue('area')): ?>
                <p class="fs12">
                    <?php echo h($part->getValue('area')) ?>
                </p>
            <?php endif ?>
            <section>
                <?php if ($part->getValue('customer_name')): ?>
                    <h3 class="element-heading">
                        <a href="<?php echo $view->hpLink($part->getPage()->link_id) ?>"><?php echo h($part->getValue('customer_name')) ?></a>
                    </h3>
                <?php endif ?>

                <?php if ($part->getValue('customer_age') || $part->getValue('structure_type') || $part->getValue('staff_name')): ?>
                    <dl class="area-profile">
                        <?php if ($part->getValue('customer_age')): ?>
                            <dt>年齢：</dt>
                            <dd><?php echo h($part->getValue('customer_age')) ?>歳</dd>
                        <?php endif ?>
                        <?php if ($part->getValue('structure_type')): ?>
                            <dt>ご契約種別：</dt>
                            <dd>
                                <?php echo implode('/', $view->optionValues($part->structure_type->getValueOptions(), $part->structure_type->getValue('value'))) ?>
                            </dd>
                        <?php endif ?>
                        <?php if ($part->getValue('staff_name')): ?>
                            <dt>弊社担当：</dt>
                            <dd><?php echo h($part->getValue('staff_name')) ?></dd>
                        <?php endif ?>
                    </dl>
                <?php endif ?>
            </section>
        </div>
        <div class="clear">
            <section>
                <h4 class="element-heading2"><?php echo h($part->getValue('title')) ?></h4>
                <?php if ($part->getValue('customer_comment')): ?>
                    <?php echo $part->getValue('customer_comment') ?>
                <?php endif ?>
            </section>
        </div>
    </div>
    <?php $index++ ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>