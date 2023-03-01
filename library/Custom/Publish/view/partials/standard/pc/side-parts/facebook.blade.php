<?php
$hp = $view->page->getHp();
if (!$hp->fb_page_url || !$hp->fb_timeline_flg) {
    return;
}
?>
<div class="side-others-sns">
    <div class="fb-like-box" data-href="<?php echo $hp->fb_page_url ?>" data-width="200" data-height="300" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="true" data-show-border="true"></div>
</div>
