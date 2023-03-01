<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
// set page_type_code for heading.blade.php
$view->registry('render:page_type_code', $view->page->getRow()->page_type_code + 0); ?>

<?php $view->getInnerHtml(); ?>

<section>
    <?php
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'hp' => $view->hp));

    $page_size = $view->element->getValue('page_size') + 0;
    $pages = $view->filterCollection($view->all_pages, array('page_type_code', HpPageRepository::TYPE_INFO_DETAIL, 'public_flg', 1), array(['date', 'id'], ['DESC', 'DESC']), $page_size);
    ?>

    <div class="element element-news">
        <?php if (count($pages) > 0) : ?>
            <dl>
                <?php 
                echo '<?php $viewHelper = new ViewHelper($this->_view); ?>';
                echo '<?php $pageIndex = $viewHelper->getPageById('.$pages[0]['parent_page_id'].');?>'; 
                ?>
                <?php foreach ($pages as $page): ?>
                    <?php
                    echo '<?php $pageId = '.$page['id'].'?>';
                    if ($hpPageTable->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                        echo $script = file_get_contents($view->getScriptPath('main-parts/include/info-list_script_link_detail.blade.php'));
                        ?>
                        <dt>
                            <?php 
                            echo '<?php echo $date;?>';
                            echo '<?php if($newMark) :?> ';
                            echo config('constants.new_mark.NEW_MARK');
                            echo '<?php endif; ?>';
                            ?>
                        </dt>
                        <dd>
                            <?php echo '<?php echo $content;?>'; ?>
                        </dd>
                        <?php
                    } else {
                        $url = $view->hpLink($page['link_id']);
                        echo '<?php $url = "'.$url.'" ;?>';
                        echo $script = file_get_contents($view->getScriptPath('main-parts/include/info-list_script.blade.php'));
                        ?>
                        <dt>
                            <?php 
                            echo '<?php echo $date;?>';
                            echo '<?php if($newMark) :?> ';
                            echo config('constants.new_mark.NEW_MARK');
                            echo '<?php endif; ?>';
                            ?>
                        </dt>
                        <dd>
                            <a href="<?php echo $url ?>"><?php echo '<?php echo $title;?>'; ?></a>
                        </dd>
                        <?php
                    }
                    ?>
                <?php endforeach ?>
            </dl>
            <p class="link-pastnews">
                <a href="<?php echo $view->hpLink()->type(HpPageRepository::TYPE_INFO_INDEX) ?>">過去のお知らせをすべて見る</a>
            </p>
        <?php else : ?>
            <p>現在お知らせはありません</p>
        <?php endif; ?>
    </div>
</section>
