<?php $view->getInnerHtml() ;?>

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

   <?php foreach ($parts->getSubForms() as $page_id => $item): ?>
   <div class="element element-colum-list">
      <?php if ($item->getValue('image')): ?>
      <div class="element-colum-list-left">
        <img src="<?php echo $view->hpImage($item->getValue('image')) ?>" alt="<?php echo $item->getValue('image_title') ?>"/>
      </div>
      <?php endif ?>
      <div class="element-colum-list-right">
        <h3 class="heading-lv2"><?php echo h($item->getValue('title')) ?></h3>
        <div class="element-text-wrapper">
        <?php $new = ""; ?>
        <?php if($item->getValue('date') > date("Y年m月d日", strtotime("-10 day"))) : ?>
        <?php $new = " new"; ?>
        <?php endif;?>
          <p class="element-colum-list-date<?php echo $new; ?>"><?php echo h($item->getValue('date')) ?></p>
          <p class="element-tx"><?php echo h(mb_strimwidth(strip_tags($item->getValue('read_content')), 0, 100, "...", "utf-8")) ?></p>
          <p class="bold tar"><a href="<?php echo $view->hpLink($item->getPage()->link_id) ?>">続きはこちら</a></p>
        </div>
      </div>
    </div>
    <?php endforeach ?>

<?php echo $view->partial('main-parts/pager.blade.php', array('page' => $view->page, 'bottom' => true, 'current' => $view->listNumber, 'total' => $view->listCount, 'blog_yyyymm' => $view->blog_yyyymm)) ?>