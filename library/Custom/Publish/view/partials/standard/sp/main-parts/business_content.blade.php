
<?php foreach ($view->element->elements->getSubForms() as $rows): ?>

  <?php if($rows->getValue('heading') != "") : ?><h3 class="heading-lv2"><?php echo h($rows->getValue('heading')); ?></h3><?php endif;?>
  
  <?php foreach ($rows->elements->getSubForms() as $item): ?>
  <div class="element element-bussiness element-bussiness-col1 element-box-bg">
    <?php if ($item->getValue('image')): ?>
    <div class="element-bussiness-mainvisual">
      <img src="<?php echo $view->hpImage($item->getValue('image')) ?>" alt="<?php echo h($item->getValue('image_title')) ?>">
    </div>
    <?php endif; ?>
    <dl>
      <dt>
        <strong><?php echo h($item->getValue('business_name')); ?></strong>
        <span><?php echo h($item->getValue('rubi')); ?></span>
      </dt>
      <dd>
        <div class="element-tx">
          <?php echo $item->getValue('description'); ?>
        </div>
        
	    <?php if($item->getValue('link_name') != "" && $item->getValue('url') != ""): ?>
        <p class="tac"><a href="<?php echo h($item->getValue('url')); ?>" class="btn-lv2"<?php if($item->getValue('link_target_blank') == 1) : ?> target="_blank"<?php endif; ?>><?php echo h($item->getValue('link_name')); ?></a></p>
        <?php endif; ?>
      </dd>
    </dl>
  </div>
  <?php endforeach ?>
<?php endforeach ?>


