<?php
$page = $view->pages[$view->page->id];
$yyyymm_list = array_unique($view->yyyymm_list);
$count = array_count_values($view->yyyymm_list); ?>
<div class="side-nav">
    <section>
        <h3 class="side-nav-heading">月別記事</h3>
        <ul>
            <?php foreach ($yyyymm_list as $yyyymm) : ?>
                <?php
                $yyyymm = (string)$yyyymm;
                $year = substr($yyyymm, 0, 4);
                $month = substr($yyyymm, 4, 2);
                ?>
                <?php $href = substr($view->hpHref($page), 0, -1).$yyyymm.'/"'; ?>
                <li><a <?php echo $href; ?>><?php echo $year.'年'.$month.'月'; ?>(<?php echo $count[$yyyymm]; ?>)</a></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>