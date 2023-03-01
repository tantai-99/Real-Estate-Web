<?php $view->getInnerHtml() ;?>

<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if ($part instanceof \Library\Custom\Hp\Page\Parts\CustomervoiceDetail) {
                $parts[$page->getRow()->id] = $part;
            }
        }
    }
}
?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php
$index = 0;
$last_index = count($parts) - 1;
?>
<?php foreach ($parts->getSubForms() as $page_id => $part): ?>
    <?php
    $url = $view->hpLink($part->getPage()->link_id);
    echo '<?php $url = "'.$url.'" ;?>';
    echo $script = file_get_contents($view->getScriptPath('main-parts/include/customervoice-index_script.blade.php'));
    ?>
    <?php $index++ ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
