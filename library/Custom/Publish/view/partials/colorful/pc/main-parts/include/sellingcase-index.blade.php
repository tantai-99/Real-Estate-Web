<?php $view->getInnerHtml() ;?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $this->page, 'bottom' => false, 'current' => $this->listNumber, 'total' => $this->listCount)) ?>

<?php foreach ($view->pages as $page): ?>
    <?php foreach ($page->form->getSubForm('main')->getSubForms() as $area): ?>
        <?php foreach ($area->parts->getSubForms() as $part): ?>
            <?php if (!$part instanceof \Library\Custom\Hp\Page\Parts\SellingcaseDetail) continue; ?>
            <?php $sellingIdx=0 ?>
            <?php foreach ($part->elements->getSubForms() as $el): ?>
                <?php if (!$el->getValue('heading')) continue; ?>
                <?php
                $url = $view->hpLink($part->getPage()->link_id);
                echo '<?php $url = "'.$url.'" ;?>';
                $script = file_get_contents($view->getScriptPath('main-parts/include/sellingcase-index_script.php'));
                echo str_replace ( '(\'//section[@class="sellingcase"]/h3[@class="heading-lv2"]\')->item(0)',
                                   '(\'//section[@class="sellingcase"]/h3[@class="heading-lv2"]\')->item('.$sellingIdx.')', $script );
                $sellingIdx++;
                ?>
            <?php endforeach ?>
        <?php endforeach ?>
    <?php endforeach ?>
<?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount)) ?>
