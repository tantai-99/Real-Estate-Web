<?php
if (!$view->pages) {
    return;
}

echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm))
?>

    <div class="element element-news">
        <dl>
            <?php foreach ($view->pages as $index => $page): ?>
                <?php $tdk = $page->form->getSubForm('tdk') ?>
                <dt><?php echo h($tdk->getValue('date')) ?></dt>
                <dd>
                    <a href="<?php echo $view->hpLink($page->getRow()->link_id) ?>"><?php echo h($tdk->getValue('title')) ?></a>
                </dd>
                <?php $index++ ?>
            <?php endforeach ?>
        </dl>
    </div>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm)) ?>