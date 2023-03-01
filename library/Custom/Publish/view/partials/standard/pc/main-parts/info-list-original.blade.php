<section>
    <?php
    use App\Repositories\HpPage\HpPageRepository;
    use App\Repositories\HpPage\HpPageRepositoryInterface;
    use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'hp' => $view->hp));

    $page_size = $view->element->getValue('page_size') + 0;
    $hpPageTable = \App::make(HpPageRepositoryInterface::class);
    $pages = $view->filterCollectionTop($view->all_pages, array('page_type_code', HpPageRepository::TYPE_INFO_DETAIL,  'public_flg', 1, 'parent_page_id', $view->element->getValue('page_id')), array(['date', 'id'], ['DESC', 'DESC']), $page_size);
    ?>

    <div class="element element-news">
    <?php if (count($pages) > 0) : ?>
        <?php 
        foreach ($view->all_pages as $page) {
            if ($page['page_type_code'] == HpPageRepository::TYPE_INFO_INDEX) {
                $pageIndex = $page;
                break;
            }
        }
        ?>
            <dl>
                <?php foreach ($pages as $page): ?>
                    <dt>
                        <?php echo date('Y年m月d日', strtotime($page['date'])) ?>
                        <?php echo $hpPageTable->checkNewMark($pageIndex['new_mark'], $page['date']) ? config('constants.new_mark.NEW_MARK') : "";?>
                    </dt>
                    <dd>
                    <?php
                    $listTitle = $page['list_title'];
                    $listTitle = preg_replace('/((<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)$)/', '', $listTitle);
                    $listTitle = preg_replace('/(<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)/', '<br>', $listTitle);
                    if ($hpPageTable->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                        $linkDetail = \App::make(HpInfoDetailLinkRepositoryInterface::class)->getData($page['id'], $view->hp->id);
                        if ( $linkDetail->link_page_id || $linkDetail->link_url|| $linkDetail->file2 || $linkDetail->link_house ) {
                            $url	= "" ;
                            switch ( $linkDetail->link_type )
                            {
                                case config('constants.link_type.PAGE')	:
                                    $url = $view->hpLink(	$linkDetail->link_page_id	) ;
                                    break ;
                                case config('constants.link_type.URL')		:
                                    $url =					$linkDetail->link_url		  ;
                                    break ;
                                case config('constants.link_type.FILE')	:
                                    $url = $view->hpFile2( $linkDetail->file2			) ;
                                    break ;
                                case config('constants.link_type.HOUSE')	:
                                    $url = $view->hpLinkHouse( $linkDetail->link_house			) ;
                                    break;
                            }
                            ?>
                            <a href="<?php echo $url ?>" target="<?php echo $linkDetail->link_target_blank ? '_blank' : '_self' ; ?>"><?php echo $listTitle ?></a>
                            <?php
                        } else {
                            echo $listTitle;
                        }
                    } else {
                       ?>
                        <a href="<?php echo $view->hpLink($page['link_id']) ?>"><?php echo empty($plistTitle) ? $page['title'] : $listTitle ?></a>
                       <?php
                    }
                    ?>
                    </dd>
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
