<?php
// cms server
if ($publishType == 4) {

    $sidenav = new sidenav($view, $device, $publishType,$sidebarOtherLinkTitle, $filename, $pages, $thisPage);
}
// gmo server
else {
    $sidenav = new sidenav($this->_view, $device, $publishType,$sidebarOtherLinkTitle);
}
?>
<?php if ($sidenav->display || ($sidenav->isTop && $isTopOriginal)) : ?>
    <?php if(isset($themeId) && ($themeId == 21 || $themeId == 22 || $themeId == 23)) : ?>
    <div class="side-others">
    <?php else : ?>
    <div class="side-nav">
    <?php endif; ?>
        <?php if ($device == 'pc' || ($sidenav->isTop && $isTopOriginal)) : ?>
        <section>
            <?php if(isset($themeId) && $themeId == 22) : ?>
            <h3 class="side-search-heading"><?php echo htmlspecialchars($sidenav->title()); ?></h3>
            <?php elseif(isset($themeId) && ($themeId == 21 || $themeId == 23)) : ?>
            <h3 class="side-others-heading"><?php echo htmlspecialchars($sidenav->title()); ?></h3>
            <?php else : ?>
            <h3 class="side-nav-heading"><?php echo htmlspecialchars($sidenav->title()); ?></h3>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(isset($themeId) && ($themeId == 21 || $themeId == 22 || $themeId == 23)) : ?>
        <ul class="side-others-link">
        <?php else : ?>
        <ul>
        <?php endif; ?>
            <?php if ($sidenav->isTop && $isTopOriginal) :?>
                <?php foreach ($sidenav->firstLevelPages as $page) : ?>
                    <?php
                    if (isset($page['is_gnav']) && $page['is_gnav'] && ($page['id'] != $sidenav->firstLevelPage['id'] || $page['page_type_code'] == 1)) continue ;?>
                    <li>
                        <a <?php echo $sidenav->viewHelper->hpHref($page); ?>><?php echo htmlspecialchars($page['title']); ?></a>
                        <?php if ($page['id'] == $sidenav->firstLevelPage['id']) : ?>
                            <?php $sidenav->echoChild($page['id'], $sidenav->pages); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($sidenav->firstLevelPages as $page) : ?>
                    <?php if ($device == 'pc') : ?>
                        <?php
                        // グローバルナビメニュー && (アクセス中のページでない || トップページ) continue
                        if (isset($page['is_gnav']) && $page['is_gnav'] && ($page['id'] != $sidenav->firstLevelPage['id'] || $page['page_type_code'] == 1)) continue ;?>
                        <li>
                            <a <?php echo $sidenav->viewHelper->hpHref($page); ?>><?php echo htmlspecialchars($page['title']); ?></a>
                            <?php if ($page['id'] == $sidenav->firstLevelPage['id']) : ?>
                                <?php $sidenav->echoChild($page['id'], $sidenav->pages); ?>
                            <?php endif; ?>
                        </li>
                    <?php elseif ($device == 'sp'): ?>
                        <?php if ($page['id'] == $sidenav->firstLevelPage['id']) : ?>
                            <li>
                                <a <?php echo $sidenav->viewHelper->hpHref($page); ?>><?php echo htmlspecialchars($page['title']); ?></a>
                                <?php $sidenav->echoChild($page['id'], $sidenav->pages); ?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php if ($device == 'pc' || ($sidenav->isTop && $isTopOriginal)) : ?>
            </section>
        <?php endif; ?>
    </div>
<?php endif; ?>