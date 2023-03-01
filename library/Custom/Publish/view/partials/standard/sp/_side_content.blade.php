<?php
if (!$view->page->form->getSubForm('side')) {
    return;
}

$hasParts = false;
foreach ($view->page->form->getSubForm('side')->getSubForms() as $parts) {
    if ('0' === $parts->getValue('display_flg') || get_class($parts) == 'Library\Custom\Hp\Page\SideParts') {
        continue;
    }
    $hasParts = true;
    break;
}
if (!$hasParts) {
    return;
}
?>
<?php foreach ($view->page->form->getSubForm('side')->getSubForms() as $parts): ?>
    <?php if ('0' === $parts->getValue('display_flg')) continue ?>

    <?php
    switch (get_class($parts)) {
        case 'Library\Custom\Hp\Page\SideParts\Fb':
            $template = 'side-parts/facebook.blade.php';
            break;
        case 'Library\Custom\Hp\Page\SideParts\Tw':
            $template = 'side-parts/twitter.blade.php';
            break;
        case 'Library\Custom\Hp\Page\SideParts\Qr':
            $template = null;
            break;
        case 'Library\Custom\Form\Element\Hidden':
            continue 2;
        default:
            $template = $parts->getTemplate('side-parts/');
    }
    if (!$template) {
        continue;
    }
    echo $view->partial($template, array('element' => $parts, 'page' => $view->page, 'all_pages' => $view->all_pages));
    ?>
<?php endforeach ?>