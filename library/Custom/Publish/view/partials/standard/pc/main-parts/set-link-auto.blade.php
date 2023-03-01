<?php
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use Library\Custom\Model\Estate;
$isPreview = getActionName() === 'previewPage';
$table = \App::make(HpPageRepositoryInterface::class);
$pageTypyCode = $view->page->getRow()->page_type_code;
$pageId =  $view->page->getRow()->id;
$larges = $table->fetchLargeCategoryPages($view->hp->id)->toSiteMapArray();
$largeCategory = $table->getPageArticleByCategory(HpPageRepository::CATEGORY_LARGE);
$viewElement = $view->element;
if ($isPreview):
    if ($pageTypyCode == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION):
        if(count($larges) > 1): ?>
        <section class="element-auto-link link-menu">
            <ul>
                <?php foreach ($larges as $key => $large): ?>
                <li><?php $url = $view->hpLink($large['link_id']);?>
                    <a href="<?php echo $url ?>"><span><?php echo $large['title'] ?></span></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
        <?php foreach ($view->element->elements->getSubForms() as $item): ?>
            <?php if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-auto-lead">
                    <p><?php echo $item->getValue('lead') ?></p>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if(count($larges) > 0):
            foreach ($larges as $key => $large):
                $smalls = $table->fetchSmallCategoryWithLargePages($view->hp->id, $large['id'])->toSiteMapArray();
                $url = $view->hpLink($large['link_id']);
                $smallList = [];
                // if (count($smalls) > 0): ?>
                <section class="block-large-category element-block-large">
                    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $large['title'], 'level' => 1, 'element' => null)) ?>
                    <div class="element-auto-link large-list">
                        <ul>
                            <?php foreach ($smalls as $key => $small): 
                                $smallList[] = $small['id'];
                                $urlSmall = $view->hpLink($small['link_id']);
                            ?>
                            <li><a href="<?php echo $urlSmall ?>"><?php echo $small['title'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="element-auto-link pick-up element-pick">
                            <p><span>PICK UP</span></p>
                            <?php
                            if (count($smallList) > 0):
                                $acticle = $table->fetchArticleCategoryWithSmallPages($view->hp->id, $smallList)->toSiteMapArray();
                                if (count($acticle) > 0):
                                    $random = rand(0, count($acticle)-1);
                                    $urlActicle = $view->hpLink($acticle[$random]['link_id']);
                                ?>
                                <a href="<?php echo $urlActicle ?>"><?php echo $acticle[$random]['title'] ?></a>
                                <?php endif;
                            endif; ?>
                        </div>
                        <p class="link-category">
                            <a href="<?php echo $url ?>"><span class="link-title"><?php echo $large['title'] ?></span><span class="link">一覧ページ</span></a>
                        </p>
                    </div>
                </section>
            <?php // endif;
            endforeach;
        endif; ?>
    <?php endif;
    if (in_array($pageTypyCode, $largeCategory)):
        foreach ($view->element->elements->getSubForms() as $item):
            if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-large-lead">
                    <p><?php echo $item->getValue('lead'); ?></p>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
        <section>
            <div class="element-auto-link element-small-category">
                <div class="small-category">
                    <span>目次<label>[<a href="#">非表示</a>]</label></span>
                    <ul>
                        <?php $smalls = $table->fetchSmallCategoryWithLargePages($view->hp->id, $view->page->getRow()->id)->toSiteMapArray();
                        foreach ($smalls as $key => $small):
                            $urlSmall = $view->hpLink($small['link_id']);
                        ?>
                        <li><a href="<?php echo $urlSmall ?>"><span><?php echo $small['title'] ?></span></a></li>
                        <?php endforeach;
                        ?>
                    </ul>
                </div>
            </div>
        </section>
        <?php if (count($smalls) > 0):
            foreach ($smalls as $key => $small):
                $url = $view->hpLink($small['link_id']);
            ?>
            <section class="block-small-category element-block-small">
                <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $small['title'], 'level' => 1, 'element' => null)) ?>
                <div class="element-auto-link small-list">
                    <ul>
                    <?php $acticles = $table->fetchArticleCategoryWithSmallPages($view->hp->id, [$small['id']])->toSiteMapArray();
                    if (count($acticles) > 0):
                        foreach ($acticles as $key => $acticle) :
                            $urlActicle = $view->hpLink($acticle['link_id']);
                    ?>
                    <li>
                        <a href="<?php echo $urlActicle ?>"><span><?php echo $acticle['title'] ?></span></a>
                    </li>
                    <?php endforeach;
                    endif; ?>
                    </ul>
                    <p class="link-category">
                        <a href="<?php echo $url ?>"><span class="link-title"><?php echo $small['title'] ?></span><span class="link">一覧ページ</span></a>
                    </p>
                </div>
            </section>
            <?php endforeach;
        endif; ?>
        <?php
            $acticleWithoutCategory = $table->fetchActicleCategoryWithoutCategoryPages($view->hp->id, $pageId);
            if (count($acticleWithoutCategory) > 0) : ?>
        <section>
            <div class="element-auto-link link-other-category">
                <h3><span></span>他のカテゴリーの記事も見てみる</h3>
                <div class="element-auto-link">
                    <?php if (count($acticleWithoutCategory) > 5) {
                        $randomNumber = range(0, count($acticleWithoutCategory) - 1);
                        shuffle($randomNumber);
                        $randomNumber = array_slice($randomNumber ,0 ,5);
                    }
                    if (isset($randomNumber)):
                        foreach ($randomNumber as $number): ?>
                        <ul class="link-other">
                            <li><?php echo $table->fetchRowById($acticleWithoutCategory[$number]['parent_page_id'])->title; ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($acticleWithoutCategory[$number]['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithoutCategory[$number]['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    else :
                        foreach ($acticleWithoutCategory as $key => $acticleWithout): ?>
                        <ul class="link-other">
                            <li><?php echo $table->fetchRowById($acticleWithout['parent_page_id'])->title ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($acticleWithout['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                        endif;
                    ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <?php if(count($larges) > 1): ?>
        <section class="element-large-category">
            <h3><?php 
            $usefllRealEstate = $table->fetchLargeByPageTypeCodePages($view->hp->id, HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION);
            if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0]['title'];
            ?></h3>
            <div class="element-auto-link">
                <ul>
                    <?php foreach ($larges as $key => $large):
                        $url = $view->hpLink($large['link_id']);
                    ?>
                    <?php if ($large['id'] != $pageId): ?>
                    <li class="large-link">
                        <a href="<?php echo $url ?>"><span><?php echo $large['title'] ?></span></a>
                        <?php else : ?>
                    <li class="large-title">
                        <span><?php echo $large['title'] ?></span>
                    </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php endif; ?>
    <?php endif;
    if (in_array($pageTypyCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_SMALL))):
        foreach ($view->element->elements->getSubForms() as $item):
            if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-small-lead">
                    <p><?php echo $item->getValue('lead') ?></p>
                </div>
            </section>
            <?php endif;
        endforeach; ?>
        <?php $acticles = $table->fetchArticleCategoryWithSmallPages($view->hp->id, [$view->page->getRow()->id])->toSiteMapArray();
        if (count($acticles) > 0): ?>
        <div class="element-auto-link list-article">
        <?php foreach ($acticles as $key => $acticle) :
            $url = $view->hpLink($acticle['link_id']);
            $acticleContent = \App::make(HpMainPartsRepositoryInterface::class)->fetchAll([
                    ['hp_id', $view->hp->id],
                    ['page_id', $acticle['id']],
                    ['parts_type_code', HpMainPartsRepository::PARTS_ARTICLE_TEMPLATE]
                ])->toArray();
        ?>
        <div class="small-list-article">
            <div class="element-image-small-article">
                <?php if ($acticleContent && isset($acticleContent[0]['attr_1'])): ?>
                <a href="<?php echo $url; ?>"><img src="/image/hp-image?image_id=<?php echo h($acticleContent[0]['attr_1'])?>" alt="<?php echo $acticleContent[0]['attr_2'] ?>"></a>
                <?php else: ?>
                <a href="<?php echo $url; ?>"><img src="<?php $view->src('imgs/img_nophoto_160.png') ;?>" alt="img nophoto"></a>
                <?php endif; ?>
            </div>
            <div class="element-text-small-article">
                <p class="element-article-title"><?php echo $acticle['title']; ?></p>
                <?php
                $content = null;
                if ($acticleContent && isset($acticleContent[0]['attr_3'])) {
                    $content = str_replace('&nbsp;', '', strip_tags($acticleContent[0]['attr_3']));
                } 
                if (mb_strlen($content) > 50) {
                    $content = mb_substr($content,0,50,'UTF-8') . '...';
                } ?>
                <div class="element-article-content"><?php echo $content; ?></div>
                <p class="link-category"><a href="<?php echo $url ?>">記事を読む</a></p>
            </div>
        </div>
        <hr/>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php
        $smalls = $table->fetchSmallCategoryWithLargePages($view->hp->id, [$view->page->getRow()->parent_page_id])->toSiteMapArray();
        $largeTitle = $table->fetchRowById($view->page->getRow()->parent_page_id)->title;
        $smallList = [];
        if (count($smalls) > 1) :
            foreach ($smalls as $key => $small) {
                if ($pageId != $small['id']) {
                    $smallList[] = array('link_id' => $small['link_id'], 'title' => $small['title']);
                }
            }
        endif;
        if (count($smallList) > 0) : ?>
        <section>
            <div class="element-auto-link link-other-category small-link-other">
                <h3><span></span>他のカテゴリーの記事も見てみる</h3>
                <div class="element-auto-link">
                <?php
                    if (count($smallList) > 5) {
                        $randomNumber = range(0, count($smallList) - 1);
                        shuffle($randomNumber);
                        $randomNumber = array_slice($randomNumber ,0 ,5);
                    }
                    if (isset($randomNumber)):
                        foreach ($randomNumber as $number): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($smallList[$number]['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $smallList[$number]['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    else :
                        foreach ($smallList as $key => $acticleWithout): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($acticleWithout['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <?php if(count($larges) > 1): ?>
        <section class="element-large-category">
            <h3><?php 
            $usefllRealEstate = $table->fetchLargeByPageTypeCodePages($view->hp->id, HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION);
            if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0]['title'];
            ?></h3>
            <div class="element-auto-link">
                <ul>
                    <?php foreach ($larges as $key => $large):
                        $url = $view->hpLink($large['link_id']);
                    ?>
                    <li class="large-link">
                        <a href="<?php echo $url ?>"><span class="link"><?php echo $large['title'] ?></span></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>
<?php else:
    if ($pageTypyCode == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION):
        echo '<?php
        $pages = $viewHelper->getChildByLinkId('.$view->page->getRow()->id.');
        if (count($pages) > 1): ?>
        <section class="element-auto-link link-menu">
            <ul>
                <?php foreach($pages as $key => $page) : ?>
                <li><a href="#link_<?php echo $key; ?>"><span><?php echo $page[\'title\'] ?></span></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>';
        foreach ($view->element->elements->getSubForms() as $item): ?>
            <?php if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-auto-lead">
                    <p><?php echo $item->getValue('lead') ?></p>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php echo '<?php
        if(count($pages > 0)):
            foreach ($pages as $key => $page):
                $smalls = $viewHelper->getChildByLinkId($page[\'id\']);
                $smallList = [];
                // if (count($smalls) > 0): ?>
                <section class="block-large-category element-block-large" id="link_<?php echo $key; ?>">
                    <h3 class="heading-lv2"><?php echo $page[\'title\'] ?></h3>
                    <div class="element-auto-link large-list">
                    <ul>
                        <?php foreach ($smalls as $key => $small): 
                            $smallList[] = $small[\'id\'];
                            $urlSmall = $viewHelper->hpLink($small[\'link_id\']);
                        ?>
                        <li><a href="<?php echo $urlSmall ?>"><span><?php echo $small[\'title\'] ?></span></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="element-auto-link pick-up element-pick">
                        <p><span>PICK UP</span></p>
                        <?php
                        if (count($smallList) > 0):
                            $acticle = $viewHelper->getChildByArrayId($smallList);
                            if (count($acticle) > 0):
                                $random = rand(0, count($acticle)-1);
                                $urlActicle = $viewHelper->hpLink($acticle[$random][\'link_id\']);
                            ?>
                            <a href="<?php echo $urlActicle ?>"><?php echo $acticle[$random][\'title\'] ?></a>
                            <?php endif;
                        endif; ?>
                    </div>
                    <p class="link-category">
                        <a href="<?php echo $viewHelper->hpLink($page[\'link_id\']) ?>"><span class="link-title"><?php echo $page[\'title\'] ?></span><span class="link">一覧ページ</span></a>
                    </p>
                    </div>
                </section>
            <?php // endif;
            endforeach;
        endif;
        ?>';
    endif; ?>
    <?php if (in_array($pageTypyCode, $largeCategory)): 
        foreach ($view->element->elements->getSubForms() as $item):
            if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-large-lead">
                    <p><?php echo $item->getValue('lead'); ?></p>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>
        <section>
             <div class="element-auto-link element-small-category">
                <div class="small-category">
                    <span>目次<label>[<a href="#">非表示</a>]</label></span>
                    <ul>
                    <?php echo '<?php $smalls = $viewHelper->getChildByLinkId('.$view->page->getRow()->id.');
                    foreach ($smalls as $key => $small):
                        $urlSmall = $viewHelper->hpLink($small[\'link_id\']);
                    ?>
                    <li><a href="#small_<?php echo $key; ?>"><span><?php echo $small[\'title\'] ?></span></a></li>
                    <?php endforeach; ?>';
                    ?>
                </ul>
                </div>
            </div>
        </section>
        <?php echo '<?php if (count($smalls) > 0):
            foreach ($smalls as $key => $small):
                $url = $viewHelper->hpLink($small[\'link_id\']);
            ?>
            <section class="block-small-category element-block-small" id="small_<?php echo $key; ?>">
                <h3 class="heading-lv2"><?php echo $small[\'title\'] ?></h3>
                <div class="element-auto-link small-list">
                    <ul>
                    <?php $acticles = $viewHelper->getChildByLinkId($small[\'id\']);
                    if (count($acticles) > 0):
                        foreach ($acticles as $key => $acticle) :
                            $urlActicle = $viewHelper->hpLink($acticle[\'link_id\']);
                    ?>
                    <li>
                        <a href="<?php echo $urlActicle ?>"><span><?php echo $acticle[\'title\'] ?></span></a>
                    </li>
                    <?php endforeach;
                    endif; ?>
                    </ul>
                    <p class="link-category">
                    <a href="<?php echo $url ?>"><span class="link-title"><?php echo $small[\'title\'] ?></span><span class="link">一覧ページ</span></a>
                </p>
                </div>
            </section>
            <?php endforeach;
        endif; ?>
        '; ?>
        <?php
            echo '<?php
                $larges = $viewHelper->getLargesCategory();
                $lagesPage = $viewHelper->getChildWithoutPageId('. $pageId .', '.HpPageRepository::CATEGORY_LARGE.');
                $acticleWithoutCategory = [];
                foreach ($lagesPage as $key => $lage):
                    $smalls = $viewHelper->getChildByLinkId($lage[\'id\']);
                    if (count($smalls) > 0) {
                        foreach ($smalls as $key => $small):
                            $acticleWithoutCategory[] = $small;
                        endforeach;
                    }
                endforeach;
            if (count($acticleWithoutCategory) > 0) : ?>';
        ?>
        <section>
            <div class="element-auto-link link-other-category">
                <h3><span></span>他のカテゴリーの記事も見てみる</h3>
                <div class="element-auto-link">
                <?php echo '<?php
                if (count($acticleWithoutCategory) > 5) {
                    $randomNumber = range(0, count($acticleWithoutCategory) - 1);
                    shuffle($randomNumber);
                    $randomNumber = array_slice($randomNumber ,0 ,5);
                }
                if (isset($randomNumber)):
                    foreach ($randomNumber as $number): ?>
                    <ul class="link-other">
                        <li><?php echo $viewHelper->getPageByIdPage($acticleWithoutCategory[$number][\'parent_page_id\'])[\'title\']; ?></li>
                    <li>
                        <?php $urlActicleWithout = $viewHelper->hpLink($acticleWithoutCategory[$number][\'link_id\']); ?>
                        <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithoutCategory[$number][\'title\'] ?></a>
                    </li>
                    </ul>
                    <?php endforeach;
                else :
                    foreach ($acticleWithoutCategory as $key => $acticleWithout): ?>
                    <ul class="link-other">
                        <li><?php echo $viewHelper->getPageByIdPage($acticleWithout[\'parent_page_id\'])[\'title\'] ?></li>
                        <li>
                            <?php $urlActicleWithout = $viewHelper->hpLink($acticleWithout[\'link_id\']); ?>
                            <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout[\'title\'] ?></a>
                        </li>
                    </ul>
                    <?php endforeach;
                    endif;
                ?>'; ?>
                </div>
            </div>
        </section>
        <?php echo '<?php endif; ?>'; ?>
        <?php echo '<?php 
        $pageMenus = $viewHelper->getChildByPageTypeCode($viewHelper->getLargesCategory());
        if(count($pageMenus) > 1): ?>'; ?>
        <section class="element-large-category">
            <h3><?php 
            echo '<?php $usefllRealEstate = $viewHelper->getChildByPageTypeCode([' . HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION . ']);
            if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0][\'title\'];
            ?>'; ?></h3>
            <div class="element-auto-link">
            <ul>
                <?php echo '<?php
                
                foreach ($pageMenus as $key => $menu):
                    $url = $viewHelper->hpLink($menu[\'link_id\']);
                ?>
                <?php if ($menu[\'id\'] != ' . $pageId . '): ?>
                <li class="large-link">
                    <a href="<?php echo $url ?>"><span class="link"><?php echo $menu[\'title\'] ?></span></a>
                </li>
                    <?php else : ?>
                <li class="large-title">
                    <span class="title"><?php echo $menu[\'title\'] ?></span>
                </li>
                    <?php endif; ?>
                <?php endforeach; ?>'; ?>
            </ul>
            </div>
        </section>
        <?php echo '<?php endif; ?>'; ?>
    <?php endif; ?>
    <?php if (in_array($pageTypyCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_SMALL))):
        foreach ($view->element->elements->getSubForms() as $item):
            if ($item->getValue('lead')): ?>
            <section>
                <div class="element-auto-link element-small-lead">
                    <p><?php echo $item->getValue('lead') ?></p>
                </div>
            </section>
            <?php endif;
        endforeach; ?>
        <?php echo '<?php
        $acticles = $viewHelper->getChildByLinkId('.$view->page->getRow()->id.');
        if (count($acticles) > 0): ?>
        <div class="element-auto-link list-article">
        <?php
            foreach ($acticles as $key => $acticle) :
            $url = $viewHelper->hpLink($acticle[\'link_id\']);
            $acticleContent = $viewHelper->getArticle($acticle[\'link_id\']);
        ?>
        <div class="small-list-article">
            <div class="element-image-small-article">
                <?php if ($acticleContent && isset($acticleContent[\'attr_1\'])): ?>
                <a href="<?php echo $url; ?>"><img src="<?php echo $acticleContent[\'attr_1\'] ?>" alt="<?php echo $acticleContent[\'attr_2\'] ?>"></a>
                <?php else: ?>
                <a href="<?php echo $url; ?>"><img src="/pc/imgs/img_nophoto_160.png" alt="img nophoto"></a>
                <?php endif; ?>
            </div>
            <div class="element-text-small-article">
                <p class="element-article-title"><?php echo $acticle[\'title\']; ?></p>
                <?php $content = null;
                if ($acticleContent && isset($acticleContent[\'attr_3\'])) {
                    $content = str_replace(\'&nbsp;\', \'\', strip_tags($acticleContent[\'attr_3\']));
                } 
                if ($content && isset($acticleContent[\'len\']) && $acticleContent[\'len\'] > 50) {
                    $content = mb_substr($content,0,50,\'UTF-8\') . \'...\';
                } ?>
                <p class="element-article-content"><?php echo $content; ?></p>
                <p class="link-category"><a href="<?php echo $url ?>">記事を読む</a></p>
            </div>
        </div>
        <hr/>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>'; ?>
        <?php echo '<?php $smalls = $viewHelper->getChildByLinkId('.$view->page->getRow()->parent_page_id.');
            $largeTitle = $viewHelper->getPageByIdPage('.$view->page->getRow()->parent_page_id.')[\'title\'];
            $smallList = [];
            if (count($smalls) > 1) :
                foreach ($smalls as $key => $small) {
                    if (' . $pageId . ' != $small[\'id\']) {
                        $smallList[] = array(\'link_id\' => $small[\'link_id\'], \'title\' => $small[\'title\']);
                    }
                }
            endif;
            if (count($smallList) > 0) : ?>'; ?>
        <section>
            <div class="element-auto-link link-other-category small-link-other">
                <h3><span></span>他のカテゴリーの記事も見てみる</h3>
                <div class="element-auto-link">
                <?php echo '<?php
                    if (count($smallList) > 5) {
                        $randomNumber = range(0, count($smallList) - 1);
                        shuffle($randomNumber);
                        $randomNumber = array_slice($randomNumber ,0 ,5);
                    }
                    if (isset($randomNumber)):
                        foreach ($randomNumber as $number): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                        <li>
                            <?php $urlActicleWithout = $viewHelper->hpLink($smallList[$number][\'link_id\']); ?>
                            <a href="<?php echo $urlActicleWithout ?>"><?php echo $smallList[$number][\'title\'] ?></a>
                        </li>
                        </ul>
                        <?php endforeach;
                    else :
                        foreach ($smallList as $key => $acticleWithout): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                        <li>
                            <?php $urlActicleWithout = $viewHelper->hpLink($acticleWithout[\'link_id\']); ?>
                            <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout[\'title\'] ?></a>
                        </li>
                        </ul>
                        <?php endforeach;
                    endif; ?>'; ?>
                </div>
            </div>
        </section>
        <?php echo '<?php endif; ?>'; ?>
        
        <?php echo '<?php
            $pageMenus = $viewHelper->getChildByPageTypeCode($viewHelper->getLargesCategory());
            if(count($pageMenus) > 1):
        ?>'; ?>
         <section class="element-large-category">
            <h3><?php 
            echo '<?php $usefllRealEstate = $viewHelper->getChildByPageTypeCode([' . HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION . ']);
            if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0][\'title\'];
            ?>'; ?></h3>
            <div class="element-auto-link">
                <ul>
                <?php echo '<?php 
                foreach ($pageMenus as $key => $menu):
                    $url = $viewHelper->hpLink($menu[\'link_id\']);
                ?>
                <li class="large-link">
                    <a href="<?php echo $url ?>"><span class="link"><?php echo $menu[\'title\'] ?></span></a>
                </li>
                <?php endforeach; ?>'; ?>
            </ul>
        </section>
        <?php echo '<?php endif; ?>'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php
$pageContact = $table->fetchLargeByPageTypeCodePages($view->hp->id, HpPageRepository::TYPE_FORM_CONTACT);
if (!empty($pageContact)) {
    $pageContact = $pageContact[0];
    $pageContact['new_path'] = $pageContact['filename'] .'/index.html';
}
?>
<?php foreach ($viewElement->elements->getSubForms() as $item): ?>
    <?php if ($item->getValue('contact')): ?>
    <?php if (in_array($pageTypyCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))): ?>
    <section class="articles-contact articles-template">
    <?php else: ?>
    <section class="articles-contact">
    <?php endif; ?>
        <div>
            <?php if ($isPreview): ?>
            <p><a <?php echo $view->hpHref($pageContact); ?>><?php echo $pageContact['title']; ?></a></p>
            <?php else:
            echo '<?php
                $pageContact = $viewHelper->getPageByLinkId('. $pageContact['link_id'] .'); ?>
                <p><a <?php echo $viewHelper->hpHref($pageContact); ?>><?php echo $pageContact["title"]; ?></a></p>';
            endif; ?>
        </div>
    </section>
    <?php endif; ?>
<?php endforeach; ?>
<?php if ($isPreview && in_array($pageTypyCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))): ?>
    <?php $articles = $table->fetchSmallCategoryWithLargePages($view->hp->id, [$view->page->getRow()->parent_page_id])->toSiteMapArray();
    $small = $table->fetchRowById($view->page->getRow()->parent_page_id);
    $largeTitle = $table->fetchRowById($small->parent_page_id)->title;
    $articleList = [];
    $beforeArt = null;
    $afterArt = null;
    $sort = $view->page->getRow()->sort;
    if (count($articles) > 1) :
        foreach ($articles as $key => $article) {
            if ($pageId != $article['id']) {
                $articleList[] = array('link_id' => $article['link_id'], 'title' => $article['title']);
                if ($sort > $article['sort']) {
                    if (empty($beforeArt)) {
                        $beforeArt = $article;
                    } else {
                        if ($beforeArt['sort'] < $article['sort']) {
                            $beforeArt = $article;
                        }
                    }
                }
                if ($sort < $article['sort']) {
                    if (empty($afterArt)) {
                        $afterArt = $article;
                    } else {
                        if ($afterArt['sort'] > $article['sort']) {
                            $afterArt = $article;
                        }
                    }
                }
            }
        }
    endif;
    ?>
    <?php if (!empty($beforeArt) || !empty($afterArt)): ?>
    <section class="article-back-next article-custom">
    <?php else : ?>
    <section class="article-back-next">
    <?php endif;?>
        <div>
            <?php if (!empty($beforeArt)): ?>
            <div class="article-back">
                <span>前の記事</span>
                <a href="<?php echo $view->hpLink($beforeArt['link_id']); ?>"><?php echo $beforeArt['title']; ?></a>
            </div>
            <?php endif; ?>
            <?php if (!empty($afterArt)): ?>
            <div class="article-next">
                <span>次の記事</span>
                <a href="<?php echo $view->hpLink($afterArt['link_id']); ?>"><?php echo $afterArt['title']; ?></a>
            </div>
            <?php endif; ?>
            <div class="article-clear"></div>
        </div>
    </section>
    <?php
    if (count($articleList) > 0) : ?>
        <section>
            <div class="element-auto-link link-other-category">
                <h3><span></span>こんな記事も読まれています</h3>
                <div class="element-auto-link">
                <?php
                    if (count($articleList) > 5) {
                        $randomNumber = range(0, count($articleList) - 1);
                        shuffle($randomNumber);
                        $randomNumber = array_slice($randomNumber ,0 ,5);
                    }
                    if (isset($randomNumber)):
                        foreach ($randomNumber as $number): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($articleList[$number]['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $articleList[$number]['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    else :
                        foreach ($articleList as $key => $acticleWithout): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $view->hpLink($acticleWithout['link_id']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout['title'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?php if(count($larges) > 1): ?>
        <section class="element-large-category element-large-article">
            <h3><?php 
            $usefllRealEstate = $table->fetchLargeByPageTypeCodePages($view->hp->id, HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION);
            if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0]['title'];
            ?></h3>
            <div class="element-auto-link">
                <ul>
                    <?php foreach ($larges as $key => $large):
                        $url = $view->hpLink($large['link_id']);
                    ?>
                    <li class="large-link">
                        <a href="<?php echo $url ?>"><span class="link"><?php echo $large['title'] ?></span></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>
<?php elseif (in_array($pageTypyCode, $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))): ?>
    <?php echo '
    <?php 
    $articles = $viewHelper->getChildByLinkId('.$view->page->getRow()->parent_page_id.');
    $small = $viewHelper->getPageByIdPage('.$view->page->getRow()->parent_page_id.');
    $largeTitle = $viewHelper->getPageByIdPage($small[\'parent_page_id\'])[\'title\'];
    $articleList = [];
    $beforeArt = null;
    $afterArt = null;
    $sort = '.$view->page->getRow()->sort.';
    if (count($articles) > 1) :
        foreach ($articles as $key => $article) {
            if ('.$pageId.' != $article[\'id\']) {
                $articleList[] = array(\'link_id\' => $article[\'link_id\'], \'title\' => $article[\'title\']);
                if ($sort > $article[\'sort\']) {
                    if (empty($beforeArt)) {
                        $beforeArt = $article;
                    } else {
                        if ($beforeArt[\'sort\'] < $article[\'sort\']) {
                            $beforeArt = $article;
                        }
                    }
                }
                if ($sort < $article[\'sort\']) {
                    if (empty($afterArt)) {
                        $afterArt = $article;
                    } else {
                        if ($afterArt[\'sort\'] > $article[\'sort\']) {
                            $afterArt = $article;
                        }
                    }
                }
            }
        }
    endif;
    ?>
    <?php if (!empty($beforeArt) || !empty($afterArt)): ?>
    <section class="article-back-next article-custom">
    <?php else : ?>
    <section class="article-back-next">
    <?php endif;?>
        <div>
            <?php if (!empty($beforeArt)): ?>
            <div class="article-back">
                <span>前の記事</span>
                <a href="<?php echo $viewHelper->hpLink($beforeArt[\'link_id\']); ?>"><?php echo $beforeArt[\'title\']; ?></a>
            </div>
            <?php endif; ?>
            <?php if (!empty($afterArt)): ?>
            <div class="article-next">
                <span>次の記事</span>
                <a href="<?php echo $viewHelper->hpLink($afterArt[\'link_id\']); ?>"><?php echo $afterArt[\'title\']; ?></a>
            </div>
            <?php endif; ?>
            <div class="article-clear"></div>
        </div>
    </section>
    <?php
    if (count($articleList) > 0) : ?>
        <section>
            <div class="element-auto-link link-other-category">
                <h3><span></span>こんな記事も読まれています</h3>
                <div class="element-auto-link">
                <?php
                    if (count($articleList) > 5) {
                        $randomNumber = range(0, count($articleList) - 1);
                        shuffle($randomNumber);
                        $randomNumber = array_slice($randomNumber ,0 ,5);
                    }
                    if (isset($randomNumber)):
                        foreach ($randomNumber as $number): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $viewHelper->hpLink($articleList[$number][\'link_id\']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $articleList[$number][\'title\'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    else :
                        foreach ($articleList as $key => $acticleWithout): ?>
                        <ul class="link-other">
                            <li><?php echo $largeTitle ?></li>
                            <li>
                                <?php $urlActicleWithout = $viewHelper->hpLink($acticleWithout[\'link_id\']); ?>
                                <a href="<?php echo $urlActicleWithout ?>"><?php echo $acticleWithout[\'title\'] ?></a>
                            </li>
                        </ul>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>'; ?>
    <?php echo '<?php
        $pageMenus = $viewHelper->getChildByPageTypeCode($viewHelper->getLargesCategory());
        if(count($pageMenus) > 1):
    ?>'; ?>
     <section class="element-large-category element-large-article">
        <h3><?php 
        echo '<?php $usefllRealEstate = $viewHelper->getChildByPageTypeCode([' . HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION . ']);
        if (count($usefllRealEstate) > 0) echo $usefllRealEstate[0][\'title\'];
        ?>'; ?></h3>
        <div class="element-auto-link">
            <ul>
            <?php echo '<?php 
            foreach ($pageMenus as $key => $menu):
                $url = $viewHelper->hpLink($menu[\'link_id\']);
            ?>
            <li class="large-link">
                <a href="<?php echo $url ?>"><span class="link"><?php echo $menu[\'title\'] ?></span></a>
            </li>
            <?php endforeach; ?>'; ?>
        </ul>
    </section>
    <?php echo '<?php endif; ?>'; ?>
<?php endif; ?>
<?php if ($isPreview): ?>
<?php $map = $view->getPublishEstateInstance()->getMap(); ?>
<?php $typeList = Estate\TypeList::getInstance();
$li_class = array(
    1 => "apartment",
    2 => "shop",
    3 => "building",
    4 => "parking",
    5 => "land",
    6 => "warehouse",
    7 => "apartment",
    8 => "house",
    9 => "land",
   10 => "shop",
   11 => "building",
   12 => "warehouse",
);
if (count($map) > 0): ?>
<div class="element-auto-search-housing">
    <?php foreach ([Estate\ClassList::RENT, Estate\ClassList::PURCHASE,] as $rent_or_purchase) : ?>
        <?php if (isset($map[$rent_or_purchase])) : ?>
            <section class="element-search-from-item">
                <h4 class="heading-search-from area <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>rent<?php else : ?>buy<?php endif; ?>">
                    <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>賃貸物件を探す<?php else : ?>売買物件を探す<?php endif; ?>
                </h4>
                <ul>
                    <?php foreach ($map[$rent_or_purchase] as $class => $array): ?>
                        <?php foreach ($array as $type => $name): ?>
                            <li class="<?php echo $li_class[$type]; ?>"><a href="<?php echo "/{$typeList->getUrl($type)}/"; ?>" target="_blank"><span><?php echo $name; ?></span></a></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php else :
    echo '<?php $sidesearcharcticle_error = $this->viewHelper->includeCommonFile("sidesearcharcticle");?>';
endif;
?>