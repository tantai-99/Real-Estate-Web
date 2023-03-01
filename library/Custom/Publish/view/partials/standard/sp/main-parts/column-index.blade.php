<?php
if (!$view->pages) {
    return;
}

$parts = [];
foreach ($view->pages as $page) {
    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
        foreach ($area->parts->getSubForms() as $part) {
            if ($part instanceof \Library\Custom\Hp\Page\Parts\ColumnDetail) {
                $part->title = $page->form->getSubForm('tdk')->title;
                $part->date = $page->form->getSubForm('tdk')->date;
                $parts[$page->getRow()->id] = $part;
            }
        }
    }
}

echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => false, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm))
?>

<?php foreach ($parts as $page_id => $item): ?>
  <div class="element element-colum-list">
    <a href="<?php echo $view->hpLink($item->getPage()->link_id) ?>">
      <div class="element-colum-list-left"><?php if ($item->getValue('image')): ?><img src="<?php echo $view->hpImage($item->getValue('image')) ?>" alt="<?php echo $item->getValue('image_title') ?>"/>
   　　 <?php endif ?>
      </div>
      <div class="element-colum-list-right">
        <h3 class="heading-lv2"><?php echo h($item->title->getValue()) ?></h3>
        <div class="element-text-wrapper">
          <p class="element-tx">
            <?php echo h(mb_strimwidth(strip_tags($item->getValue('read_content')), 0, 100, "...", "utf-8")) ?>
          </p>
          <?php $new = ""; ?>
          <?php if($item->date->getValue() > date("Y年m月d日", strtotime("-10 day"))) : ?>
          <?php $new = " new"; ?>
          <?php endif;?>
          <p class="element-colum-list-date<?php echo $new; ?>"><?php echo h($item->date->getValue()) ?></p>
        </div>
      </div>
    </a>
  </div>
<?php endforeach ?>


<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm)) ?>