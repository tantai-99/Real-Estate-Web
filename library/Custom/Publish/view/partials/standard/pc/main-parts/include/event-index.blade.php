<?php $view->getInnerHtml() ;?>

<?php
$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if (!$part instanceof \Library\Custom\Hp\Page\Parts\EventDetail) {
                continue;
            }
            foreach ($part->elements->getSubForms() as $event) {
                if (!isset($parts[$page->getRow()->id])) {
                    $parts[$page->getRow()->link_id] = array();
                }
                $parts[$page->getRow()->link_id][] = $event;
            }
        }
    }
}

$index = 0;
$last_index = count($parts) - 1;
?>
<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>

<?php foreach ($parts->getSubForms() as $page_id => $part): ?>
    <?php $eventIdx=0 ?>
    <?php foreach ($part as $event): ?>
        <?php
        $url = $view->hpLink($page_id);
        echo '<?php $url = "'.$url.'" ;?>';
        $script = file_get_contents($view->getScriptPath('main-parts/include/event-index_script.blade.php'));
        echo str_replace ( '(\'//h3[@class="heading-lv2"]\')->item(0)', 
                           '(\'//h3[@class="heading-lv2"]\')->item('.$eventIdx.')', $script );
        $eventIdx++;
        ?>
    <?php endforeach ?>
    <?php $index++ ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
