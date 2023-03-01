<?php foreach ($view->top_page->form->getSubForm('side')->getSubForms() as $parts): ?>
    <?php if ('0' === $parts->getValue('display_flg')) continue ?>

    <?php
    $page = $view->top_page;
    switch(get_class($parts)){
        case 'Library\Custom\Hp\Page\SideParts\Fb':
            $template = 'side-parts/facebook.blade.php';
            break;
        case 'Library\Custom\Hp\Page\SideParts\Tw':
            $template = 'side-parts/twitter.blade.php';
            break;
        case 'Library\Custom\Hp\Page\SideParts\Freeword':
            if ($view->isTopOriginal && $view->isTop) {
                $template = null;
                break;
            }
            $template = 'side-parts/freeword.blade.php';
            break;
        case 'Library\Custom\Hp\Page\SideParts\Qr':
            $template = 'side-parts/qr.blade.php';
            if ($view->page !== "" && $view->isPreview) {
                $page = $view->page;
            }
            break;
        case 'Library\Custom\Form\Element\Hidden':
            continue 2;
        default:
            $template = $parts->getTemplate('side-parts/');
    }
    if (!$template) {
        continue;
    }
    echo $view->partial($template, array(
        'element'     => $parts,
        'page'        => $page,
        'all_pages'   => $view->all_pages,
        'side_common' => true,
        'isDispTopQr' => $view->isTopOriginal && $view->isTop,
        'is_preview'  => $view->isPreview
    ));
    ?>
<?php endforeach ?>