<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
?>
<?php $view->getInnerHtml() ;?>

<?php
if (!$view->pages || !count($view->pages)) {
    return;
}

echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<div class="element element-news">
    <dl>
    <?php echo '<?php $pageIndex = $viewHelper->getPageById('.$view->page->getRow()->id.');?>'; ?>
        <?php foreach ($view->pages as $page): ?>
            <?php
            $page = $page->getRow()->toArray();
            echo '<?php $pageId = '.$page['id'].'?>';
            if ($hpPageTable->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                echo $script = file_get_contents($view->getScriptPath('main-parts/include/info-index_script_link_detail.blade.php'));
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
                echo $script = file_get_contents($view->getScriptPath('main-parts/include/info-index_script.blade.php'));
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
</div>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
