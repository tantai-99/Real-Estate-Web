<?php
// ページ一覧
$pageDetail = $viewHelper->getPageById($pageId);

$linkDetail = $viewHelper->getLinkInfoDetail($pageId);

$listTitle = $pageDetail['list_title'];
$listTitle = preg_replace('/((<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)$)/', '', $listTitle);
$listTitle = preg_replace('/(<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)/', '<br>', $listTitle);

if ( $linkDetail['link_page_id'] || $linkDetail['link_url'] || $linkDetail['file2'] || $linkDetail['link_house'] ) {
    $url	= "" ;
    switch ( $linkDetail['link_type'] )
    {
        case 1	:
            $url = $viewHelper->hpLink(	$linkDetail['link_page_id']	) ;
            break ;
        case 2		:
            $url =					$linkDetail['link_url']		  ;
            break ;
        case 3	:
            $url = $viewHelper->hpFile2( $linkDetail['file2']			) ;
            break ;
        case 4	:
            $url = $viewHelper->hpLinkHouse( $linkDetail['link_house']			) ;
            break ;
    }
    $target = $linkDetail['link_target_blank'] ? '_blank' : '_self';
    $arr = array('<', 'a href="', $url, '" target="', $target, '">', $listTitle, '</', 'a', '>');
    $content = implode('', $arr);
} elseif($linkDetail['page_id'] == $pageId) {
    $arr = array('<', 'a href="/404notFound">', $listTitle, '</', 'a', '>');
    $content = implode('', $arr);

} else {
    $content = $listTitle;

}

$newMark = $viewHelper->checkNewMark($pageIndex, $pageDetail['date']);

$date = date('Y年m月d日', strtotime($pageDetail['date']));

?>