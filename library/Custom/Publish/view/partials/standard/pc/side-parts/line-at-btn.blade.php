<?php
$hp = $view->page->getHp();
if (!$hp->line_at_freiend_button) {
    return;
}
$comment = $view->element->getValue('comment');
?>
<div class="side-others-line">
    <section>
        <?php if ( $comment ) :?>
        <div class="side-others-line-balloon"><?php echo h($comment) ?></div>
        <?php endif ?>
        <p class="side-others-line-img">
            <?php echo($hp->line_at_freiend_button);?>
        </p>
    </section>
</div>
