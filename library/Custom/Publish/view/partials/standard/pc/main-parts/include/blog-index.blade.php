<?php $view->getInnerHtml() ;?>

<?php
if (!$view->pages) {
    return;
}

echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm))
?>

    <div class="element element-news">
        <dl>
            <?php foreach ($view->pages as $index => $page): ?>
                <?php
                $url = $view->hpLink($page->getRow()->link_id);
                echo '<?php $url = "'.$url.'" ;?>';
                echo $script = file_get_contents($view->getScriptPath('main-parts/include/blog-index_script.blade.php'));
                ?>
                <dt><?php echo '<?php echo $date;?>' ;?></dt>
                <dd>
                    <a href="<?php echo $view->hpLink($page->getRow()->link_id) ?>"><?php echo '<?php echo $title;?>' ;?></a>
                </dd>
                <?php $index++ ?>
            <?php endforeach ?>
        </dl>
    </div>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm)) ?>
