<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if ($part instanceof \Library\Custom\Hp\Page\Parts\StaffDetail) {
                $parts[$page->getRow()->link_id] = $part;
            }
        }
    }
}

$index = 0;
$last_index = count($parts) - 1;
?>
<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
<?php foreach ($parts as $page_id => $part): ?>
    <div class="element element-tximg2 <?php if ($index !== $last_index) echo 'element-line' ?>">
        <p class="element-right">
          <?php if($part->getValue('image')):?>
            <img src="<?php echo $view->hpImage($part->getValue('image')) ?>"  alt="<?php echo h($part->getValue('image_title')) ?>"/>
          <?php endif ?>
        </p>

        <div class="element-left">
            <p class="fs12">
                <?php if ($part->getValue('position') !== '0') echo implode('/', $view->optionValues($part->position->getValueOptions(), $part->getValue('position'))) ?>
                <?php if ($part->getValue('position') !== '0' && $part->getValue('shop_name')): ?>
                    /
                <?php endif ?>
                <?php if ($part->getValue('shop_name')): ?>
                    <?php echo h($part->getValue('shop_name')) ?>
                <?php endif ?>
            </p>
            <section>
                <?php if ($part->name->getValue() || $part->kana->getValue()): ?>
                    <h3 class="element-heading">
                        <a href="<?php echo $view->hpLink($page_id) ?>">
                            <?php if ($part->name->getValue()): ?>
                                <?php echo h($part->name->getValue()) ?>
                            <?php endif ?>
                            <?php if ($part->kana->getValue()): ?>
                                <span class="fs12">（<?php echo h($part->kana->getValue()) ?>）</span>
                            <?php endif ?>
                        </a>
                    </h3>
                <?php endif ?>

                <?php if ($part->birthplace->getValue() || $part->hobby->getValue() || $part->qualification->getValue()): ?>
                    <dl class="area-profile">
                        <?php if ($part->birthplace->getValue()): ?>
                            <dt>出身：</dt>
                            <dd><?php echo h($part->birthplace->getValue()) ?></dd>
                        <?php endif ?>
                        <?php if ($part->hobby->getValue()): ?>
                            <dt>趣味：</dt>
                            <dd><?php echo h($part->hobby->getValue()) ?></dd>
                        <?php endif ?>
                        <?php if ($part->qualification->getValue()): ?>
                            <dt>資格：</dt>
                            <dd>
                                <?php echo implode('/', $view->optionValues($part->qualification->getValueOptions(), $part->qualification->getValue())) ?>
                            </dd>
                        <?php endif ?>
                    </dl>
                <?php endif ?>

                <?php if ($part->getValue('pr')) echo $part->getValue('pr'); ?>
            </section>
        </div>
    </div>
    <?php $index++ ?>
<?php endforeach ?>
<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
