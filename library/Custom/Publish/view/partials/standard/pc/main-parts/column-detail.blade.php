<div class="element element-linespace-l">
    <p class="element-date">公開日：<?php echo h($view->page->form->getSubForm('tdk')->getValue('date'))?></p>
    <?php if ($view->element->getValue('image')): ?>
    <p class="element-tx">
        <img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>" alt="<?php echo h($view->element->getValue('image_title')) ?>">
    </p>
    <?php endif ?>
    <p class="element-tx"><?php echo $view->element->getValue('read_content') ?></p>
</div>

<div class="element element-tximg6 element-toc element-linespace-l">
    <dl>
        <dt class="element-heading">目次</dt>
        <dd>
        <ol>

        <?php foreach ($view->element->elements->getSubForms() as $key => $item): ?>
            <?php if($item->getValue('heading_type') == 1) : ?>
            <?php elseif($item->getValue('heading_type') == 2) : ?>
            <li><a href="#jump_<?php echo $key; ?>">・<?php echo h($item->getValue('heading')) ?></a></li>
            <?php elseif($item->getValue('heading_type') == 3) : ?>
            <li class="second-layer"><a href="#jump_<?php echo $key; ?>">・<?php echo h($item->getValue('heading')) ?></a></li>
            <?php endif ?>
            <?php endforeach ?>
        </ol>
        </dd>
    </dl>
</div>

<?php foreach ($view->element->elements->getSubForms() as $key => $item): ?>
<span id="jump_<?php echo $key; ?>"></span>
<?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $item->getValue('heading'), 'level' => $item->getValue('heading_type')-1, 'element' => null)) ?>

<div class="element element-linespace-l">
    <?php if ($item->getValue('image')): ?>
    <p class="element-img-right element-inline">
        <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
    </p>
    <?php endif ?>
    <p><?php echo $item->getValue('description') ?></p>
</div>
<?php endforeach ?>
