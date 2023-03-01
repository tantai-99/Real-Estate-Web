<?php
$extra_attr = '';
if (getActionName() === 'previewPage') {
    $extra_attr = ' data-enabled-link="true"';
}
$element = $view->element;
?>
<section>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getTitle(), 'level' => 1, 'element' => null)) ?>

    <div class="element">
        <ul class="list-file">
            <?php foreach ($element->elements->getSubForms() as $el): ?>
                <li>
                    <a href="<?php echo $view->hpFileLink($view->page->getHp()->id, $el->getValue('file')) ?>" <?php echo $extra_attr ?>
                       class="link-<?php echo $view->hpFileContent($view->page->getHp()->id, $el->getValue('file'))['kind'] ?>"><?php echo h($el->getValue('file_title')) ?></a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</section>
