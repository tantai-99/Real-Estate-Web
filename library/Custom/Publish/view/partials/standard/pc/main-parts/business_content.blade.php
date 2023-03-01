<?php //echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getValue('heading'), 'level' => 1)) ?>

<?php foreach ($view->element->elements->getSubForms() as $rows): ?>

    <?php if($rows->getValue('heading') != "") : ?><h3 class="heading-lv2"><?php echo h($rows->getValue('heading')); ?></h3><?php endif;?>

    <?php if($rows->getValue('row_setting') == 1) : ?>

    <?php foreach ($rows->elements->getSubForms() as $item): ?>
    <div class="element element-bussiness element-bussiness-col1 element-box-bg">
        <?php if ($item->getValue('image')): ?>
        <div class="element-bussiness-mainvisual element-left">
            <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
        </div>
        <?php endif; ?>

        <dl class="element-right">
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
    <?php elseif($rows->getValue('row_setting') > 1) : ?>

        <div class="element element-bussiness element-bussiness-col<?php echo trim($rows->getValue('row_setting'));?>">
          <div class="element-bussiness-inner">
          <?php foreach ($rows->elements->getSubForms() as $item): ?>
            <div class="element-col-box element-box-bg">
              <?php if ($item->getValue('image')): ?>
              <div class="element-bussiness-mainvisual">
                <img src="<?php echo $view->hpImage($item->getValue('image')) ?>" alt="<?php echo h($item->getValue('image_title')) ?>">
              </div>
              <?php endif ?>
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
                  <p class="tac">
                    <a href="<?php echo h($item->getValue('url')); ?>" class="btn-lv2"<?php if($item->getValue('link_target_blank') == 1) : ?> target="_blank"<?php endif; ?>><?php echo h($item->getValue('link_name')); ?></a>
                  </p>
                  <?php endif; ?>

                </dd>
              </dl>
            </div>
          <?php endforeach ?>
          </div>
        </div>
    <?php endif; ?>

<?php endforeach ?>
