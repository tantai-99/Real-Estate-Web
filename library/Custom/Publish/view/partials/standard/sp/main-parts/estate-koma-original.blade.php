<h2 class="heading-lv1"><span>おすすめ物件</span></h2>
<div class="element element-recommend-caro estate-koma"
     data-special-path="<?= $view->element->getSpecialPath(); ?>"
     data-rows="<?= $view->element->getValue('sp_rows') ?>"
     data-sort-option="<?= $view->element->getValue('sort_option') ?>"
>
  <?php for ($i = 0; $i < $view->element->getValue('sp_rows') * $view->element->getValue('sp_columns'); $i++): ?>
  <div class="recommend-caro-item">
      <a href="#">
        <p class="recommend-caro-ph"><img src="<?php $view->src('imgs/img_loading.gif', '/images/dummy/dummy_koma.png'); ?>"" alt=""></p>
        <div class="recommend-caro-info">
          <p class="recommend-caro-name">物件名</p>
          <p class="tx-price">-万円</p>
          <p class="recommend-caro-kind">間取り：</p>
          <p class="recommend-caro-station">徒歩-分</p>
        </div>
      </a>
    </div>
  <?php endfor; ?>
</div>