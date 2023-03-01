<?php
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
if (!$view->pages || !count($view->pages)) {
    return;
}

$hpPageTable = \App::make(HpPageRepositoryInterface::class);

echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<div class="element element-news">
    <dl>
        <?php foreach ($view->pages as $page): ?>
            <?php $tdk = $page->form->getSubForm('tdk') ?>
            <?php $page = $page->getRow()->toArray() ?>
            <dt>
                <?php echo h($tdk->getValue('date')) ?>
                <?php echo $hpPageTable->checkNewMark($view->page->form->getSubForm('tdk')->getValue('new_mark'), $page['date']) ? config('constants.new_mark.NEW_MARK') : "";?>
            </dt>
            <dd>
            <?php
            $listTitle = $page['list_title'];
            $listTitle = preg_replace('/((<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)$)/', '', $listTitle);
            $listTitle = preg_replace('/(<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)/', '<br>', $listTitle);
            if ($hpPageTable->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                $linkDetail = \App::make(HpInfoDetailLinkRepositoryInterface::class)->getData($page['id'], $page['hp_id']);
                if ($linkDetail && ($linkDetail['link_page_id'] || $linkDetail['link_url'] || $linkDetail['file2'] || $linkDetail['link_house'])) {
                    $url	= "" ;
                    switch ( $linkDetail['link_type'] )
                    {
                        case config('constants.link_type.PAGE')	:
                            $url = $view->hpLink(	$linkDetail['link_page_id']	) ;
                            break ;
                        case config('constants.link_type.URL')		:
                            $url =					$linkDetail['link_url']		  ;
                            break ;
                        case config('constants.link_type.FILE')	:
                            $url = $view->hpFile2( $linkDetail['file2']			) ;
                            break ;
                        case config('constants.link_type.HOUSE')	:
                            $url = $view->hpLinkHouse( $linkDetail->link_house			) ;
                            break;
                    }
                    ?>
                    <a href="<?php echo $url ?>" target="<?php echo $linkDetail['link_target_blank'] ? '_blank' : '_self' ; ?>"><?php echo $listTitle ?></a>
                    <?php
                } else {
                    echo $listTitle;
                }
            } else {
                ?>
                <a href="<?php echo $view->hpLink($page['link_id']) ?>"><?php echo empty($listTitle) ? $page['title'] : $listTitle ?></a>
                <?php
            }
            ?>
            </dd>
        <?php endforeach ?>
    </dl>
</div>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
