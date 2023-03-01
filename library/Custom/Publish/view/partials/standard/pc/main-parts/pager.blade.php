<?php
// ページ設定
$current_page = $view->current;
$total_pages = $view->total;
$blog_yyyymm = $view->blog_yyyymm;

if ($total_pages == 1){
    return;
}

$link_id = $view->page->getRow()->link_id;

$pages_from = max(1, $current_page - 5);
$pages_to = min($total_pages, $pages_from + 10);
if ($pages_to == $total_pages && $pages_from > 1){
    $pages_from = max(1, $pages_to - 10);
}

// プレビュー時にリンクを確認できるようにムリヤリ調整...
$extra_attr = '';
if (getActionName() === 'previewPage') {
    $extra_attr = ' data-enabled-link="true"';
}
?>
<div class="pager <?php if ($view->bottom) echo 'pager-bottom' ?>">
    <ul>
        <?php if ($current_page != 1): ?>
            <li class="pager-prev"><a href="<?php echo $view->hpPagerQuery($link_id, $current_page - 1, $blog_yyyymm) ?>" <?php echo $extra_attr?>>前へ</a></li>
        <?php else: ?>
            <li class="pager-prev"><span>前へ</span></li>
        <?php endif ?>
        <?php for ($i = $pages_from; $i <= $pages_to; $i++): ?>
            <li>
                <?php if ($i == $current_page): ?>
                    <span><?php echo $i?></span>
                <?php else: ?>
                    <a href="<?php echo $view->hpPagerQuery($link_id, $i, $blog_yyyymm)?>" <?php echo $extra_attr?>><?php echo $i?></a>
                <?php endif ?>
            </li>
        <?php endfor ?>
        <?php if ($current_page != $total_pages): ?>
            <li class="pager-next"><a href="<?php echo $view->hpPagerQuery($link_id, $current_page + 1, $blog_yyyymm) ?>" <?php echo $extra_attr?>>次へ</a></li>
        <?php else : ?>
            <li class="pager-next"><span>次へ</span></li>
        <?php endif ?>
    </ul>
</div>
