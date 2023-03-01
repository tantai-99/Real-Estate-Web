<?php
$hp = $view->page->getHp();
if (!$hp->line_at_freiend_qrcode) {
    return;
}
$heading = $view->element->getValue('heading');
$comment = $view->element->getValue('comment');
?>
<div class="side-others-line-qr">
    <section>
    <?php if ( $heading ) :?>
    <h3 class="side-others-line-qr-heading"><?php echo h($heading) ?></h3>
    <?php endif ?>
    <p class="side-others-line-qr-img">
        <?php echo($hp->line_at_freiend_qrcode);?>
    </p>
    <?php if ( $comment ) :?>
    <p class="side-others-line-qr-balloon"><?php echo h($comment) ?></p>
     <?php endif ?>
    </section>
</div>
