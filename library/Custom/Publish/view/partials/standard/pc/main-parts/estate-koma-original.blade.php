
<?php echo $view->partial('main-parts/heading.blade.php', ['element' => $view->element, 'heading' => 'おすすめ物件', 'level' => 1]); ?>
<div class="element element-recommend estate-koma"
     data-special-path="<?= $view->element->getSpecialPath(); ?>"
     data-rows="<?= $view->element->getValue('pc_rows') ?>"
     data-sort-option="<?= $view->element->getValue('sort_option') ?>"
>
  <?php for ($i = 0; $i < $view->element->getValue('pc_rows') * $view->element->getValue('pc_columns'); $i++): ?>
    <div class="recommend-item">
      <p class="recommend-ph"><a href="#"><img src="<?php $view->src('imgs/img_loading.gif', '/images/dummy/dummy_koma.png'); ?>" alt=""></a></p>
      <p class="recommend-name"><a href="#">物件名</a></p>
      <p class="tx-price">価格：-万円</p>
      <p class="recommend-kind">間取り：-</p>
      <p class="recommend-station">徒歩-分</p>
    </div>
  <?php endfor; ?>
</div>